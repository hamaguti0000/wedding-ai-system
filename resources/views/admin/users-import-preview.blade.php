@extends('layouts.app')
@section('title', 'CSV登録確認 | Admin')

@push('styles')
<style>
.import-page { max-width: 1160px; margin: 56px auto 80px; padding: 0 24px; display: grid; gap: 18px; }
.import-page, .import-page * { box-sizing: border-box; }
.import-page form,
.import-hero,
.import-summary,
.import-panel,
.guest-list,
.guest-card,
.guest-fields,
.field-row,
.field-group,
.confirm-bar { min-width: 0; }
.import-hero {
    display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;
    background: #fff; border: 1px solid #eadfce; border-radius: 12px; padding: 22px 24px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.import-hero h1 { margin: 0; }
.import-hero p { margin: 7px 0 0; color: #76685d; font-size: .88rem; line-height: 1.6; }
.import-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.import-link {
    display: inline-flex; align-items: center; gap: 8px; padding: 9px 12px;
    border-radius: 8px; border: 1px solid #dccbb8; background: #fffdf9;
    color: #493b32; font-size: .84rem; font-weight: 800; text-decoration: none;
}
.import-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.summary-card {
    background: #fff; border: 1px solid #eadfce; border-radius: 10px; padding: 14px 16px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.04);
}
.summary-card__label { color: #967753; font-size: .72rem; letter-spacing: 1.2px; text-transform: uppercase; font-weight: 800; }
.summary-card__value { margin-top: 7px; color: #332820; font-size: 1.8rem; line-height: 1; font-weight: 800; }
.summary-card__sub { margin-top: 6px; color: #7e6f62; font-size: .8rem; }
.summary-card.is-error { border-color: #fecdd3; background: #fff8f9; }
.summary-card.is-error .summary-card__label, .summary-card.is-error .summary-card__value { color: #be123c; }
.import-panel {
    background: #fff; border: 1px solid #eadfce; border-radius: 12px; padding: 18px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.05);
}
.guest-list { display: grid; gap: 12px; }
.guest-card {
    display: grid; grid-template-columns: 150px minmax(0, 1fr); gap: 16px;
    border: 1px solid #eadfce; border-radius: 10px; background: #fcf9f5; padding: 14px;
}
.guest-card.has-error { border-color: #fecdd3; background: #fff8f9; }
.guest-status { display: grid; align-content: start; gap: 8px; }
.line-number { color: #8a7a6d; font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .8px; }
.status-badge {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    width: fit-content; padding: 7px 10px; border-radius: 999px; font-size: .78rem; font-weight: 900;
}
.status-badge.is-ok { background: #e8f5ec; color: #23723d; }
.status-badge.is-error { background: #ffe4e6; color: #be123c; }
.row-errors { margin: 0; padding-left: 18px; color: #be123c; font-size: .8rem; line-height: 1.55; }
.guest-fields { display: grid; gap: 12px; }
.field-row { display: grid; gap: 10px; }
.field-row.cols-3 { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.2fr); }
.field-row.cols-2 { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
.field-group label {
    display: block; margin-bottom: 5px; color: #7a6b5d; font-size: .76rem;
    font-weight: 800; letter-spacing: .4px;
}
.field-group input, .field-group textarea {
    display: block; width: 100%; max-width: 100%; min-width: 0;
    border: 1px solid #d9c8b5; border-radius: 7px; padding: 9px 10px;
    background: #fff; color: #302821; font-size: .9rem;
}
.field-group textarea { min-height: 42px; resize: vertical; }
.field-group input:focus, .field-group textarea:focus {
    outline: none; border-color: #b38b59; box-shadow: 0 0 0 2px rgba(179,139,89,.13);
}
.derived {
    display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
    padding-top: 2px;
}
.derived-pill {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 9px;
    border-radius: 999px; background: #f2ece4; color: #68594c; font-size: .78rem; font-weight: 800;
}
.confirm-bar {
    position: sticky; bottom: 12px; z-index: 5;
    display: grid; grid-template-columns: minmax(220px, 360px) 1fr; gap: 14px; align-items: end;
    background: rgba(255,255,255,.96); border: 1px solid #eadfce; border-radius: 12px;
    padding: 14px 16px; box-shadow: 0 10px 28px rgba(0,0,0,.12);
}
.confirm-bar input { display: block; width: 100%; max-width: 100%; min-width: 0; border: 1px solid #d9c8b5; border-radius: 7px; padding: 10px 11px; }
.confirm-help { color: #76685d; font-size: .8rem; line-height: 1.6; margin: 5px 0 0; }
.field-error { display: block; color: #be123c; white-space: pre-line; margin-top: 5px; font-size: .82rem; }
@media (max-width: 860px) {
    .import-page { margin: 32px auto 56px; padding: 0 14px; }
    .import-hero { display: block; padding: 16px; }
    .import-actions { margin-top: 12px; }
    .import-summary { grid-template-columns: 1fr; }
    .guest-card { grid-template-columns: 1fr; }
    .guest-status { display: flex; align-items: flex-start; flex-wrap: wrap; }
    .field-row.cols-3, .field-row.cols-2, .confirm-bar { grid-template-columns: 1fr; }
    .confirm-bar { position: static; }
}
</style>
@endpush

@section('content')
@php
    $totalCount = count($rows);
    $errorCount = collect($rows)->filter(fn ($row) => count($row['errors']) > 0)->count();
    $okCount = $totalCount - $errorCount;
    $sideLabels = ['groom' => '新郎側', 'bride' => '新婦側'];
    $relationshipLabels = ['family' => '親族', 'friend' => '友人・知人', 'colleague' => '職場関係', 'other' => 'その他'];
@endphp

<div class="import-page">
    <section class="import-hero">
        <div>
            <h1>CSV登録確認</h1>
            <p>読み込んだゲストを確認し、必要な項目を修正してから登録します。</p>
        </div>
        <div class="import-actions">
            <a href="{{ route('admin.users') }}" class="import-link">
                <i class="fa-solid fa-arrow-left"></i> ユーザー管理に戻る
            </a>
        </div>
    </section>

    @if ($errors->has('rows'))
        <div class="alert alert-error">{{ $errors->first('rows') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.import') }}">
        @csrf

        <section class="import-summary">
            <div class="summary-card">
                <div class="summary-card__label">Loaded</div>
                <div class="summary-card__value">{{ $totalCount }}</div>
                <div class="summary-card__sub">読み込み件数</div>
            </div>
            <div class="summary-card">
                <div class="summary-card__label">Ready</div>
                <div class="summary-card__value">{{ $okCount }}</div>
                <div class="summary-card__sub">登録可能</div>
            </div>
            <div class="summary-card {{ $errorCount > 0 ? 'is-error' : '' }}">
                <div class="summary-card__label">Needs Fix</div>
                <div class="summary-card__value">{{ $errorCount }}</div>
                <div class="summary-card__sub">要修正</div>
            </div>
        </section>

        <section class="import-panel">
            <div class="guest-list">
                @foreach ($rows as $i => $row)
                    @php $hasError = count($row['errors']) > 0; @endphp
                    <article class="guest-card {{ $hasError ? 'has-error' : '' }}">
                        <aside class="guest-status">
                            <div class="line-number">CSV {{ $row['line'] }}行目</div>
                            @if ($hasError)
                                <span class="status-badge is-error"><i class="fa-solid fa-triangle-exclamation"></i> 要修正</span>
                                <ul class="row-errors">
                                    @foreach ($row['errors'] as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="status-badge is-ok"><i class="fa-solid fa-circle-check"></i> 登録可</span>
                            @endif
                        </aside>

                        <div class="guest-fields">
                            <div class="field-row cols-3">
                                <div class="field-group">
                                    <label>姓</label>
                                    <input type="text" name="rows[{{ $i }}][last_name]" value="{{ $row['last_name'] }}">
                                </div>
                                <div class="field-group">
                                    <label>名</label>
                                    <input type="text" name="rows[{{ $i }}][first_name]" value="{{ $row['first_name'] }}">
                                </div>
                                <div class="field-group">
                                    <label>ユーザー名</label>
                                    <input type="text" name="rows[{{ $i }}][username]" value="{{ $row['username'] }}" required>
                                </div>
                            </div>

                            <div class="field-row cols-3">
                                <div class="field-group">
                                    <label>関係</label>
                                    <input type="text" name="rows[{{ $i }}][relationship_text]" value="{{ $row['relationship_text'] }}">
                                </div>
                                <div class="field-group">
                                    <label>肩書き1</label>
                                    <input type="text" name="rows[{{ $i }}][title1]" value="{{ $row['title1'] }}">
                                </div>
                                <div class="field-group">
                                    <label>肩書き2</label>
                                    <input type="text" name="rows[{{ $i }}][title2]" value="{{ $row['title2'] }}">
                                </div>
                            </div>

                            <div class="field-row cols-2">
                                <div class="field-group">
                                    <label>お言葉</label>
                                    <textarea name="rows[{{ $i }}][notes]">{{ $row['notes'] }}</textarea>
                                </div>
                                <div class="field-group">
                                    <label>自動判定</label>
                                    <div class="derived">
                                        <span class="derived-pill">
                                            <i class="fa-solid fa-people-arrows"></i>
                                            {{ $sideLabels[$row['guest_side']] ?? '立場未設定' }}
                                        </span>
                                        <span class="derived-pill">
                                            <i class="fa-solid fa-user-tag"></i>
                                            {{ $relationshipLabels[$row['relationship']] ?? '関係未設定' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="confirm-bar">
            <div>
                <label>初期パスワード <span class="req">*</span></label>
                <input type="text" name="initial_password" value="{{ old('initial_password') }}" placeholder="8文字以上" autocomplete="off">
                @error('initial_password')<span class="field-error">{{ $message }}</span>@enderror
                <p class="confirm-help">登録されたゲストは初回ログイン後にパスワード変更が必要になります。</p>
            </div>
            <div class="import-actions">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-user-plus"></i> 内容を再確認して登録する
                </button>
                @if ($errorCount > 0)
                    <span class="field-error" style="margin-top:0;">エラー行が残っている場合は、この画面に戻ります。</span>
                @endif
            </div>
        </section>
    </form>
</div>
@endsection
