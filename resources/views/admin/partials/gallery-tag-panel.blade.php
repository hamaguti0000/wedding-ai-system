{{-- 写真に写っている人物のタグ付けパネル
     $photo: GalleryPhoto（taggedUsers をロード済み）
     $taggableGuests: Collection<User> --}}
@php
    $taggedIds = $photo->taggedUsers->pluck('id')->all();
    $taggedGroupIds = $photo->taggedGroups->pluck('id')->all();
    $taggedGroupNames = $photo->taggedGroups->map(fn($group) => $group->displayName())->unique()->values()->all();
@endphp
<div id="tag-{{ $photo->id }}" class="gl-tag-panel" data-photo-id="{{ $photo->id }}">
    <form method="POST" action="{{ route('admin.gallery.tag', $photo->id) }}" class="gl-tag-form" data-photo-id="{{ $photo->id }}">
        @csrf
        <div class="gl-tag-panel__head">
            <div>
                <strong>写っているゲスト・グループ</strong>
                <span class="gl-tag-selected-count">{{ count($taggedIds) + count($taggedGroupIds) }}件選択中</span>
            </div>
            <button type="button" class="gl-tag-clear">全解除</button>
        </div>

        <div class="gl-tag-selected" aria-live="polite">
            @foreach ($photo->taggedUsers as $tagged)
            <span class="gl-tag-chip" data-user-id="{{ $tagged->id }}">{{ $tagged->guestProfile?->fullName() ?: $tagged->name }}</span>
            @endforeach
            @foreach ($photo->taggedGroups->unique(fn($group) => $group->displayName()) as $group)
            <span class="gl-tag-chip gl-tag-chip--group" data-group-id="{{ $group->id }}">{{ $group->displayName() }}</span>
            @endforeach
        </div>

        @if ($taggableGuests->isNotEmpty())
        <input type="search" class="gl-tag-search" placeholder="名前・ふりがなで検索" autocomplete="off">
        @endif

        @if ($taggableGroups->isNotEmpty())
        <div class="gl-tag-panel__subhead">グループ</div>
        <div class="gl-tag-panel__list gl-tag-panel__list--groups">
            @foreach ($taggableGroups as $group)
            @php
                $groupName = $group->displayName();
                $searchName = strtolower($groupName . ' ' . $group->id);
            @endphp
            <label data-name="{{ $searchName }}" data-group-id="{{ $group->id }}" data-label="{{ $groupName }}">
                <input type="checkbox" name="group_ids[]" value="{{ $group->id }}"
                       {{ in_array($group->id, $taggedGroupIds, true) || in_array($groupName, $taggedGroupNames, true) ? 'checked' : '' }}>
                <span><strong>{{ $groupName }}</strong></span>
            </label>
            @endforeach
        </div>
        @endif

        <div class="gl-tag-panel__subhead">ゲスト</div>
        <div class="gl-tag-panel__list">
            @forelse ($taggableGuests as $guest)
            @php
                $guestName = $guest->guestProfile?->fullName() ?: $guest->name;
                $furigana = $guest->guestProfile?->furigana() ?? '';
                $searchName = strtolower($guestName . ' ' . $furigana . ' ' . ($guest->username ?? ''));
            @endphp
            <label data-name="{{ $searchName }}" data-user-id="{{ $guest->id }}" data-label="{{ $guestName }}">
                <input type="checkbox" name="user_ids[]" value="{{ $guest->id }}"
                       {{ in_array($guest->id, $taggedIds, true) ? 'checked' : '' }}>
                <span>
                    <strong>{{ $guestName }}</strong>
                    @if ($furigana)
                    <small>{{ $furigana }}</small>
                    @endif
                </span>
            </label>
            @empty
            <p style="font-size:0.78rem;color:#c0b0a0;">ゲストが登録されていません</p>
            @endforelse
        </div>
        <div class="gl-tag-panel__actions">
            <button type="submit" class="btn-primary gl-tag-save" style="padding:6px 16px;font-size:0.82rem;">保存</button>
            <span class="gl-tag-status" aria-live="polite"></span>
        </div>
    </form>
</div>
