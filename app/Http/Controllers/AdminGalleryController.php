<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use App\Models\GuestGroup;
use App\Models\User;
use App\Services\GalleryImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminGalleryController extends Controller
{
    public function index()
    {
        $hasGuestGroups = Schema::hasTable('guest_groups');
        $galleryRelations = ['taggedUsers.guestProfile'];
        if ($hasGuestGroups) {
            $galleryRelations[] = 'taggedGroups.primaryGuest';
        }

        $photos  = GalleryPhoto::where('is_guest_upload', false)
            ->with($galleryRelations)
            ->orderBy('sort_order')->orderBy('id')->get();
        if (! $hasGuestGroups) {
            $photos->each->setRelation('taggedGroups', collect());
        }

        $pending = GalleryPhoto::where('is_guest_upload', true)
            ->where('status', 'pending')
            ->with('uploader')
            ->orderByDesc('created_at')->get();

        $guestApprovedRelations = ['uploader', 'taggedUsers.guestProfile'];
        if ($hasGuestGroups) {
            $guestApprovedRelations[] = 'taggedGroups.primaryGuest';
        }

        $guestApproved = GalleryPhoto::where('is_guest_upload', true)
            ->whereIn('status', ['approved', 'rejected'])
            ->with($guestApprovedRelations)
            ->orderByDesc('created_at')->get();
        if (! $hasGuestGroups) {
            $guestApproved->each->setRelation('taggedGroups', collect());
        }

        $taggableGuests = User::where('role', 'guest')
            ->with('guestProfile')
            ->get()
            ->sortBy(function (User $u) {
                $p = $u->guestProfile;
                return $p ? $p->last_name . $p->first_name : $u->name;
            })
            ->values();

        $taggableGroups = $hasGuestGroups
            ? GuestGroup::with('primaryGuest')
                ->get()
                ->sortBy(fn (GuestGroup $group) => $group->displayName())
                ->unique(fn (GuestGroup $group) => $group->displayName())
                ->values()
            : collect();

        return view('admin.gallery', compact('photos', 'pending', 'guestApproved', 'taggableGuests', 'taggableGroups'));
    }

    public function store(Request $request, GalleryImageOptimizer $imageOptimizer)
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

        foreach ($request->file('photos') as $i => $file) {
            $path = $imageOptimizer->store($file, 'gallery');
            GalleryPhoto::create([
                'file_path'  => $path,
                'caption'    => $request->captions[$i] ?? null,
                'sort_order' => $maxOrder + $count + 1,
                'is_active'  => true,
                'status'     => 'approved',
            ]);
            $count++;
        }

        return back()->with('success', "{$count}枚の写真を追加しました");
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
        Storage::disk('public')->delete(array_filter([
            $photo->file_path,
            $photo->display_file_path,
        ]));
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
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $hasGuestGroups = Schema::hasTable('guest_groups');
        if ($hasGuestGroups) {
            $request->validate([
                'group_ids'   => 'nullable|array',
                'group_ids.*' => 'string|exists:guest_groups,id',
            ]);
        }

        $photo->taggedUsers()->sync($request->input('user_ids', []));
        if ($hasGuestGroups) {
            $photo->taggedGroups()->sync($this->expandGroupIdsByDisplayName($request->input('group_ids', [])));
            $photo->load(['taggedUsers.guestProfile', 'taggedGroups.primaryGuest']);
        } else {
            $photo->load('taggedUsers.guestProfile');
            $photo->setRelation('taggedGroups', collect());
        }

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
                'groups' => $photo->taggedGroups
                    ->map(fn (GuestGroup $group) => [
                        'id' => $group->id,
                        'name' => $group->displayName(),
                        'type' => 'group',
                    ])
                    ->unique('name')
                    ->values(),
            ]);
        }

        return back()->with('success', '写真のタグ付けを更新しました');
    }


    /**
     * 管理画面では同じ表示名のグループを1つにまとめて見せる。
     * 保存時は同名グループをすべて紐付け、同じ関係グループのゲスト全員に写真が届くようにする。
     */
    private function expandGroupIdsByDisplayName(array $groupIds): array
    {
        if (empty($groupIds)) {
            return [];
        }

        $allGroups = GuestGroup::with('primaryGuest')->get();
        $selectedNames = $allGroups
            ->whereIn('id', $groupIds)
            ->map(fn (GuestGroup $group) => $group->displayName())
            ->unique()
            ->values();

        return $allGroups
            ->filter(fn (GuestGroup $group) => $selectedNames->contains($group->displayName()))
            ->pluck('id')
            ->values()
            ->all();
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
