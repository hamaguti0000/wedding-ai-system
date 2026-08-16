{{-- 写真に写っている人物のタグ付けパネル
     $photo: GalleryPhoto（taggedUsers をロード済み）
     $taggableGuests: Collection<User> --}}
@php
    $taggedIds = $photo->taggedUsers->pluck('id')->all();
@endphp
<div id="tag-{{ $photo->id }}" class="gl-tag-panel">
    <form method="POST" action="{{ route('admin.gallery.tag', $photo->id) }}">
        @csrf
        <div class="gl-tag-panel__list">
            @forelse ($taggableGuests as $guest)
            <label>
                <input type="checkbox" name="user_ids[]" value="{{ $guest->id }}"
                       {{ in_array($guest->id, $taggedIds, true) ? 'checked' : '' }}>
                {{ $guest->guestProfile?->fullName() ?: $guest->name }}
            </label>
            @empty
            <p style="font-size:0.78rem;color:#c0b0a0;">ゲストが登録されていません</p>
            @endforelse
        </div>
        <button type="submit" class="btn-primary" style="padding:6px 16px;font-size:0.82rem;">タグを保存</button>
    </form>
</div>
