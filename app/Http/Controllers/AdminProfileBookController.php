<?php

namespace App\Http\Controllers;

use App\Models\ProfileBookPage;
use App\Services\PdfToImagesConverter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AdminProfileBookController extends Controller
{
    public function edit()
    {
        $pages = ProfileBookPage::orderBy('page_number')->get();

        return view('admin.profile-book', compact('pages'));
    }

    /**
     * 1つ以上のPDFをアップロードし、各PDFのページを画像化して
     * 既存ページの末尾に追記する（既存ページは消さない）。
     */
    public function upload(Request $request, PdfToImagesConverter $converter)
    {
        $request->validate([
            'pdfs'   => 'required|array|max:10',
            'pdfs.*' => 'file|mimes:pdf|max:20480',
        ], [
            'pdfs.required' => 'PDFファイルを選択してください',
            'pdfs.max'      => '一度に最大10ファイルまでアップロードできます',
            'pdfs.*.mimes'  => 'PDFファイルを選択してください',
            'pdfs.*.max'    => '1ファイル20MB以内にしてください',
        ]);

        $tmpDir = storage_path('app/tmp/profile-book-'.Str::random(12));
        $nextPageNumber = (ProfileBookPage::max('page_number') ?? 0) + 1;

        try {
            $newRecords = [];

            foreach ($request->file('pdfs') as $pdf) {
                $images = $converter->convert($pdf->getRealPath(), $tmpDir.'-'.Str::random(8));

                foreach ($images as $localPath) {
                    $storedPath = 'profile-book/'.Str::random(20).'.jpg';
                    Storage::disk('public')->put($storedPath, file_get_contents($localPath));
                    $newRecords[] = ['page_number' => $nextPageNumber, 'image_path' => $storedPath];
                    $nextPageNumber++;
                }
            }

            DB::transaction(function () use ($newRecords): void {
                foreach ($newRecords as $record) {
                    ProfileBookPage::create($record);
                }
            });

            return back()->with('success', count($newRecords).'ページを追加しました');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'PDFの変換に失敗しました。ファイルを確認してもう一度お試しください。');
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    /** ページを1件削除し、残りのページ番号を詰め直す */
    public function destroyPage(int $id)
    {
        $page = ProfileBookPage::findOrFail($id);
        Storage::disk('public')->delete($page->image_path);
        $page->delete();

        ProfileBookPage::orderBy('page_number')->get()
            ->values()
            ->each(fn (ProfileBookPage $p, int $i) => $p->update(['page_number' => $i + 1]));

        return back()->with('success', 'ページを削除しました');
    }

    /** プロフィールブック全体を削除する */
    public function destroy()
    {
        $pages = ProfileBookPage::all();

        foreach ($pages as $page) {
            Storage::disk('public')->delete($page->image_path);
        }

        ProfileBookPage::query()->delete();

        return back()->with('success', 'プロフィールブックを削除しました');
    }

    public function moveUp(int $id)
    {
        $page = ProfileBookPage::findOrFail($id);
        $prev = ProfileBookPage::where('page_number', '<', $page->page_number)
            ->orderByDesc('page_number')->first();

        if ($prev) {
            [$page->page_number, $prev->page_number] = [$prev->page_number, $page->page_number];
            $page->save();
            $prev->save();
        }

        return back();
    }

    public function moveDown(int $id)
    {
        $page = ProfileBookPage::findOrFail($id);
        $next = ProfileBookPage::where('page_number', '>', $page->page_number)
            ->orderBy('page_number')->first();

        if ($next) {
            [$page->page_number, $next->page_number] = [$next->page_number, $page->page_number];
            $page->save();
            $next->save();
        }

        return back();
    }
}
