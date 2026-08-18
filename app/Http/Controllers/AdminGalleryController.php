<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\GuestGroup;
use App\Models\User;
use App\Services\ImageDuplicateDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminGalleryController extends Controller
{
    public function index()
    {
        $photos  = GalleryPhoto::where('is_guest_upload', false)
            ->with(['taggedUsers.guestProfile', 'taggedGroups.primaryGuest'])
            ->orderBy('sort_order')->orderBy('id')->get();

        $pending = GalleryPhoto::where('is_guest_upload', true)
            ->where('status', 'pending')
            ->with('uploader')
            ->orderByDesc('created_at')->get();

        $guestApproved = GalleryPhoto::where('is_guest_upload', true)
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['uploader', 'taggedUsers.guestProfile', 'taggedGroups.primaryGuest'])
            ->orderByDesc('created_at')->get();

        $taggableGuests = User::where('role', 'guest')
            ->with('guestProfile')
            ->get()
            ->sortBy(function (User $u) {
                $p = $u->guestProfile;
                return $p ? $p->last_name . $p->first_name : $u->name;
            })
            ->values();

        $taggableGroups = GuestGroup::with('primaryGuest')
            ->get()
            ->sortBy(fn (GuestGroup $group) => $group->displayName())
            ->values();

        return view('admin.gallery', compact('photos', 'pending', 'guestApproved', 'taggableGuests', 'taggableGroups'));
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
    public function approve(Request $request, int $id)
    {
        $photo = GalleryPhoto::where('is_guest_upload', true)->findOrFail($id);
        $maxOrder = GalleryPhoto::max('sort_order') ?? 0;

        $photo->update([
            'status'     => 'approved',
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        $message = '写真を承認してギャラリーに追加しました';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'photo_id' => $photo->id,
                'status' => 'approved',
            ]);
        }

        return back()->with('success', $message);
    }

    /** ゲスト投稿を却下（ファイルは保持） */
    public function reject(Request $request, int $id)
    {
        $photo = GalleryPhoto::where('is_guest_upload', true)->findOrFail($id);
        $photo->update(['status' => 'rejected', 'is_active' => false]);

        $message = '写真を却下しました';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'photo_id' => $photo->id,
                'status' => 'rejected',
            ]);
        }

        return back()->with('success', $message);
    }

    /** 写真に写っている人物をタグ付け */
    public function tag(Request $request, int $id)
    {
        $photo = GalleryPhoto::where('status', 'approved')->findOrFail($id);

        $request->validate([
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'integer|exists:users,id',
            'group_ids'   => 'nullable|array',
            'group_ids.*' => 'string|exists:guest_groups,id',
        ]);

        $photo->taggedUsers()->sync($request->input('user_ids', []));
        $photo->taggedGroups()->sync($request->input('group_ids', []));
        $photo->load(['taggedUsers.guestProfile', 'taggedGroups.primaryGuest']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '写真のタグ付けを更新しました',
                'photo_id' => $photo->id,
                'tags' => $photo->taggedUsers->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->guestProfile?->fullName() ?: $user->name,
                    'type' => 'user',
                ])->values(),
                'groups' => $photo->taggedGroups->map(fn (GuestGroup $group) => [
                    'id' => $group->id,
                    'name' => $group->displayName(),
                    'type' => 'group',
                ])->values(),
            ]);
        }

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
