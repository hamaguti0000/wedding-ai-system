@extends('layouts.app')
@section('title', 'ユーザー編集 | Admin')

@push('styles')
<style>
/* ユーザー編集固有スタイル */
.edit-wrap .page-sub { font-size: 0.85rem; color: #999; margin-bottom: 20px; }

.edit-card { background: #fff; border-radius: 14px; padding: 24px 28px; box-shadow: 0 4px 14px rgba(0,0,0,0.07); margin-bottom: 20px; }
.edit-card h2 { font-size: 0.78rem; font-weight: 700; color: #b38b59; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #f0ebe3; }

.fg   { display: grid; gap: 14px; margin-bottom: 14px; }
.fg-2 { grid-template-columns: 1fr 1fr; }

.role-toggle { display: flex; border: 1px solid #e0d0bc; border-radius: 6px; overflow: hidden; }
.role-toggle input { display: none; }
.role-toggle label { flex: 1; text-align: center; padding: 10px 6px; font-size: 0.85rem; font-weight: 500; color: #9b8573; background: #fffdf9; cursor: pointer; transition: background 0.15s, color 0.15s; margin: 0; line-height: 1.2; }
.role-toggle label:not(:last-child) { border-right: 1px solid #e0d0bc; }
.role-toggle input:checked + label { background: #b38b59; color: #fff; }

.guest-fields.hidden { opacity: 0.35; pointer-events: none; }

.btn-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px; }
.btn-save { flex: 1; }

.back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 0.84rem; color: #b38b59; text-decoration: none; margin-bottom: 20px; transition: color 0.15s; }
.back-link:hover { color: #9a7447; }

@media (max-width: 767px) {
    .edit-card { padding: 18px 16px; }
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

    <a href="{{ route('admin.users') }}" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> ユーザー管理に戻る
    </a>

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
            <div class="fg fg-2" style="margin-bottom:14px;">
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
            <div class="form-group" style="margin-bottom:14px;">
                <label>ご関係の詳細</label>
                <input type="text" name="relationship_detail"
                    value="{{ old('relationship_detail', $user->guestProfile?->relationship_detail) }}"
                    placeholder="例：大学時代の友人">
            </div>
        </div>

        {{-- ── 出欠・参加情報 ── --}}
        <div class="edit-card guest-fields" id="attendanceFields">
            <h2>出欠・参加情報</h2>
            <div class="fg fg-2" style="margin-bottom:14px;">
                <div class="form-group">
                    <label>出欠状況</label>
                    <select name="participation">
                        <option value="pending"   {{ old('participation', $user->guestProfile?->participation ?? 'pending') === 'pending'   ? 'selected' : '' }}>未回答</option>
                        <option value="attending" {{ old('participation', $user->guestProfile?->participation) === 'attending' ? 'selected' : '' }}>出席</option>
                        <option value="declining" {{ old('participation', $user->guestProfile?->participation) === 'declining' ? 'selected' : '' }}>欠席</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>食物アレルギー</label>
                    <select name="has_allergy">
                        <option value="0" {{ old('has_allergy', $user->guestProfile?->has_allergy ? '1' : '0') === '0' ? 'selected' : '' }}>なし</option>
                        <option value="1" {{ old('has_allergy', $user->guestProfile?->has_allergy ? '1' : '0') === '1' ? 'selected' : '' }}>あり</option>
                    </select>
                </div>
            </div>
            <div class="fg fg-2" style="margin-bottom:14px;">
                <div class="form-group">
                    <label>出席人数（合計）</label>
                    <input type="number" name="attending_count" min="0" max="20"
                        value="{{ old('attending_count', $user->guestProfile?->attending_count ?? 0) }}">
                </div>
                <div class="form-group">
                    <label>うちお子様（12歳以下）</label>
                    <input type="number" name="children_count" min="0" max="10"
                        value="{{ old('children_count', $user->guestProfile?->children_count ?? 0) }}">
                </div>
            </div>
            <div class="form-group">
                <label>アレルギーの詳細</label>
                <textarea name="allergy_notes" rows="3" style="width:100%;padding:10px 13px;border:1px solid #e0d0bc;border-radius:6px;font-family:'Noto Sans JP',sans-serif;font-size:0.92rem;box-sizing:border-box;resize:vertical;"
                    placeholder="例：卵・乳製品アレルギー">{{ old('allergy_notes', $user->guestProfile?->allergy_notes) }}</textarea>
            </div>
        </div>

        {{-- ── 連絡先 ── --}}
        <div class="edit-card guest-fields" id="contactFields">
            <h2>連絡先</h2>
            <div class="fg fg-2" style="margin-bottom:14px;">
                <div class="form-group">
                    <label>電話番号</label>
                    <input type="tel" name="phone"
                        value="{{ old('phone', $user->guestProfile?->phone) }}"
                        placeholder="090-0000-0000">
                </div>
                <div class="form-group">
                    <label>郵便番号</label>
                    <input type="text" name="postal_code"
                        value="{{ old('postal_code', $user->guestProfile?->postal_code) }}"
                        placeholder="000-0000" maxlength="8">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:14px;">
                <label>ご住所</label>
                <input type="text" name="address"
                    value="{{ old('address', $user->guestProfile?->address) }}"
                    placeholder="東京都◯◯区◯◯町1-2-3">
            </div>
            <div class="form-group">
                <label>メッセージ（ゲストから）</label>
                <textarea name="notes" rows="3" style="width:100%;padding:10px 13px;border:1px solid #e0d0bc;border-radius:6px;font-family:'Noto Sans JP',sans-serif;font-size:0.92rem;box-sizing:border-box;resize:vertical;"
                    placeholder="ゲストからのメッセージ">{{ old('notes', $user->guestProfile?->notes) }}</textarea>
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
function toggleGuestFields(isGuest) {
    ['guestFields','attendanceFields','contactFields'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('hidden', !isGuest);
    });
}
document.querySelectorAll('input[name="role"]').forEach(r => {
    r.addEventListener('change', () => toggleGuestFields(r.value === 'guest'));
});
(function() {
    const checked = document.querySelector('input[name="role"]:checked');
    toggleGuestFields(!checked || checked.value === 'guest');
})();
</script>
@endsection
