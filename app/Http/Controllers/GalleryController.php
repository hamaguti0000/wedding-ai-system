<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\GuestGroup;
use App\Services\GalleryImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GalleryController extends Controller
{
    public function index()
    {
        $relations = ['uploader.guestProfile', 'taggedUsers.guestProfile'];
        $hasGuestGroups = Schema::hasTable('guest_groups');
        if ($hasGuestGroups) {
            $relations[] = 'taggedGroups.primaryGuest';
            $relations[] = 'taggedGroups.members';
        }

        $photos = GalleryPhoto::where('is_active', true)
            ->where('status', 'approved')
            ->with($relations)
            ->orderBy('sort_order')->orderBy('id')->get();
        if (! $hasGuestGroups) {
            $photos->each->setRelation('taggedGroups', collect());
        }

        $currentUser = Auth::user();
        $currentUserGroupIds = collect();
        $currentUserGroupNames = collect();
        if ($currentUser && $hasGuestGroups) {
            $currentUserGroups = $currentUser->guestGroups()->with('primaryGuest')->get();
            $currentUserGroupIds = $currentUserGroups->pluck('id');
            $currentUserGroupNames = collect($currentUserGroups
                ->map(fn ($group) => $group->galleryDisplayName())
                ->all())
                ->filter()
                ->values();
        }

        if ($currentUser?->guestProfile && $hasGuestGroups) {
            $profileGroupName = GuestGroup::galleryLabelFor(
                $currentUser->guestProfile->guest_side,
                $currentUser->guestProfile->relationship
            );

            if ($profileGroupName) {
                $currentUserGroupNames->push($profileGroupName);
            }
        }

        $currentUserGroupNames = $currentUserGroupNames->unique()->values();

        return view('gallery', compact('photos', 'currentUserGroupIds', 'currentUserGroupNames'));
    }


    public function downloadSelected(Request $request)
    {
        $validated = $request->validate([
            'photo_ids' => 'required|array|min:1|max:80',
            'photo_ids.*' => 'integer',
        ]);

        $photos = GalleryPhoto::where('is_active', true)
            ->where('status', 'approved')
            ->whereIn('id', $validated['photo_ids'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        abort_if($photos->isEmpty(), 404);

        $zipPath = storage_path('app/tmp/gallery-selected-' . Auth::id() . '-' . now()->format('YmdHis') . '.zip');
        if (! is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0775, true);
        }

        $zip = new ZipArchive();
        abort_if($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true, 500);

        $added = 0;
        foreach ($photos as $index => $photo) {
            $relativePath = $photo->file_path ?: $photo->display_file_path;
            if (! $relativePath || ! Storage::disk('public')->exists($relativePath)) {
                continue;
            }

            $extension = pathinfo($relativePath, PATHINFO_EXTENSION) ?: 'jpg';
            $zip->addFile(
                Storage::disk('public')->path($relativePath),
                sprintf('wedding-photo-%03d-%d.%s', $index + 1, $photo->id, $extension)
            );
            $added++;
        }
        $zip->close();

        abort_if($added === 0, 404);

        return response()->download($zipPath, 'wedding-photos-' . now()->format('YmdHis') . '.zip')->deleteFileAfterSend(true);
    }

    public function uploadForm()
    {
        return view('gallery-upload');
    }

    public function upload(Request $request, GalleryImageOptimizer $imageOptimizer)
    {
        $request->validate([
            'photos'     => 'required|array|max:10',
            'photos.*'   => 'required|image|mimes:jpeg,png,webp|max:10240',
            'message'    => 'nullable|string|max:500',
        ], [
            'photos.required'  => '写真を選択してください',
            'photos.*.image'   => '画像ファイルを選択してください',
            'photos.*.max'     => '1枚10MB以内にしてください',
            'photos.max'       => '一度に最大10枚までアップロードできます',
        ]);

        $maxOrder = GalleryPhoto::max('sort_order') ?? 0;
        $count = 0;

        foreach ($request->file('photos') as $file) {
            $stored = $imageOptimizer->storeOriginalWithDisplay($file, 'gallery/guest');
            GalleryPhoto::create([
                'file_path'           => $stored['original'],
                'display_file_path'   => $stored['display'],
                'caption'             => $request->message ?: null,
                'gallery_category'    => 'other',
                'photo_source'        => 'guest',
                'sort_order'          => $maxOrder + $count + 1,
                'is_active'           => false,
                'uploaded_by_user_id' => Auth::id(),
                'status'              => 'pending',
                'is_guest_upload'     => true,
            ]);
            $count++;
        }

        $message = $this->uploadResultMessage($count);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'uploaded_count' => $count,
            ]);
        }

        return back()->with('success', $message);
    }

    private function uploadResultMessage(int $count): string
    {
        return "{$count}枚の写真を投稿しました！管理者の確認後に公開されます🎉";
    }

}
