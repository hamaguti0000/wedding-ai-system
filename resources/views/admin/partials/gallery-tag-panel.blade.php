{{-- 写真に写っている人物のタグ付けパネル
     $photo: GalleryPhoto（taggedUsers をロード済み）
     $taggableGuests: Collection<User> --}}
@php
    $taggedIds = $photo->taggedUsers->pluck('id')->all();
@endphp
<div id="tag-{{ $photo->id }}" class="gl-tag-panel" data-photo-id="{{ $photo->id }}">
    <form method="POST" action="{{ route('admin.gallery.tag', $photo->id) }}" class="gl-tag-form" data-photo-id="{{ $photo->id }}">
        @csrf
        <div class="gl-tag-panel__head">
            <div>
                <strong>写っているゲスト</strong>
                <span class="gl-tag-selected-count">{{ count($taggedIds) }}名選択中</span>
            </div>
            <button type="button" class="gl-tag-clear">全解除</button>
        </div>

        <div class="gl-tag-selected" aria-live="polite">
            @foreach ($photo->taggedUsers as $tagged)
            <span class="gl-tag-chip" data-user-id="{{ $tagged->id }}">{{ $tagged->guestProfile?->fullName() ?: $tagged->name }}</span>
            @endforeach
        </div>

        @if ($taggableGuests->isNotEmpty())
        <input type="search" class="gl-tag-search" placeholder="名前・ふりがなで検索" autocomplete="off">
        @endif

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
            <button type="submit" class="btn-primary gl-tag-save" style="padding:6px 16px;font-size:0.82rem;">Ajaxで保存</button>
            <span class="gl-tag-status" aria-live="polite"></span>
        </div>
    </form>
</div>
