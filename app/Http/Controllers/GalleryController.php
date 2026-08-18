<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Services\ImageDuplicateDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class GalleryController extends Controller
{
    public function index()
    {
        $relations = ['taggedUsers.guestProfile'];
        if (Schema::hasTable('guest_groups')) {
            $relations[] = 'taggedGroups.primaryGuest';
        }

        $photos = GalleryPhoto::where('is_active', true)
            ->where('status', 'approved')
            ->with($relations)
            ->orderBy('sort_order')->orderBy('id')->get();
        if (! Schema::hasTable('guest_groups')) {
            $photos->each->setRelation('taggedGroups', collect());
        }

        return view('gallery', compact('photos'));
    }

    public function uploadForm()
    {
        return view('gallery-upload');
    }

    public function upload(Request $request, ImageDuplicateDetector $duplicateDetector)
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
        $count           = 0;
        $duplicateCount  = 0;

        foreach ($request->file('photos') as $file) {
            // 誰かから回ってきた同じ写真を複数人が投稿するケースがあるため、
            // 完全一致・見た目がほぼ同じ写真は新規登録せずスキップする。
            if ($duplicateDetector->findDuplicate($file->getRealPath()) !== null) {
                $duplicateCount++;
                continue;
            }

            $path = $file->store('gallery/guest', 'public');
            GalleryPhoto::create([
                'file_path'           => $path,
                'caption'             => $request->message ?: null,
                'sort_order'          => $maxOrder + $count + 1,
                'is_active'           => false,
                'uploaded_by_user_id' => Auth::id(),
                'status'              => 'pending',
                'is_guest_upload'     => true,
                'file_hash'           => $duplicateDetector->fileHash($file->getRealPath()),
                'phash'               => $duplicateDetector->perceptualHash($file->getRealPath()),
            ]);
            $count++;
        }

        $message = $this->uploadResultMessage($count, $duplicateCount);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'uploaded_count' => $count,
                'duplicate_count' => $duplicateCount,
            ]);
        }

        return back()->with('success', $message);
    }

    private function uploadResultMessage(int $count, int $duplicateCount): string
    {
        if ($count === 0 && $duplicateCount > 0) {
            return 'その写真はすでに他の方が投稿済みでした。別の写真を投稿してください。';
        }

        if ($duplicateCount > 0) {
            return "{$count}枚の写真を投稿しました！(既に投稿済みの写真と同じものが{$duplicateCount}枚あったため除外しました)管理者の確認後に公開されます🎉";
        }

        return "{$count}枚の写真を投稿しました！管理者の確認後に公開されます🎉";
    }
}
