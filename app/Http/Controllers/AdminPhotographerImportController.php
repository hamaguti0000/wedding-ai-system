<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\PhotographerImportBatch;
use App\Models\PhotographerImportItem;
use App\Services\GalleryImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class AdminPhotographerImportController extends Controller
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function index()
    {
        File::ensureDirectoryExists(storage_path('app/imports'));

        $batches = PhotographerImportBatch::withCount([
            'items',
            'items as pending_count' => fn ($q) => $q->where('status', PhotographerImportItem::STATUS_PENDING),
            'items as accepted_count' => fn ($q) => $q->where('status', PhotographerImportItem::STATUS_ACCEPTED),
            'items as rejected_count' => fn ($q) => $q->where('status', PhotographerImportItem::STATUS_REJECTED),
        ])->latest()->get();

        return view('admin.photographer-imports', [
            'batches' => $batches,
            'categoryOptions' => GalleryPhoto::categoryOptions(),
            'serverImportPath' => storage_path('app/imports'),
            'zipAvailable' => class_exists(ZipArchive::class),
        ]);
    }

    public function store(Request $request, GalleryImageOptimizer $imageOptimizer)
    {
        $request->validate([
            'name' => 'nullable|string|max:120',
            'gallery_category' => 'required|string|in:' . implode(',', array_keys(GalleryPhoto::categoryOptions())),
            'zip_file' => 'nullable|file|mimes:zip|max:5500000',
            'server_zip_path' => 'nullable|string|max:1000',
        ], [
            'zip_file.max' => 'ZIPが大きすぎます。サーバに配置してパス指定で取り込んでください。',
            'zip_file.mimes' => 'ZIPファイルを選択してください。',
        ]);

        if (! class_exists(ZipArchive::class)) {
            return back()->with('error', 'PHP ZipArchive が有効ではないため、ZIPを解凍できません。');
        }

        [$zipPath, $storedZipPath, $originalName] = $this->resolveZipPath($request);
        if (! $zipPath) {
            return back()->with('error', 'ZIPファイルを選択するか、サーバ上のZIPパスを入力してください。');
        }

        $batch = PhotographerImportBatch::create([
            'name' => $request->input('name') ?: pathinfo($originalName, PATHINFO_FILENAME),
            'zip_path' => $storedZipPath,
            'original_filename' => $originalName,
            'status' => PhotographerImportBatch::STATUS_READY,
            'gallery_category' => $request->input('gallery_category'),
            'created_by_user_id' => Auth::id(),
        ]);

        try {
            $result = $this->extractZip($batch, $zipPath, $imageOptimizer);
            $batch->update([
                'total_entries' => $result['total'],
                'imported_count' => $result['imported'],
                'skipped_count' => $result['skipped'],
                'status' => PhotographerImportBatch::STATUS_READY,
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => PhotographerImportBatch::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.gallery.imports')
                ->with('error', 'ZIPの解凍に失敗しました: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.gallery.imports.sort', $batch)
            ->with('success', "{$batch->imported_count}枚を仕分け待ちに取り込みました");
    }

    public function sort(Request $request, PhotographerImportBatch $batch)
    {
        $status = $request->query('status', 'pending');
        if (! in_array($status, ['all', PhotographerImportItem::STATUS_PENDING, PhotographerImportItem::STATUS_ACCEPTED, PhotographerImportItem::STATUS_REJECTED], true)) {
            $status = PhotographerImportItem::STATUS_PENDING;
        }

        $query = $batch->items()->orderBy('sort_order')->orderBy('id');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return view('admin.photographer-import-sort', [
            'batch' => $batch->loadCount([
                'items',
                'items as pending_count' => fn ($q) => $q->where('status', PhotographerImportItem::STATUS_PENDING),
                'items as accepted_count' => fn ($q) => $q->where('status', PhotographerImportItem::STATUS_ACCEPTED),
                'items as rejected_count' => fn ($q) => $q->where('status', PhotographerImportItem::STATUS_REJECTED),
            ]),
            'items' => $query->paginate(60)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function decide(Request $request, PhotographerImportBatch $batch, PhotographerImportItem $item)
    {
        abort_unless($item->photographer_import_batch_id === $batch->id, 404);

        $request->validate([
            'decision' => 'required|in:accept,reject',
        ]);

        $this->applyDecision($batch, $item, $request->input('decision'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $item->fresh()->status,
                'message' => $request->input('decision') === 'accept' ? '公開にしました' : '除外しました',
            ]);
        }

        return back()->with('success', $request->input('decision') === 'accept' ? '公開にしました' : '除外しました');
    }

    public function bulkDecide(Request $request, PhotographerImportBatch $batch)
    {
        $validated = $request->validate([
            'decision' => 'required|in:accept,reject',
            'item_ids' => 'required|array',
            'item_ids.*' => 'integer',
        ]);

        $items = $batch->items()->whereIn('id', $validated['item_ids'])->get();
        foreach ($items as $item) {
            $this->applyDecision($batch, $item, $validated['decision']);
        }

        return back()->with('success', "{$items->count()}枚を更新しました");
    }

    private function applyDecision(PhotographerImportBatch $batch, PhotographerImportItem $item, string $decision): void
    {
        DB::transaction(function () use ($batch, $item, $decision) {
            if ($decision === 'accept') {
                GalleryPhoto::where('status', 'approved')->increment('sort_order');

                $photo = $item->galleryPhoto ?: GalleryPhoto::create([
                    'file_path' => $item->file_path,
                    'display_file_path' => $item->display_file_path,
                    'caption' => null,
                    'gallery_category' => $batch->gallery_category,
                    'photo_source' => 'photographer',
                    'sort_order' => 1,
                    'is_active' => true,
                    'status' => 'approved',
                    'is_guest_upload' => false,
                ]);

                if ($item->gallery_photo_id) {
                    $photo->update([
                        'status' => 'approved',
                        'is_active' => true,
                        'photo_source' => 'photographer',
                        'gallery_category' => $batch->gallery_category,
                        'sort_order' => 1,
                    ]);
                }

                $item->update([
                    'gallery_photo_id' => $photo->id,
                    'status' => PhotographerImportItem::STATUS_ACCEPTED,
                    'decided_at' => now(),
                    'decided_by_user_id' => Auth::id(),
                ]);

                return;
            }

            if ($item->galleryPhoto) {
                $item->galleryPhoto->update([
                    'status' => 'rejected',
                    'is_active' => false,
                ]);
            }

            $item->update([
                'status' => PhotographerImportItem::STATUS_REJECTED,
                'decided_at' => now(),
                'decided_by_user_id' => Auth::id(),
            ]);
        });
    }

    /**
     * @return array{0:?string,1:?string,2:string}
     */
    private function resolveZipPath(Request $request): array
    {
        if ($request->hasFile('zip_file')) {
            $file = $request->file('zip_file');
            $stored = $file->store('photographer-zips');

            return [Storage::path($stored), $stored, $file->getClientOriginalName()];
        }

        $path = trim((string) $request->input('server_zip_path'));
        if ($path === '') {
            return [null, null, ''];
        }

        $realPath = realpath($path);
        $allowedBase = realpath(storage_path('app/imports'));
        if (! $realPath || ! $allowedBase || ! str_starts_with($realPath, $allowedBase . DIRECTORY_SEPARATOR)) {
            return [null, null, ''];
        }

        return [$realPath, null, basename($realPath)];
    }

    /**
     * @return array{total:int,imported:int,skipped:int}
     */
    private function extractZip(PhotographerImportBatch $batch, string $zipPath, GalleryImageOptimizer $imageOptimizer): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $zip = new ZipArchive();
        $opened = $zip->open($zipPath);
        if ($opened !== true) {
            throw new \RuntimeException('ZIPファイルを開けませんでした');
        }

        $total = 0;
        $imported = 0;
        $skipped = 0;
        $baseDir = 'photographer-imports/' . $batch->id;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            $total++;

            if ($this->shouldSkipZipEntry($name)) {
                $skipped++;
                continue;
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (! in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                $skipped++;
                continue;
            }

            $stream = $zip->getStream($name);
            if (! $stream) {
                $skipped++;
                continue;
            }

            $filePath = $baseDir . '/original/' . Str::uuid() . '.' . ($extension === 'jpeg' ? 'jpg' : $extension);
            Storage::disk('public')->put($filePath, stream_get_contents($stream));
            fclose($stream);

            $displayPath = $imageOptimizer->optimizedCopyForPublicFile($filePath, $baseDir . '/display');
            $absolutePath = Storage::disk('public')->path($filePath);

            PhotographerImportItem::create([
                'photographer_import_batch_id' => $batch->id,
                'original_name' => mb_substr($name, 0, 255),
                'file_path' => $filePath,
                'display_file_path' => $displayPath,
                'file_size' => is_file($absolutePath) ? filesize($absolutePath) : 0,
                'mime_type' => Storage::disk('public')->mimeType($filePath),
                'status' => PhotographerImportItem::STATUS_PENDING,
                'sort_order' => $imported + 1,
            ]);

            $imported++;
        }

        $zip->close();

        return compact('total', 'imported', 'skipped');
    }

    private function shouldSkipZipEntry(string $name): bool
    {
        return str_ends_with($name, '/')
            || str_starts_with($name, '__MACOSX/')
            || str_contains($name, '/__MACOSX/')
            || str_contains($name, '/../')
            || str_starts_with($name, '.')
            || str_contains($name, '/.');
    }
}
