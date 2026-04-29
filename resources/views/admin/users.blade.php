@extends('layouts.app')
@section('title', 'ユーザー管理 | Admin')

@push('styles')
<style>
.users-wrap {
    max-width: 900px;
    margin: 40px auto 80px;
    padding: 0 20px;
    font-family: 'Noto Sans JP', sans-serif;
}
.users-wrap h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: #b38b59;
    margin-bottom: 8px;
}
.admin-nav {
    display: flex;
    gap: 12px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}
.admin-nav a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    background: #fef9f0;
    color: #b38b59;
    border: 1px solid #e8d5b7;
    transition: background 0.2s;
}
.admin-nav a.active,
.admin-nav a:hover { background: #b38b59; color: #fff; }

/* 登録フォームカード */
.card {
    background: #fff;
    border-radius: 14px;
    padding: 28px 32px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    margin-bottom: 32px;
}
.card h2 {
    font-size: 0.82rem;
    font-weight: 700;
    color: #b38b59;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 20px;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.form-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
.form-full { grid-column: 1 / -1; }
.form-group label {
    display: block;
    font-size: 0.82rem;
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
}
.form-group input:focus, .form-group select:focus {
    border-color: #b38b59;
    outline: none;
    box-shadow: 0 0 0 3px rgba(179,139,89,0.12);
}
.req { color: #c0392b; margin-left: 3px; }
.field-error { color: #c0392b; font-size: 0.8rem; margin-top: 4px; display: block; }
.btn-primary {
    padding: 11px 32px;
    background: #b38b59;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.92rem;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 8px;
}
.btn-primary:hover { background: #9a7447; }

/* ユーザー一覧 */
.user-table-wrap {
    background: #fff;
    border-radius: 14px;
    padding: 24px 32px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
}
.user-table-wrap h2 {
    font-size: 0.82rem;
    font-weight: 700;
    color: #b38b59;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 18px;
}
table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
th {
    background: #fdf6ee;
    color: #b38b59;
    font-weight: 700;
    padding: 10px 14px;
    text-align: left;
    border-bottom: 2px solid #eedfc4;
}
td {
    padding: 10px 14px;
    border-bottom: 1px solid #f0f0f0;
    color: #333;
    vertical-align: middle;
}
tr:last-child td { border-bottom: none; }
.badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
}
.badge.attending { background: #eafaf1; color: #27ae60; }
.badge.declining  { background: #fdf2f2; color: #e74c3c; }
.badge.pending    { background: #fef9ee; color: #f39c12; }
.text-muted { color: #aaa; font-size: 0.82rem; }
.btn-delete {
    padding: 5px 14px;
    background: transparent;
    border: 1px solid #e8b4b4;
    color: #c0392b;
    border-radius: 5px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-delete:hover { background: #fdf2f2; }
.alert-success {
    background: #eafaf1; border: 1px solid #a9dfbf; color: #1e8449;
    padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;
}
.empty-state { text-align: center; padding: 40px; color: #aaa; }

@media (max-width: 600px) {
    .card { padding: 20px 18px; }
    .form-grid, .form-grid.cols-3 { grid-template-columns: 1fr; }
    .user-table-wrap { padding: 16px; }
    th, td { padding: 8px 10px; font-size: 0.82rem; }
    .col-hide { display: none; }
}
</style>
@endpush

@section('content')
<div class="users-wrap">

    <h1>ユーザー管理</h1>

    <nav class="admin-nav">
        <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-list-check"></i> ゲスト一覧</a>
        <a href="{{ route('admin.users') }}" class="active"><i class="fa-solid fa-user-plus"></i> ユーザー管理</a>
        <a href="{{ route('admin.seating') }}"><i class="fa-solid fa-chair"></i> 席次表</a>
        <a href="{{ route('admin.settings') }}"><i class="fa-solid fa-gear"></i> 式の情報</a>
    </nav>

    @if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── 新規登録フォーム ── --}}
    <div class="card">
        <h2>新規ゲスト登録</h2>

        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            {{-- ログイン情報 --}}
            <div class="form-grid" style="margin-bottom:14px;">
                <div class="form-group">
                    <label>ユーザー名 <span class="req">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        placeholder="yamada_taro" autocomplete="off">
                    @error('username')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>パスワード <span class="req">*</span></label>
                    <input type="text" name="password" value="{{ old('password') }}"
                        placeholder="初期パスワード（6文字以上）" autocomplete="off">
                    @error('password')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- 氏名 --}}
            <div class="form-grid cols-3" style="margin-bottom:14px;">
                <div class="form-group">
                    <label>姓</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="山田">
                </div>
                <div class="form-group">
                    <label>名</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="太郎">
                </div>
                <div class="form-group">
                    {{-- spacer --}}
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

            {{-- 立場・関係 --}}
            <div class="form-grid" style="margin-bottom:20px;">
                <div class="form-group">
                    <label>お立場</label>
                    <select name="guest_side">
                        <option value="">-- 未設定 --</option>
                        <option value="groom" {{ old('guest_side') === 'groom' ? 'selected' : '' }}>新郎側</option>
                        <option value="bride" {{ old('guest_side') === 'bride' ? 'selected' : '' }}>新婦側</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>ご関係</label>
                    <select name="relationship">
                        <option value="">-- 未設定 --</option>
                        <option value="friend"    {{ old('relationship') === 'friend'    ? 'selected' : '' }}>友人・知人</option>
                        <option value="family"    {{ old('relationship') === 'family'    ? 'selected' : '' }}>親族</option>
                        <option value="colleague" {{ old('relationship') === 'colleague' ? 'selected' : '' }}>職場関係</option>
                        <option value="other"     {{ old('relationship') === 'other'     ? 'selected' : '' }}>その他</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-user-plus"></i> 登録する
            </button>
        </form>
    </div>

    {{-- ── ユーザー一覧 ── --}}
    <div class="user-table-wrap">
        <h2>登録済みゲスト（{{ $users->count() }}名）</h2>

        @if ($users->isEmpty())
        <div class="empty-state">まだゲストが登録されていません</div>
        @else
        <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>ユーザー名</th>
                    <th>氏名</th>
                    <th class="col-hide">お立場</th>
                    <th>出欠</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                @php $p = $user->guestProfile; @endphp
                <tr>
                    <td><strong>{{ $user->username ?? '—' }}</strong></td>
                    <td>
                        @if ($p && ($p->last_name || $p->first_name))
                            {{ $p->last_name }} {{ $p->first_name }}
                            @if ($p->furigana())
                            <br><span class="text-muted">{{ $p->furigana() }}</span>
                            @endif
                        @else
                            <span class="text-muted">未設定</span>
                        @endif
                    </td>
                    <td class="col-hide">
                        @if ($p?->guest_side)
                            {{ $p->guestSideLabel() }}
                            @if ($p->relationship)
                            <br><span class="text-muted">{{ $p->relationshipLabel() }}</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @php $status = $p?->participation ?? 'pending'; @endphp
                        <span class="badge {{ $status }}">
                            {{ ['attending'=>'出席','declining'=>'欠席','pending'=>'未回答'][$status] ?? '未回答' }}
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                              onsubmit="return confirm('「{{ $user->username }}」を削除しますか？')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-delete">削除</button>
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
@endsection
