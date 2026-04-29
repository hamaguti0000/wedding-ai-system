@extends('layouts.app')
@section('title', 'ユーザー管理 | Admin')

@push('styles')
<style>
/* ── ベース ── */
.users-wrap {
    max-width: 960px;
    margin: 32px auto 80px;
    padding: 0 16px;
    font-family: 'Noto Sans JP', sans-serif;
}
.users-wrap h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    color: #b38b59;
    margin-bottom: 6px;
}

/* ── 管理ナビ ── */
.admin-nav {
    display: flex;
    gap: 8px;
    margin-bottom: 28px;
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
.admin-nav a.active,
.admin-nav a:hover { background: #b38b59; color: #fff; }

/* ── カード ── */
.card {
    background: #fff;
    border-radius: 14px;
    padding: 24px 28px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    margin-bottom: 28px;
}
.card-title {
    font-size: 0.78rem;
    font-weight: 700;
    color: #b38b59;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 20px;
}

/* ── フォームグリッド ── */
.fg { display: grid; gap: 12px; margin-bottom: 12px; }
.fg-2 { grid-template-columns: 1fr 1fr; }
.fg-3 { grid-template-columns: 1fr 1fr 1fr; }
.fg-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
.form-group label {
    display: block;
    font-size: 0.8rem;
    color: #7a6a5a;
    margin-bottom: 5px;
    font-weight: 500;
}
.form-group input,
.form-group select {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #e0d0bc;
    border-radius: 6px;
    font-size: 0.92rem;
    font-family: 'Noto Sans JP', sans-serif;
    color: #3d2f25;
    background: #fffdf9;
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.form-group input:focus,
.form-group select:focus {
    border-color: #b38b59;
    outline: none;
    box-shadow: 0 0 0 3px rgba(179,139,89,0.12);
}
.req { color: #c0392b; margin-left: 3px; }
.field-error { color: #c0392b; font-size: 0.78rem; margin-top: 4px; display: block; }

/* ロール切替 */
.role-toggle {
    display: flex;
    gap: 0;
    border: 1px solid #e0d0bc;
    border-radius: 6px;
    overflow: hidden;
}
.role-toggle input { display: none; }
.role-toggle label {
    flex: 1;
    text-align: center;
    padding: 9px 6px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #9b8573;
    background: #fffdf9;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    border: none;
    margin: 0;
    line-height: 1;
}
.role-toggle label:not(:last-child) { border-right: 1px solid #e0d0bc; }
.role-toggle input:checked + label { background: #b38b59; color: #fff; }

/* ゲスト専用フィールド */
.guest-fields { transition: opacity 0.2s; }
.guest-fields.hidden { opacity: 0.35; pointer-events: none; }

/* ── ボタン ── */
.btn-primary {
    padding: 10px 28px;
    background: #b38b59;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.9rem;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-primary:hover { background: #9a7447; }
.btn-sm {
    padding: 4px 12px;
    font-size: 0.78rem;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    transition: background 0.2s;
    white-space: nowrap;
}
.btn-sm-pw  { background: #f0f4ff; color: #3a5bd9; border: 1px solid #c5d0f8; }
.btn-sm-pw:hover  { background: #dce5ff; }
.btn-sm-del { background: transparent; color: #c0392b; border: 1px solid #e8b4b4; }
.btn-sm-del:hover { background: #fdf2f2; }

/* ── アラート ── */
.alert {
    padding: 11px 16px;
    border-radius: 8px;
    margin-bottom: 18px;
    font-size: 0.88rem;
}
.alert-success { background: #eafaf1; border: 1px solid #a9dfbf; color: #1e8449; }
.alert-error   { background: #fdf2f2; border: 1px solid #f5b7b1; color: #c0392b; }

/* ── ユーザーテーブル ── */
.user-table-wrap {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    overflow: hidden;
}
.user-table-wrap .card-title { padding: 22px 28px 0; margin-bottom: 16px; }
.table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
table { width: 100%; border-collapse: collapse; font-size: 0.88rem; min-width: 500px; }
th {
    background: #fdf6ee;
    color: #b38b59;
    font-weight: 700;
    padding: 10px 16px;
    text-align: left;
    border-bottom: 2px solid #eedfc4;
    white-space: nowrap;
}
td { padding: 10px 16px; border-bottom: 1px solid #f0f0f0; color: #333; vertical-align: middle; }
tr:last-child td { border-bottom: none; }

/* パスワード変更行 */
.pw-row { display: none; background: #fdf6ee; }
.pw-row.open { display: table-row; }
.pw-form {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    padding: 4px 0;
}
.pw-form input {
    padding: 6px 10px;
    border: 1px solid #e0d0bc;
    border-radius: 5px;
    font-size: 0.85rem;
    width: 160px;
    min-width: 100px;
}
.pw-form input:focus { border-color: #b38b59; outline: none; }
.pw-form .btn-sm-pw { padding: 6px 14px; }

/* バッジ */
.badge {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 700;
    white-space: nowrap;
}
.badge.attending { background: #eafaf1; color: #27ae60; }
.badge.declining  { background: #fdf2f2; color: #e74c3c; }
.badge.pending    { background: #fef9ee; color: #f39c12; }
.badge.admin-role { background: #f0edff; color: #6c3fd9; }
.badge.guest-role { background: #fef9f0; color: #b38b59; }
.text-muted { color: #aaa; font-size: 0.82rem; }

.empty-state { text-align: center; padding: 48px; color: #aaa; }

/* ── レスポンシブ ── */
@media (max-width: 767px) {
    .users-wrap { margin-top: 20px; padding: 0 12px; }
    .users-wrap h1 { font-size: 1.35rem; }
    .card { padding: 18px 16px; }
    .fg-2, .fg-3, .fg-4 { grid-template-columns: 1fr; }
    .fg-2-sm { grid-template-columns: 1fr 1fr; }
    th, td { padding: 8px 10px; font-size: 0.82rem; }
    .user-table-wrap .card-title { padding: 16px 16px 0; }
    .col-md-hide { display: none; }
    table { min-width: 360px; }
    .pw-form input { width: 130px; }
}
@media (min-width: 768px) {
    .fg-2 { grid-template-columns: 1fr 1fr; }
    .fg-3 { grid-template-columns: 1fr 1fr 1fr; }
    .fg-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }
}
</style>
@endpush

@section('content')
<div class="users-wrap">

    <h1>ユーザー管理</h1>

    <nav class="admin-nav">
        <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-list-check"></i> ゲスト一覧</a>
        <a href="{{ route('admin.users') }}" class="active"><i class="fa-solid fa-users"></i> ユーザー管理</a>
        <a href="{{ route('admin.seating') }}"><i class="fa-solid fa-chair"></i> 席次表</a>
        <a href="{{ route('admin.settings') }}"><i class="fa-solid fa-gear"></i> 式の情報</a>
    </nav>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- ── 新規登録フォーム ── --}}
    <div class="card">
        <p class="card-title">新規ユーザー登録</p>

        <form method="POST" action="{{ route('admin.users.store') }}" id="createForm">
            @csrf

            {{-- ロール選択 --}}
            <div class="fg fg-2" style="margin-bottom:16px;">
                <div class="form-group">
                    <label>ロール <span class="req">*</span></label>
                    <div class="role-toggle">
                        <input type="radio" name="role" id="role_guest" value="guest"
                            {{ old('role','guest') === 'guest' ? 'checked' : '' }}>
                        <label for="role_guest"><i class="fa-solid fa-user"></i> ゲスト</label>
                        <input type="radio" name="role" id="role_admin" value="admin"
                            {{ old('role') === 'admin' ? 'checked' : '' }}>
                        <label for="role_admin"><i class="fa-solid fa-user-shield"></i> 管理者</label>
                    </div>
                    @error('role')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div></div>
            </div>

            {{-- ログイン情報 --}}
            <div class="fg fg-2" style="margin-bottom:16px;">
                <div class="form-group">
                    <label>ユーザー名 <span class="req">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        placeholder="yamada_taro" autocomplete="off">
                    @error('username')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>パスワード <span class="req">*</span></label>
                    <input type="text" name="password" value="{{ old('password') }}"
                        placeholder="6文字以上" autocomplete="off">
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- ゲスト専用フィールド --}}
            <div class="guest-fields" id="guestFields">
                <div class="fg fg-4" style="margin-bottom:12px;">
                    <div class="form-group">
                        <label>姓</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="山田">
                    </div>
                    <div class="form-group">
                        <label>名</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="太郎">
                    </div>
                    <div class="form-group">
                        <label>フリガナ（姓）</label>
                        <input type="text" name="furigana_sei" value="{{ old('furigana_sei') }}" placeholder="ヤマダ">
                    </div>
                    <div class="form-group">
                        <label>フリガナ（名）</label>
                        <input type="text" name="furigana_mei" value="{{ old('furigana_mei') }}" placeholder="タロウ">
                    </div>
                </div>
                <div class="fg fg-2">
                    <div class="form-group">
                        <label>お立場</label>
                        <select name="guest_side">
                            <option value="">— 未設定 —</option>
                            <option value="groom" {{ old('guest_side') === 'groom' ? 'selected' : '' }}>新郎側</option>
                            <option value="bride" {{ old('guest_side') === 'bride' ? 'selected' : '' }}>新婦側</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>ご関係</label>
                        <select name="relationship">
                            <option value="">— 未設定 —</option>
                            <option value="friend"    {{ old('relationship') === 'friend'    ? 'selected' : '' }}>友人・知人</option>
                            <option value="family"    {{ old('relationship') === 'family'    ? 'selected' : '' }}>親族</option>
                            <option value="colleague" {{ old('relationship') === 'colleague' ? 'selected' : '' }}>職場関係</option>
                            <option value="other"     {{ old('relationship') === 'other'     ? 'selected' : '' }}>その他</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top:12px;">
                <i class="fa-solid fa-user-plus"></i> 登録する
            </button>
        </form>
    </div>

    {{-- ── ユーザー一覧 ── --}}
    <div class="user-table-wrap">
        <p class="card-title">登録済みユーザー（{{ $users->count() }}名）</p>

        @if ($users->isEmpty())
        <div class="empty-state">まだユーザーが登録されていません</div>
        @else
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ユーザー名</th>
                    <th>氏名</th>
                    <th class="col-md-hide">ロール</th>
                    <th class="col-md-hide">出欠</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                @php
                    $p = $user->guestProfile;
                    $status = $p?->participation ?? 'pending';
                @endphp
                <tr>
                    <td><strong>{{ $user->username ?? '—' }}</strong></td>
                    <td>
                        @if ($p && ($p->last_name || $p->first_name))
                            {{ trim($p->last_name . ' ' . $p->first_name) }}
                            @if ($p->furigana())
                            <br><span class="text-muted">{{ $p->furigana() }}</span>
                            @endif
                        @else
                            <span class="text-muted">{{ $user->name }}</span>
                        @endif
                    </td>
                    <td class="col-md-hide">
                        <span class="badge {{ $user->role }}-role">
                            {{ $user->isAdmin() ? '管理者' : 'ゲスト' }}
                        </span>
                    </td>
                    <td class="col-md-hide">
                        @if (!$user->isAdmin())
                        <span class="badge {{ $status }}">
                            {{ ['attending'=>'出席','declining'=>'欠席','pending'=>'未回答'][$status] ?? '—' }}
                        </span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="{{ route('admin.users.edit', $user->id) }}"
                           class="btn-sm btn-sm-pw" style="text-decoration:none;">
                            <i class="fa-solid fa-pen-to-square"></i> 編集
                        </a>
                        &nbsp;
                        <button class="btn-sm btn-sm-pw"
                            onclick="togglePw({{ $user->id }})">
                            <i class="fa-solid fa-key"></i> PW
                        </button>
                        &nbsp;
                        @if ($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                              style="display:inline"
                              onsubmit="return confirm('「{{ $user->username }}」を削除しますか？')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-sm btn-sm-del">削除</button>
                        </form>
                        @else
                        <span class="text-muted" style="font-size:0.75rem;">（自分）</span>
                        @endif
                    </td>
                </tr>
                {{-- パスワード変更行 --}}
                <tr class="pw-row" id="pw-row-{{ $user->id }}">
                    <td colspan="5">
                        <form method="POST" action="{{ route('admin.users.password', $user->id) }}"
                              class="pw-form">
                            @csrf @method('PATCH')
                            <input type="password" name="password"
                                   placeholder="新しいパスワード" required minlength="6">
                            <input type="password" name="password_confirmation"
                                   placeholder="確認用" required minlength="6">
                            <button type="submit" class="btn-sm btn-sm-pw">
                                <i class="fa-solid fa-check"></i> 変更する
                            </button>
                            <button type="button" class="btn-sm btn-sm-del"
                                    onclick="togglePw({{ $user->id }})">キャンセル</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>

</div>

<script>
function togglePw(id) {
    const row = document.getElementById('pw-row-' + id);
    row.classList.toggle('open');
    if (row.classList.contains('open')) {
        row.querySelector('input[name="password"]').focus();
    }
}

// ロール切替でゲスト専用フィールドを表示/非表示
document.querySelectorAll('input[name="role"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const guestFields = document.getElementById('guestFields');
        guestFields.classList.toggle('hidden', radio.value !== 'guest');
    });
});
// 初期状態
(function() {
    const checked = document.querySelector('input[name="role"]:checked');
    if (checked && checked.value !== 'guest') {
        document.getElementById('guestFields').classList.add('hidden');
    }
})();
</script>
@endsection
