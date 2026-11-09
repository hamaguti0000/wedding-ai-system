<form method="POST" action="{{ route('guestbook.store') }}">
    @csrf
    @error('message')<span class="field-error" style="display:block;margin-bottom:8px;">{{ $message }}</span>@enderror
    <textarea name="message" class="gb-form-textarea" maxlength="500"
              placeholder="お二人へのメッセージ、仲間へのひとこと、なんでも！&#10;（最大500文字）"
              id="gbTextarea" oninput="updateCount(this)">{{ old('message', $myMessage?->message) }}</textarea>

    <span class="gb-sticker-label">スタンプ（任意）</span>
    <div class="gb-stickers">
        @foreach(['🌸','💍','🥂','💐','🎊','🎉','🕊️','💕','✨','🍀','🌈','🎶','🤍','💫','🌙','⭐','🫶','🥰','😊','🙏'] as $s)
        <input type="radio" name="sticker" id="s-{{ $loop->index }}" class="gb-sticker-opt"
               value="{{ $s }}" {{ old('sticker', $myMessage?->sticker) === $s ? 'checked' : '' }}>
        <label for="s-{{ $loop->index }}" class="gb-sticker-lbl">{{ $s }}</label>
        @endforeach
    </div>

    <div class="gb-form-actions">
        <button type="submit" class="gb-submit-btn">
            <i class="fa-solid fa-paper-plane"></i>
            {{ $myMessage ? '書き直す' : '送る' }}
        </button>
        <span class="gb-char-count" id="gbCount">{{ mb_strlen(old('message', $myMessage?->message ?? '')) }} / 500</span>
    </div>
</form>

<script>
function updateCount(el) {
    const len = [...el.value].length;
    const el2 = document.getElementById('gbCount');
    if (!el2) return;
    el2.textContent = len + ' / 500';
    el2.className = 'gb-char-count' + (len > 450 ? ' warn' : '');
}
</script>
