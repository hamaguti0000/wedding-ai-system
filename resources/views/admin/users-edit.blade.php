@extends('layouts.app')
@section('title', 'ユーザー編集 | Admin')

@push('styles')
<style>
.edit-wrap {
    max-width: 720px;
    margin: 24px auto 80px;
    padding: 0 14px;
    font-family: 'Noto Sans JP', sans-serif;
}
.edit-wrap h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #b38b59;
    margin-bottom: 6px;
}
.edit-wrap .page-sub {
    font-size: 0.85rem;
    color: #999;
    margin-bottom: 24px;
}
.admin-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.admin-nav a {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    border-radius: 6px;
    font-size: 0.82rem;
    font-weight: 500;
    text-decoration: none;
    background: #fef9f0;
    color: #b38b59;
    border: 1px solid #e8d5b7;
    transition: background 0.2s;
    white-space: nowrap;
}
.admin-nav a:hover { background: #b38b59; color: #fff; }

/* ── カード ── */
.edit-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px 16px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    margin-bottom: 20px;
}
.edit-card h2 {
    font-size: 0.78rem;
    font-weight: 700;
    color: #b38b59;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f0ebe3;
}
.fg { display: grid; gap: 14px; margin-bottom: 14px; }
.fg-2 { grid-template-columns: 1fr 1fr; }

.form-group label {
    display: block;
    font-size: 0.8rem;
    color: #7a6a5a;
    margin-bottom: 5px;
    font-weight: 500;
}
.form-group input, .form-group select {
    width: 100%;
    padding: 10px 13px;
    border: 1px solid #e0d0bc;
    border-radius: 6px;
    font-size: 0.95rem;
    font-family: 'Noto Sans JP', sans-serif;
    color: #3d2f25;
    background: #fffdf9;
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.form-group input:focus, .form-group select:focus {
    border-color: #b38b59;
    outline: none;
    box-shadow: 0 0 0 3px rgba(179,139,89,0.12);
}
.form-group .field-note {
    font-size: 0.76rem;
    color: #b0a090;
    margin-top: 4px;
}
.field-error { color: #c0392b; font-size: 0.78rem; margin-top: 4px; display: block; }

/* ロール切替 */
.role-toggle { display: flex; border: 1px solid #e0d0bc; border-radius: 6px; overflow: hidden; }
.role-toggle input { display: none; }
.role-toggle label {
    flex: 1; text-align: center; padding: 10px 6px;
    font-size: 0.85rem; font-weight: 500; color: #9b8573;
    background: #fffdf9; cursor: pointer;
    transition: background 0.15s, color 0.15s;
    margin: 0; line-height: 1;
}
.role-toggle label:not(:last-child) { border-right: 1px solid #e0d0bc; }
.role-toggle input:checked + label { background: #b38b59; color: #fff; }

/* ゲスト専用フィールド */
.guest-fields.hidden { opacity: 0.35; pointer-events: none; }

/* ── ボタン ── */
.btn-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
.btn-save {
    flex: 1;
    padding: 12px 20px;
    background: #b38b59;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.92rem;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    min-height: 48px;
}
.btn-save:hover { background: #9a7447; }
.btn-cancel {
    padding: 12px 20px;
    background: transparent;
    border: 1px solid #e0d0bc;
    color: #9b8573;
    border-radius: 6px;
    font-size: 0.92rem;
    font-family: 'Noto Sans JP', sans-serif;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: border-color 0.2s;
    min-height: 48px;
}
.btn-cancel:hover { border-color: #b38b59; color: #b38b59; }

.alert-error {
    background: #fdf2f2; border: 1px solid #f5b7b1; color: #c0392b;
    padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 0.88rem;
}

@media (min-width: 768px) {
    .edit-wrap { margin-top: 40px; padding: 0 20px; }
    .edit-wrap h1 { font-size: 1.8rem; }
    .edit-card { padding: 28px 32px; }
}
@media (max-width: 767px) {
    .fg-2 { grid-template-columns: 1fr; }
    .btn-row { flex-direction: column; }
    .btn-save, .btn-cancel { width: 100%; }
}
</style>
@endpush

@section('content')
<div class="edit-wrap">

    <h1>ユーザー編集</h1>
    <p class="page-sub">{{ $user->username }} の情報を編集します</p>

    <nav class="admin-nav">
        <a href="{{ route('admin.users') }}">
            <i class="fa-solid fa-arrow-left"></i> ユーザー管理に戻る
        </a>
    </nav>

    @if ($errors->any())
    <div class="alert-error">
        <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
        @csrf @method('PATCH')

        {{-- ── ロール ── --}}
        <div class="edit-card">
            <h2>ロール</h2>
            <div class="form-group">
                <div class="role-toggle">
                    <input type="radio" name="role" id="role_guest" value="guest"
                        {{ old('role', $user->role) === 'guest' ? 'checked' : '' }}>
                    <label for="role_guest"><i class="fa-solid fa-user"></i> ゲスト</label>
                    <input type="radio" name="role" id="role_admin" value="admin"
                        {{ old('role', $user->role) === 'admin' ? 'checked' : '' }}>
                    <label for="role_admin"><i class="fa-solid fa-user-shield"></i> 管理者</label>
                </div>
            </div>
        </div>

        {{-- ── ログイン情報 ── --}}
        <div class="edit-card">
            <h2>ログイン情報</h2>
            <div class="form-group" style="margin-bottom:14px;">
                <label>ユーザー名</label>
                <input type="text" name="username"
                    value="{{ old('username', $user->username) }}"
                    autocomplete="off" required>
                @error('username')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <div class="fg fg-2">
                <div class="form-group">
                    <label>新しいパスワード</label>
                    <input type="password" name="password" autocomplete="new-password" placeholder="変更しない場合は空欄">
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>パスワード（確認）</label>
                    <input type="password" name="password_confirmation" autocomplete="new-password" placeholder="確認用">
                </div>
            </div>
        </div>

        {{-- ── ゲスト情報 ── --}}
        <div class="edit-card guest-fields" id="guestFields">
            <h2>ゲスト情報</h2>
            <div class="fg fg-2" style="margin-bottom:14px;">
                <div class="form-group">
                    <label>姓</label>
                    <input type="text" name="last_name"
                        value="{{ old('last_name', $user->guestProfile?->last_name) }}"
                        placeholder="山田">
                </div>
                <div class="form-group">
                    <label>名</label>
                    <input type="text" name="first_name"
                        value="{{ old('first_name', $user->guestProfile?->first_name) }}"
                        placeholder="太郎">
                </div>
                <div class="form-group">
                    <label>フリガナ（姓）</label>
                    <input type="text" name="furigana_sei"
                        value="{{ old('furigana_sei', $user->guestProfile?->furigana_sei) }}"
                        placeholder="ヤマダ">
                </div>
                <div class="form-group">
                    <label>フリガナ（名）</label>
                    <input type="text" name="furigana_mei"
                        value="{{ old('furigana_mei', $user->guestProfile?->furigana_mei) }}"
                        placeholder="タロウ">
                </div>
            </div>
            <div class="fg fg-2">
                <div class="form-group">
                    <label>お立場</label>
                    <select name="guest_side">
                        <option value="">— 未設定 —</option>
                        <option value="groom" {{ old('guest_side', $user->guestProfile?->guest_side) === 'groom' ? 'selected' : '' }}>新郎側</option>
                        <option value="bride" {{ old('guest_side', $user->guestProfile?->guest_side) === 'bride' ? 'selected' : '' }}>新婦側</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ご関係</label>
                    <select name="relationship">
                        <option value="">— 未設定 —</option>
                        <option value="friend"    {{ old('relationship', $user->guestProfile?->relationship) === 'friend'    ? 'selected' : '' }}>友人・知人</option>
                        <option value="family"    {{ old('relationship', $user->guestProfile?->relationship) === 'family'    ? 'selected' : '' }}>親族</option>
                        <option value="colleague" {{ old('relationship', $user->guestProfile?->relationship) === 'colleague' ? 'selected' : '' }}>職場関係</option>
                        <option value="other"     {{ old('relationship', $user->guestProfile?->relationship) === 'other'     ? 'selected' : '' }}>その他</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn-save">
                <i class="fa-solid fa-floppy-disk"></i> 保存する
            </button>
            <a href="{{ route('admin.users') }}" class="btn-cancel">キャンセル</a>
        </div>

    </form>
</div>

<script>
document.querySelectorAll('input[name="role"]').forEach(r => {
    r.addEventListener('change', () => {
        document.getElementById('guestFields').classList.toggle('hidden', r.value !== 'guest');
    });
});
(function() {
    const checked = document.querySelector('input[name="role"]:checked');
    if (checked && checked.value !== 'guest') {
        document.getElementById('guestFields').classList.add('hidden');
    }
})();
</script>
@endsection
