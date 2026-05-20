@extends('layouts.app')
@section('title', 'ユーザー管理 | Admin')

@push('styles')
<style>
/* ユーザー管理固有スタイル */
.card { background: #fff; border-radius: 14px; padding: 24px 28px; box-shadow: 0 4px 14px rgba(0,0,0,0.07); margin-bottom: 28px; }
.card-title { font-size: 0.78rem; font-weight: 700; color: #b38b59; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 18px; }
.user-table-wrap { overflow: hidden; }
.user-table-wrap .card-title { padding: 22px 28px 0; }
.users-wrap table { min-width: 500px; }

.fg { display: grid; gap: 12px; margin-bottom: 12px; }
.fg-2 { grid-template-columns: 1fr 1fr; }
.fg-3 { grid-template-columns: 1fr 1fr 1fr; }
.fg-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }

.role-toggle { display: flex; border: 1px solid #e0d0bc; border-radius: 6px; overflow: hidden; }
.role-toggle input { display: none; }
.role-toggle label { flex: 1; text-align: center; padding: 10px 6px; font-size: 0.85rem; font-weight: 500; color: #9b8573; background: #fffdf9; cursor: pointer; transition: background 0.15s, color 0.15s; margin: 0; line-height: 1.2; }
.role-toggle label:not(:last-child) { border-right: 1px solid #e0d0bc; }
.role-toggle input:checked + label { background: #b38b59; color: #fff; }

.guest-fields { transition: opacity 0.2s; }
.guest-fields.hidden { opacity: 0.35; pointer-events: none; }

.pw-row { display: none; background: #fdf6ee; }
.pw-row.open { display: table-row; }
.pw-form { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 6px 0; }
.pw-form input { padding: 7px 10px; border: 1px solid #e0d0bc; border-radius: 5px; font-size: 0.85rem; width: 160px; min-width: 100px; }
.pw-form input:focus { border-color: #b38b59; outline: none; }
.csv-help { margin: 8px 0 0; color: #7b6a5c; font-size: 0.82rem; line-height: 1.7; }
.csv-help code { background: #f7efe5; border-radius: 4px; padding: 2px 5px; font-size: 0.78rem; }
.field-error { white-space: pre-line; }

@media (max-width: 767px) {
    .card { padding: 16px; }
    .fg-2, .fg-3, .fg-4 { grid-template-columns: 1fr; }
    .user-table-wrap .card-title { padding: 14px 14px 0; }
    .col-md-hide { display: none; }
    .users-wrap table { min-width: 360px; }
    .pw-form input { width: 120px; }
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

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    {{-- ── CSV一括登録 ── --}}
    <div class="card">
        <p class="card-title">ゲストCSV一括登録</p>

        <form method="POST" action="{{ route('admin.users.import.preview') }}" enctype="multipart/form-data">
            @csrf

            <div class="fg fg-2" style="margin-bottom:16px;">
                <div class="form-group">
                    <label>CSVファイル <span class="req">*</span></label>
                    <input type="file" name="guest_csv" accept=".csv,text/csv,text/plain">
                    @error('guest_csv')<span class="field-error">{{ $message }}</span>@enderror
                    <p class="csv-help">
                        必須列: <code>ユーザー名</code>。任意列: <code>姓</code> <code>名</code> <code>関係</code> <code>肩書き1</code> <code>肩書き2</code> <code>お言葉</code><br>
                        ユーザー名は半角英数字、<code>.</code> <code>-</code> <code>_</code> のみ使えます。タブ区切りCSVも読み込めます。
                    </p>
                </div>
                <div class="form-group">
                    <label>登録前確認</label>
                    <p class="csv-help" style="margin-top:0;">
                        読み込み後に一覧画面で内容を確認・編集できます。ユーザー名の重複や必須不足も登録前に一覧表示します。
                    </p>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-file-import"></i> CSVを読み込む
            </button>
        </form>
    </div>

    {{-- ── 新規登録フォーム ── --}}
    <div class="card">
        <p class="card-title">新規ユーザー登録</p>

        <form method="POST" action="{{ route('admin.users.store') }}" id="createForm" enctype="multipart/form-data">
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
            <div class="fg fg-3" style="margin-bottom:16px;">
                <div class="form-group">
                    <label>ユーザー名 <span class="req">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        placeholder="yamada_taro" autocomplete="off">
                    @error('username')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        placeholder="guest@example.com" autocomplete="email">
                    @error('email')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>パスワード <span class="req">*</span></label>
                    <input type="text" name="password" value="{{ old('password') }}"
                        placeholder="6文字以上" autocomplete="off">
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            <div style="margin:0 0 16px;padding:16px 18px;background:#fffdf9;border:1px solid #f0ebe3;border-radius:10px;">
                <p class="card-title" style="margin-bottom:12px;">アイコン</p>
                @include('partials.avatar-fields', [
                    'avatarType' => old('avatar_type', 'initial'),
                    'avatarEmoji' => old('avatar_emoji', ''),
                    'avatarBgColor' => old('avatar_bg_color', '#ffffff'),
                    'avatarBorderColor' => old('avatar_border_color', '#f0e4d0'),
                    'avatarBorderWidth' => old('avatar_border_width', 3),
                    'avatarImageUrl' => null,
                    'avatarInitial' => '?',
                    'avatarTitle' => 'ユーザーアイコン',
                    'avatarDesc' => '写真、絵文字、イニシャルから選べます。'
                ])
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
                    <th class="col-md-hide">メール</th>
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
                        @if ($user->email)
                            {{ $user->email }}
                            @if (!$user->isAdmin())
                                <br><span class="text-muted">{{ $user->hasVerifiedEmail() ? '認証済み' : '未認証' }}</span>
                            @endif
                        @else
                            <span class="text-muted">未登録</span>
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
                        <a href="{{ route('admin.users.qr', $user->id) }}"
                           class="btn-sm btn-sm-pw" style="text-decoration:none;">
                            <i class="fa-solid fa-qrcode"></i> QR
                        </a>
                        &nbsp;
                        <a href="{{ route('admin.users.show', $user->id) }}"
                           class="btn-sm btn-sm-pw" style="text-decoration:none;">
                            <i class="fa-solid fa-id-card"></i> 詳細
                        </a>
                        &nbsp;
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
                    <td colspan="6">
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
