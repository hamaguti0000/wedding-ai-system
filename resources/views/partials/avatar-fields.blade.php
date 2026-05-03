@php
    $avatarType = old('avatar_type', $avatarType ?? 'initial');
    $avatarEmoji = old('avatar_emoji', $avatarEmoji ?? '');
    $avatarImageUrl = $avatarImageUrl ?? null;
    $avatarInitial = $avatarInitial ?? '?';
    $avatarTitle = $avatarTitle ?? 'アイコン';
    $avatarDesc = $avatarDesc ?? '写真、絵文字、イニシャルから選べます。';
    $avatarShowPhoto = $avatarImageUrl || $avatarType === \App\Models\User::AVATAR_PHOTO;
    $avatarEmojiGroups = \App\Models\User::avatarEmojiGroups();
    $currentEmojiCategory = '';
    foreach ($avatarEmojiGroups as $group) {
        if (array_key_exists($avatarEmoji, $group['items'])) {
            $currentEmojiCategory = $group['key'];
            break;
        }
    }
    $avatarEmojiCategory = old('avatar_emoji_category', $currentEmojiCategory ?: ($avatarEmojiGroups[0]['key'] ?? ''));
@endphp

<div class="avatar-settings"
     data-avatar-settings
     data-avatar-type="{{ $avatarType }}"
     data-avatar-emoji="{{ $avatarEmoji }}"
     data-avatar-emoji-category="{{ $avatarEmojiCategory }}"
     data-avatar-image="{{ $avatarImageUrl }}"
     data-avatar-initial="{{ $avatarInitial }}">
    <div class="avatar-settings__preview">
        <div class="avatar-preview__circle" data-avatar-preview>
            @if ($avatarType === \App\Models\User::AVATAR_PHOTO && $avatarImageUrl)
                <img src="{{ $avatarImageUrl }}" alt="">
            @elseif ($avatarType === \App\Models\User::AVATAR_EMOJI && $avatarEmoji)
                <span class="avatar-preview__circle-emoji">{{ $avatarEmoji }}</span>
            @else
                <span>{{ $avatarInitial }}</span>
            @endif
        </div>
        <div>
            <p class="avatar-settings__title">{{ $avatarTitle }}</p>
            <p class="avatar-settings__desc">{{ $avatarDesc }}</p>
        </div>
    </div>

    <div class="avatar-settings__types" role="radiogroup" aria-label="{{ $avatarTitle }}の種類">
        @foreach (\App\Models\User::avatarTypeOptions() as $value => $label)
        <label class="avatar-type-option">
            <input type="radio" name="avatar_type" value="{{ $value }}" {{ $avatarType === $value ? 'checked' : '' }}>
            <span>{{ $label }}</span>
        </label>
        @endforeach
    </div>

    <div class="avatar-settings__panel" data-avatar-emoji-panel {{ $avatarType === \App\Models\User::AVATAR_EMOJI ? '' : 'hidden' }}>
        <div class="avatar-settings__categories" role="tablist" aria-label="絵文字カテゴリ">
            @foreach ($avatarEmojiGroups as $group)
            <button type="button"
                    class="avatar-category-tab {{ $avatarEmojiCategory === $group['key'] ? 'is-active' : '' }}"
                    data-avatar-emoji-category-tab="{{ $group['key'] }}">
                {{ $group['label'] }}
            </button>
            @endforeach
        </div>
        @foreach ($avatarEmojiGroups as $group)
        <div class="avatar-settings__emoji {{ $avatarEmojiCategory === $group['key'] ? 'is-active' : '' }}"
             data-avatar-emoji-category-panel="{{ $group['key'] }}"
             role="radiogroup"
             aria-label="{{ $group['label'] }}の絵文字アイコン"
             @if ($avatarEmojiCategory !== $group['key']) hidden @endif>
            @foreach ($group['items'] as $emoji => $label)
            <label class="avatar-emoji-option">
                <input type="radio"
                       name="avatar_emoji"
                       value="{{ $emoji }}"
                       data-avatar-emoji-category="{{ $group['key'] }}"
                       {{ $avatarEmoji === $emoji ? 'checked' : '' }}>
                <span class="avatar-emoji-option__glyph">{{ $emoji }}</span>
                <small>{{ $label }}</small>
            </label>
            @endforeach
        </div>
        @endforeach
        @error('avatar_emoji')<span class="field-error">{{ $message }}</span>@enderror
    </div>

    <div class="avatar-settings__panel avatar-settings__photo" data-avatar-photo-panel {{ $avatarType === \App\Models\User::AVATAR_PHOTO ? '' : 'hidden' }}>
        <input type="file" name="avatar_image" accept="image/*" class="avatar-settings__file">
        @if ($avatarShowPhoto)
        <p class="avatar-settings__current">現在の写真を保持中です。新しい画像を選ぶと差し替わります。</p>
        @else
        <p class="avatar-settings__current">写真を選ぶと、ここに表示されます。</p>
        @endif
        @error('avatar_image')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
