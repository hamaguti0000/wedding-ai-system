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

        $photos  = GalleryPhoto::where('status', 'approved')
            ->with(array_merge($galleryRelations, ['uploader']))
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
            ->where('status', 'rejected')
            ->with($guestApprovedRelations)
            ->orderByDesc('created_at')->get();
        if (! $hasGuestGroups) {
            $guestApproved->each->setRelation('taggedGroups', collect());
        }

        $categoryOptions = GalleryPhoto::categoryOptions();
        $sourceOptions = GalleryPhoto::sourceOptions();

        // 「まだタグが付いていない写真」を一覧の先頭で案内し、そのままタグ付けに入れるようにする
        $untaggedPhotos = $photos->filter(fn (GalleryPhoto $p) => $p->taggedUsers->isEmpty() && $p->taggedGroups->isEmpty());
        $untaggedCount = $untaggedPhotos->count();
        $firstUntaggedId = $untaggedPhotos->first()?->id;

        return view('admin.gallery', compact(
            'photos', 'pending', 'guestApproved', 'categoryOptions', 'sourceOptions',
            'untaggedCount', 'firstUntaggedId'
        ));
    }

    /**
     * 1枚ずつタグ付けする専用画面。
     * 一覧に全ゲスト分のチェックボックスを写真の数だけ描画すると
     * ページが数千行規模になり操作できなくなるため、タグ付けはこの画面に分離している。
     */
    public function tagEditor(int $id)
    {
        $hasGuestGroups = Schema::hasTable('guest_groups');

        $relations = ['taggedUsers.guestProfile'];
        if ($hasGuestGroups) {
            $relations[] = 'taggedGroups.primaryGuest';
        }

        $photo = GalleryPhoto::where('status', 'approved')->with($relations)->findOrFail($id);
        if (! $hasGuestGroups) {
            $photo->setRelation('taggedGroups', collect());
        }

        // 並び順どおりの「待ち行列」を作り、前後移動と進捗表示に使う
        $queue = GalleryPhoto::where('status', 'approved')
            ->withCount('taggedUsers')
            ->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'sort_order']);

        $position = $queue->search(fn ($item) => $item->id === $photo->id);
        $prevPhoto = $position > 0 ? $queue[$position - 1] : null;
        $nextPhoto = $position !== false && $position < $queue->count() - 1 ? $queue[$position + 1] : null;

        // まだタグが付いていない次の写真（保存後のジャンプ先）
        $nextUntagged = $queue
            ->filter(fn ($item) => $item->tagged_users_count === 0 && $item->id !== $photo->id)
            ->first();

        return view('admin.gallery-tag', [
            'photo'          => $photo,
            'taggableGuests' => $this->taggableGuests(),
            'taggableGroups' => $this->taggableGroups($hasGuestGroups),
            'prevPhoto'      => $prevPhoto,
            'nextPhoto'      => $nextPhoto,
            'nextUntagged'   => $nextUntagged,
            'position'       => $position === false ? 0 : $position + 1,
            'totalCount'     => $queue->count(),
            'untaggedCount'  => $queue->where('tagged_users_count', 0)->count(),
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function taggableGuests()
    {
        return User::where('role', 'guest')
            ->with('guestProfile')
            ->get()
            ->sortBy(function (User $u) {
                $p = $u->guestProfile;
                return $p ? $p->last_name . $p->first_name : $u->name;
            })
            ->values();
    }

    /** @return \Illuminate\Support\Collection<int, GuestGroup> */
    private function taggableGroups(bool $hasGuestGroups)
    {
        if (! $hasGuestGroups) {
            return collect();
        }

        return GuestGroup::with('primaryGuest')
            ->get()
            ->sortBy(fn (GuestGroup $group) => $group->displayName())
            ->unique(fn (GuestGroup $group) => $group->displayName())
            ->values();
    }

    public function store(Request $request, GalleryImageOptimizer $imageOptimizer)
    {
        $request->validate([
            'photos'          => 'required|array|max:20',
            'photos.*'        => 'required|image|mimes:jpeg,png,webp,gif|max:10240',
            'captions'        => 'nullable|array',
            'captions.*'      => 'nullable|string|max:200',
            'gallery_category'=> 'nullable|string|in:' . implode(',', array_keys(GalleryPhoto::categoryOptions())),
            'photo_source'    => 'nullable|string|in:photographer,admin',
        ], [
            'photos.required'   => '画像を選択してください',
            'photos.*.image'    => '画像ファイルを選択してください',
            'photos.*.max'      => '1枚10MB以内にしてください',
        ]);

        $files = $request->file('photos');
        $count = 0;
        GalleryPhoto::where('status', 'approved')->increment('sort_order', count($files));

        foreach ($files as $i => $file) {
            $path = $imageOptimizer->store($file, 'gallery');
            GalleryPhoto::create([
                'file_path'  => $path,
                'caption'    => $request->captions[$i] ?? null,
                'gallery_category' => $request->input('gallery_category', 'other'),
                'photo_source' => $request->input('photo_source', 'photographer'),
                'sort_order' => $count + 1,
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
        $request->validate([
            'caption' => 'nullable|string|max:200',
            'gallery_category' => 'nullable|string|in:' . implode(',', array_keys(GalleryPhoto::categoryOptions())),
            'photo_source' => 'nullable|string|in:photographer,admin,guest',
        ]);

        $photo->update([
            'caption'   => $request->caption ?: null,
            'gallery_category' => $request->input('gallery_category', $photo->gallery_category ?: 'other'),
            'photo_source' => $photo->is_guest_upload ? 'guest' : $request->input('photo_source', $photo->photo_source ?: 'admin'),
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
        GalleryPhoto::where('status', 'approved')->increment('sort_order');

        $photo->update([
            'status'     => 'approved',
            'is_active'  => true,
            'photo_source' => 'guest',
            'sort_order' => 1,
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

        // タグ付け専用画面からは、続けて次の写真へ送る
        if ($request->filled('next_photo_id')) {
            return redirect()
                ->route('admin.gallery.tag.edit', $request->input('next_photo_id'))
                ->with('success', '保存しました');
        }

        if ($request->input('after_save') === 'index') {
            return redirect()
                ->route('admin.gallery')
                ->with('success', '写真のタグ付けを更新しました');
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

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:gallery_photos,id',
        ]);

        foreach (array_values($validated['order']) as $index => $photoId) {
            GalleryPhoto::where('status', 'approved')
                ->whereKey($photoId)
                ->update(['sort_order' => $index + 1]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => '表示順を保存しました']);
        }

        return back()->with('success', '表示順を保存しました');
    }

    public function moveUp(int $id)
    {
        $photo = GalleryPhoto::where('status', 'approved')->findOrFail($id);
        $prev  = GalleryPhoto::where('status', 'approved')
            ->where('sort_order', '<', $photo->sort_order)
            ->orderByDesc('sort_order')->first();
        if ($prev) {
            [$photo->sort_order, $prev->sort_order] = [$prev->sort_order, $photo->sort_order];
            $photo->save(); $prev->save();
        }
        return back();
    }

    public function moveDown(int $id)
    {
        $photo = GalleryPhoto::where('status', 'approved')->findOrFail($id);
        $next  = GalleryPhoto::where('status', 'approved')
            ->where('sort_order', '>', $photo->sort_order)
            ->orderBy('sort_order')->first();
        if ($next) {
            [$photo->sort_order, $next->sort_order] = [$next->sort_order, $photo->sort_order];
            $photo->save(); $next->save();
        }
        return back();
    }
}
