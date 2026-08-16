<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\User;
use App\Services\ImageDuplicateDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminGalleryController extends Controller
{
    public function index()
    {
        $photos  = GalleryPhoto::where('is_guest_upload', false)
            ->with('taggedUsers')
            ->orderBy('sort_order')->orderBy('id')->get();

        $pending = GalleryPhoto::where('is_guest_upload', true)
            ->where('status', 'pending')
            ->with('uploader')
            ->orderByDesc('created_at')->get();

        $guestApproved = GalleryPhoto::where('is_guest_upload', true)
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['uploader', 'taggedUsers'])
            ->orderByDesc('created_at')->get();

        $taggableGuests = User::where('role', 'guest')
            ->with('guestProfile')
            ->get()
            ->sortBy(function (User $u) {
                $p = $u->guestProfile;
                return $p ? $p->last_name . $p->first_name : $u->name;
            })
            ->values();

        return view('admin.gallery', compact('photos', 'pending', 'guestApproved', 'taggableGuests'));
    }

    public function store(Request $request, ImageDuplicateDetector $duplicateDetector)
    {
        $request->validate([
            'photos'          => 'required|array|max:20',
            'photos.*'        => 'required|image|mimes:jpeg,png,webp,gif|max:10240',
            'captions'        => 'nullable|array',
            'captions.*'      => 'nullable|string|max:200',
        ], [
            'photos.required'   => '画像を選択してください',
            'photos.*.image'    => '画像ファイルを選択してください',
            'photos.*.max'      => '1枚10MB以内にしてください',
        ]);

        $maxOrder = GalleryPhoto::max('sort_order') ?? 0;
        $count = 0;
        $duplicateCount = 0;

        foreach ($request->file('photos') as $i => $file) {
            if ($duplicateDetector->findDuplicate($file->getRealPath()) !== null) {
                $duplicateCount++;
                continue;
            }

            $path = $file->store('gallery', 'public');
            GalleryPhoto::create([
                'file_path'  => $path,
                'caption'    => $request->captions[$i] ?? null,
                'sort_order' => $maxOrder + $count + 1,
                'is_active'  => true,
                'status'     => 'approved',
                'file_hash'  => $duplicateDetector->fileHash($file->getRealPath()),
                'phash'      => $duplicateDetector->perceptualHash($file->getRealPath()),
            ]);
            $count++;
        }

        $message = "{$count}枚の写真を追加しました";
        if ($duplicateCount > 0) {
            $message .= "(既存の写真と同じものが{$duplicateCount}枚あったため除外しました)";
        }

        return back()->with('success', $message);
    }

    public function update(Request $request, int $id)
    {
        $photo = GalleryPhoto::findOrFail($id);
        $request->validate(['caption' => 'nullable|string|max:200']);

        $photo->update([
            'caption'   => $request->caption ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', '更新しました');
    }

    public function destroy(int $id)
    {
        $photo = GalleryPhoto::findOrFail($id);
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();
        return back()->with('success', '削除しました');
    }

    /** ゲスト投稿を承認してギャラリーに追加 */
    public function approve(int $id)
    {
        $photo = GalleryPhoto::where('is_guest_upload', true)->findOrFail($id);
        $maxOrder = GalleryPhoto::max('sort_order') ?? 0;

        $photo->update([
            'status'     => 'approved',
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', '写真を承認してギャラリーに追加しました');
    }

    /** ゲスト投稿を却下（ファイルは保持） */
    public function reject(int $id)
    {
        $photo = GalleryPhoto::where('is_guest_upload', true)->findOrFail($id);
        $photo->update(['status' => 'rejected', 'is_active' => false]);

        return back()->with('success', '写真を却下しました');
    }

    /** 写真に写っている人物をタグ付け */
    public function tag(Request $request, int $id)
    {
        $photo = GalleryPhoto::where('status', 'approved')->findOrFail($id);

        $request->validate([
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $photo->taggedUsers()->sync($request->input('user_ids', []));

        return back()->with('success', '写真のタグ付けを更新しました');
    }

    public function moveUp(int $id)
    {
        $photo = GalleryPhoto::findOrFail($id);
        $prev  = GalleryPhoto::where('sort_order', '<', $photo->sort_order)
            ->orderByDesc('sort_order')->first();
        if ($prev) {
            [$photo->sort_order, $prev->sort_order] = [$prev->sort_order, $photo->sort_order];
            $photo->save(); $prev->save();
        }
        return back();
    }

    public function moveDown(int $id)
    {
        $photo = GalleryPhoto::findOrFail($id);
        $next  = GalleryPhoto::where('sort_order', '>', $photo->sort_order)
            ->orderBy('sort_order')->first();
        if ($next) {
            [$photo->sort_order, $next->sort_order] = [$next->sort_order, $photo->sort_order];
            $photo->save(); $next->save();
        }
        return back();
    }
}
