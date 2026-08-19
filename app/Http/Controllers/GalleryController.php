<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Services\GalleryImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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
        if ($currentUser && $hasGuestGroups) {
            $currentUserGroupIds = $currentUser->guestGroups()->pluck('guest_groups.id');
        }

        return view('gallery', compact('photos', 'currentUserGroupIds'));
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
            $path = $imageOptimizer->store($file, 'gallery/guest');
            GalleryPhoto::create([
                'file_path'           => $path,
                'caption'             => $request->message ?: null,
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
