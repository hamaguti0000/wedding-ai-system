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
.bulk-toolbar {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 14px 16px; margin: 14px 16px 0; border: 1px solid #f0e4d4;
    border-radius: 10px; background: #fffdf9;
}
.bulk-toolbar__left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; color: #7b6a5c; font-size: .86rem; }
.bulk-toolbar__count { font-weight: 800; color: #4f4036; }
.select-col { width: 42px; text-align: center; }
.user-select { width: 17px; height: 17px; accent-color: #b38b59; }
.btn-bulk-del {
    display: inline-flex; align-items: center; gap: 7px; border: 0; border-radius: 7px;
    padding: 8px 11px; background: #be123c; color: #fff; font-weight: 800; font-size: .82rem;
    cursor: pointer;
}
.btn-bulk-del:disabled { opacity: .45; cursor: not-allowed; }

/* ── 検索・フィルター・ソート ── */
.user-toolbar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 14px 16px; margin-bottom: 14px;
    background: #fff; border-radius: 10px; border: 1px solid #f0ebe3;
}
.user-search-wrap { position: relative; flex: 1; min-width: 180px; max-width: 280px; }
.user-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #c0b0a0; font-size: 0.85rem; pointer-events: none; }
.user-search-input { width: 100%; padding: 8px 28px 8px 30px; border: 1px solid #e0d0bc; border-radius: 6px; font-size: 0.85rem; background: #fffdf9; box-sizing: border-box; }
.user-search-input:focus { border-color: #b38b59; outline: none; }
.user-search-clear { display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #c0b0a0; font-size: 1rem; line-height: 1; }
.user-search-clear.visible { display: block; }
.user-filter-btn { padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; border: 1px solid #e8d5b7; color: #b38b59; background: #fef9f0; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.user-filter-btn.active, .user-filter-btn:hover { background: #b38b59; color: #fff; border-color: #b38b59; }
th.user-sortable { cursor: pointer; user-select: none; }
th.user-sortable:hover { background: #f5ede0; }
.user-sort-icon { display: inline-block; margin-left: 4px; font-size: 0.74rem; color: #c0b0a0; }
th.user-sort-asc .user-sort-icon, th.user-sort-desc .user-sort-icon { color: #b38b59; }
.user-result-count { font-size: 0.82rem; color: #999; padding: 0 16px 8px; }
.user-no-results { display: none; text-align: center; padding: 40px 20px; color: #aaa; }
.user-no-results.visible { display: block; }

/* お立場・ご関係のインライン編集 */
.guest-info-select {
    padding: 5px 6px; border: 1px solid #e0d0bc; border-radius: 5px;
    font-size: 0.8rem; background: #fffdf9; color: #4f4036; max-width: 108px;
}
.guest-info-select:focus { border-color: #b38b59; outline: none; }
.guest-info-select.side-groom { border-color: #7a9cc6; background: #eef3fa; }
.guest-info-select.side-bride { border-color: #d98ca6; background: #fbeef2; }
.guest-info-select.is-saving { opacity: 0.5; }
.guest-info-saved-flash { animation: guest-info-flash 0.9s ease; }
@keyframes guest-info-flash {
    0%   { background: #d9f2df; }
    100% { background: transparent; }
}

@media (max-width: 767px) {
    .card { padding: 16px; }
    .fg-2, .fg-3, .fg-4 { grid-template-columns: 1fr; }
    .user-table-wrap .card-title { padding: 14px 14px 0; }
    .users-wrap table { min-width: 360px; }
    .pw-form input { width: 120px; }
    .bulk-toolbar { align-items: flex-start; flex-direction: column; margin: 12px 12px 0; }
}

/* サイドバー分で実際の表示幅が縮むため、ビューポート幅ではなく
   .user-table-wrap の実際の幅(コンテナクエリ)で切り替える */
@container (max-width: 950px) {
    .col-md-hide { display: none; }
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
                        <label>参加日</label>
                        <select name="event_day">
                            <option value="day1" {{ old('event_day') === 'day1' ? 'selected' : '' }}>1日目</option>
                            <option value="day2" {{ old('event_day', 'day2') === 'day2' ? 'selected' : '' }}>2日目</option>
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
        <p class="card-title">登録済みユーザー</p>

        @if ($users->isEmpty())
        <div class="empty-state">まだユーザーが登録されていません</div>
        @else
        <form method="POST" action="{{ route('admin.users.bulk-destroy') }}" id="bulkDeleteForm"
              onsubmit="return confirmBulkDelete()" style="display:none;">
            @csrf @method('DELETE')
        </form>
        <form method="POST" action="{{ route('admin.users.bulk-event-day') }}" id="bulkEventDayForm"
              onsubmit="return confirmBulkEventDay()" style="display:none;">
            @csrf @method('PATCH')
            <input type="hidden" name="event_day" id="bulkEventDayInput" value="day1">
        </form>
        {{-- ツールバー --}}
        <div class="user-toolbar">
            <div class="user-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="userSearch" class="user-search-input"
                       placeholder="ユーザー名・氏名・メールで検索" autocomplete="off">
                <button type="button" id="userSearchClear" class="user-search-clear" aria-label="クリア">✕</button>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                <button class="user-filter-btn active" data-role="all">全員</button>
                <button class="user-filter-btn" data-role="guest">ゲスト</button>
                <button class="user-filter-btn" data-role="admin">管理者</button>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                <button class="user-filter-btn active" data-rsvp="all">出欠: 全</button>
                <button class="user-filter-btn" data-rsvp="attending">出席</button>
                <button class="user-filter-btn" data-rsvp="declining">欠席</button>
                <button class="user-filter-btn" data-rsvp="pending">未回答</button>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                <button class="user-filter-btn active" data-side="all">お立場: 全</button>
                <button class="user-filter-btn" data-side="groom">新郎側</button>
                <button class="user-filter-btn" data-side="bride">新婦側</button>
                <button class="user-filter-btn" data-side="">未設定</button>
            </div>
        </div>
        <div class="user-result-count" id="userResultCount"></div>
        <div class="bulk-toolbar">
            <div class="bulk-toolbar__left">
                <span class="bulk-toolbar__count"><span id="selectedUserCount">0</span>名選択中</span>
                <span>参加日をまとめて変更したいゲストにチェックを入れてください。削除にも同じ選択を使えます。</span>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="btn-sm btn-sm-pw" id="bulkDay1Button" onclick="submitBulkEventDay('day1')" disabled>
                    <i class="fa-solid fa-calendar-day"></i> 選択を1日目にする
                </button>
                <button type="button" class="btn-sm btn-sm-pw" id="bulkDay2Button" onclick="submitBulkEventDay('day2')" disabled>
                    <i class="fa-solid fa-calendar-check"></i> 選択を2日目に戻す
                </button>
                <button type="submit" class="btn-bulk-del" id="bulkDeleteButton" form="bulkDeleteForm" disabled>
                    <i class="fa-solid fa-trash-can"></i> 選択したユーザーを削除
                </button>
            </div>
        </div>
        <div class="table-scroll">
        <table id="userTable">
            <thead>
                <tr>
                    <th class="select-col">
                        <input type="checkbox" id="selectAllUsers" class="user-select" aria-label="全員を選択">
                    </th>
                    <th class="user-sortable" data-col="username">ユーザー名 <span class="user-sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th class="user-sortable" data-col="name">氏名 <span class="user-sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th class="user-sortable" data-col="side">お立場 <span class="user-sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th class="col-md-hide">ご関係</th>
                    <th class="col-md-hide user-sortable" data-col="day">参加日 <span class="user-sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th class="col-md-hide">メール</th>
                    <th class="col-md-hide user-sortable" data-col="role">ロール <span class="user-sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th class="col-md-hide user-sortable" data-col="rsvp">出欠 <span class="user-sort-icon"><i class="fa-solid fa-sort"></i></span></th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="userTbody">
                @foreach ($users as $user)
                @php
                    $p = $user->guestProfile;
                    $status = $p?->participation ?? 'pending';
                    $fullName = $p ? trim(($p->last_name ?? '') . ' ' . ($p->first_name ?? '')) : '';
                    $furigana = $p?->furigana() ?? '';
                @endphp
                <tr data-id="{{ $user->id }}"
                    data-username="{{ strtolower($user->username) }}"
                    data-name="{{ strtolower($fullName ?: $user->username) }}"
                    data-furigana="{{ $furigana }}"
                    data-email="{{ strtolower($user->email ?? '') }}"
                    data-role="{{ $user->role }}"
                    data-rsvp="{{ $status }}"
                    data-side="{{ $p?->guest_side }}"
                    data-day="{{ $p?->event_day }}">
                    <td class="select-col">
                        @if ($user->id !== auth()->id())
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                   class="user-select user-row-select"
                                   form="bulkDeleteForm"
                                   aria-label="{{ $user->username }}を選択">
                        @else
                            <input type="checkbox" class="user-select" disabled aria-label="自分自身は選択不可">
                        @endif
                    </td>
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
                    <td>
                        @if (!$user->isAdmin())
                        <select class="guest-info-select side-{{ $p?->guest_side }}"
                                data-user-id="{{ $user->id }}" data-field="guest_side" aria-label="お立場">
                            <option value="" {{ !$p?->guest_side ? 'selected' : '' }}>未設定</option>
                            <option value="groom" {{ $p?->guest_side === 'groom' ? 'selected' : '' }}>新郎側</option>
                            <option value="bride" {{ $p?->guest_side === 'bride' ? 'selected' : '' }}>新婦側</option>
                        </select>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="col-md-hide">
                        @if (!$user->isAdmin())
                        <select class="guest-info-select" data-user-id="{{ $user->id }}" data-field="relationship" aria-label="ご関係">
                            <option value="" {{ !$p?->relationship ? 'selected' : '' }}>未設定</option>
                            <option value="friend"    {{ $p?->relationship === 'friend'    ? 'selected' : '' }}>友人・知人</option>
                            <option value="family"    {{ $p?->relationship === 'family'    ? 'selected' : '' }}>親族</option>
                            <option value="colleague" {{ $p?->relationship === 'colleague' ? 'selected' : '' }}>職場関係</option>
                            <option value="other"     {{ $p?->relationship === 'other'     ? 'selected' : '' }}>その他</option>
                        </select>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="col-md-hide">
                        @if (!$user->isAdmin())
                        <select class="guest-info-select" data-user-id="{{ $user->id }}" data-field="event_day" aria-label="参加日">
                            <option value="day1" {{ $p?->event_day === 'day1' ? 'selected' : '' }}>1日目</option>
                            <option value="day2" {{ ($p?->event_day ?: 'day2') === 'day2' ? 'selected' : '' }}>2日目</option>
                        </select>
                        @else
                        <span class="text-muted">—</span>
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
                        <button type="button" class="btn-sm btn-sm-pw"
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
                    <td colspan="10">
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
        <div class="user-no-results" id="userNoResults">
            <div style="font-size:2rem;margin-bottom:8px;">🔍</div>
            <p style="font-weight:600;color:#888;">該当するユーザーが見つかりません</p>
        </div>
        @endif
    </div>

</div>

<script>
// ── ユーザー検索・フィルター・ソート ───────────────────────
(function () {
    const state  = { q: '', role: 'all', rsvp: 'all', side: 'all', col: null, dir: 'asc' };
    const tbody  = document.getElementById('userTbody');
    const srch   = document.getElementById('userSearch');
    const clrBtn = document.getElementById('userSearchClear');
    const countEl= document.getElementById('userResultCount');
    const noRes  = document.getElementById('userNoResults');
    if (!tbody) return;

    const getRows = () => Array.from(tbody.querySelectorAll('tr[data-id]'));

    function matches(row) {
        const d = row.dataset;
        if (state.q) {
            const q = state.q;
            if (!d.username.includes(q) && !d.name.includes(q) && !d.furigana.includes(q) && !d.email.includes(q)) return false;
        }
        if (state.role !== 'all' && d.role !== state.role) return false;
        if (state.rsvp !== 'all') {
            // 管理者は出欠フィルター対象外
            if (d.role === 'admin') return false;
            if (d.rsvp !== state.rsvp) return false;
        }
        if (state.side !== 'all') {
            if (d.role === 'admin') return false;
            if ((d.side || '') !== state.side) return false;
        }
        return true;
    }

    const rsvpOrder = { attending: 0, declining: 1, pending: 2 };
    function compare(a, b) {
        const da = a.dataset, db = b.dataset;
        let va, vb;
        switch (state.col) {
            case 'username': va = da.username; vb = db.username; break;
            case 'name':     va = da.name;     vb = db.name;     break;
            case 'role':     va = da.role;     vb = db.role;     break;
            case 'rsvp':     va = rsvpOrder[da.rsvp] ?? 3; vb = rsvpOrder[db.rsvp] ?? 3; break;
            case 'side':     va = da.side || 'zzz'; vb = db.side || 'zzz'; break;
            case 'day':      va = da.day || 'zzz'; vb = db.day || 'zzz'; break;
            default: return 0;
        }
        if (va < vb) return state.dir === 'asc' ? -1 :  1;
        if (va > vb) return state.dir === 'asc' ?  1 : -1;
        return 0;
    }

    function updateIcons() {
        document.querySelectorAll('#userTable th.user-sortable').forEach(th => {
            th.classList.remove('user-sort-asc', 'user-sort-desc');
            const icon = th.querySelector('.user-sort-icon i');
            if (icon) icon.className = 'fa-solid fa-sort';
            if (th.dataset.col === state.col) {
                th.classList.add('user-sort-' + state.dir);
                if (icon) icon.className = state.dir === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
            }
        });
    }

    function applyAll() {
        const rows = getRows();
        let visible = 0;
        rows.forEach(row => {
            const show = matches(row);
            row.style.display = show ? '' : 'none';
            const pwRow = document.getElementById('pw-row-' + row.dataset.id);
            if (pwRow && !show) { pwRow.classList.remove('open'); pwRow.style.display = 'none'; }
            if (show) visible++;
        });
        if (state.col) {
            rows.filter(r => r.style.display !== 'none').sort(compare).forEach(r => {
                tbody.appendChild(r);
                const pwRow = document.getElementById('pw-row-' + r.dataset.id);
                if (pwRow) tbody.appendChild(pwRow);
            });
        }
        if (countEl) countEl.textContent = `${visible}名 表示中`;
        if (noRes)   noRes.classList.toggle('visible', visible === 0);
        updateBulkDeleteState();
    }

    // ソート
    document.querySelectorAll('#userTable th.user-sortable').forEach(th => {
        th.addEventListener('click', () => {
            const col = th.dataset.col;
            state.dir = state.col === col ? (state.dir === 'asc' ? 'desc' : 'asc') : 'asc';
            state.col = col;
            updateIcons(); applyAll();
        });
    });

    // ロールフィルター
    document.querySelectorAll('.user-filter-btn[data-role]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.user-filter-btn[data-role]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.role = btn.dataset.role;
            applyAll();
        });
    });

    // 出欠フィルター
    document.querySelectorAll('.user-filter-btn[data-rsvp]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.user-filter-btn[data-rsvp]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.rsvp = btn.dataset.rsvp;
            applyAll();
        });
    });

    // お立場フィルター
    document.querySelectorAll('.user-filter-btn[data-side]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.user-filter-btn[data-side]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.side = btn.dataset.side;
            applyAll();
        });
    });

    // 検索
    srch?.addEventListener('input', () => {
        state.q = srch.value.toLowerCase().trim();
        clrBtn?.classList.toggle('visible', state.q.length > 0);
        applyAll();
    });
    clrBtn?.addEventListener('click', () => {
        srch.value = ''; state.q = '';
        clrBtn.classList.remove('visible');
        srch.focus(); applyAll();
    });

    applyAll();
})();

// ── お立場・ご関係のインライン編集 ──────────────────────────
document.querySelectorAll('.guest-info-select').forEach(select => {
    select.addEventListener('change', async () => {
        const userId = select.dataset.userId;
        const field  = select.dataset.field;
        const value  = select.value;
        const row    = select.closest('tr');
        const csrf   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        select.classList.add('is-saving');
        select.disabled = true;

        try {
            const res = await fetch(`/admin/users/${userId}/guest-info`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ [field]: value }),
            });

            if (!res.ok) throw new Error('保存に失敗しました');

            const data = await res.json();

            if (field === 'guest_side') {
                select.className = 'guest-info-select' + (data.guest_side ? ' side-' + data.guest_side : '');
                if (row) row.dataset.side = data.guest_side || '';
            }
            if (field === 'event_day' && row) {
                row.dataset.day = data.event_day || '';
            }

            row?.classList.remove('guest-info-saved-flash');
            void row?.offsetWidth; // reflow でアニメーションを再トリガー
            row?.classList.add('guest-info-saved-flash');
        } catch (e) {
            alert('保存に失敗しました。もう一度お試しください。');
        } finally {
            select.classList.remove('is-saving');
            select.disabled = false;
        }
    });
});

function togglePw(id) {
    const row = document.getElementById('pw-row-' + id);
    row.classList.toggle('open');
    if (row.classList.contains('open')) {
        row.querySelector('input[name="password"]').focus();
    }
}

function updateBulkDeleteState() {
    const selected = document.querySelectorAll('.user-row-select:checked').length;
    const count = document.getElementById('selectedUserCount');
    const button = document.getElementById('bulkDeleteButton');
    const day1Button = document.getElementById('bulkDay1Button');
    const day2Button = document.getElementById('bulkDay2Button');
    const selectAll = document.getElementById('selectAllUsers');
    const selectable = document.querySelectorAll('.user-row-select');

    if (count) count.textContent = selected;
    if (button) button.disabled = selected === 0;
    if (day1Button) day1Button.disabled = selected === 0;
    if (day2Button) day2Button.disabled = selected === 0;
    if (selectAll) {
        selectAll.checked = selectable.length > 0 && selected === selectable.length;
        selectAll.indeterminate = selected > 0 && selected < selectable.length;
    }
}

function confirmBulkDelete() {
    const selected = document.querySelectorAll('.user-row-select:checked').length;
    if (selected === 0) return false;
    return confirm(selected + '名のユーザーを削除しますか？');
}

document.getElementById('selectAllUsers')?.addEventListener('change', e => {
    document.querySelectorAll('.user-row-select').forEach(input => {
        input.checked = e.target.checked;
    });
    updateBulkDeleteState();
});

document.querySelectorAll('.user-row-select').forEach(input => {
    input.addEventListener('change', updateBulkDeleteState);
});

updateBulkDeleteState();

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
