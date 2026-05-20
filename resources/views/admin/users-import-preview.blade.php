@extends('layouts.app')
@section('title', 'CSV登録確認 | Admin')

@push('styles')
<style>
.import-wrap { max-width: 1180px; margin: 0 auto; }
.import-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
.import-actions { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.import-card { background: #fff; border-radius: 14px; padding: 22px 24px; box-shadow: 0 4px 14px rgba(0,0,0,0.07); margin-bottom: 24px; }
.import-summary { display: flex; gap: 10px; flex-wrap: wrap; margin: 0 0 16px; }
.summary-pill { display: inline-flex; align-items: center; gap: 6px; padding: 7px 10px; border-radius: 999px; background: #f7efe5; color: #7b5d3b; font-size: 0.85rem; font-weight: 700; }
.summary-pill.error { background: #fff1f2; color: #be123c; }
.import-table-wrap { overflow-x: auto; border: 1px solid #f0ebe3; border-radius: 10px; }
.import-table { width: 100%; min-width: 1120px; border-collapse: collapse; }
.import-table th { background: #fffdf9; color: #7b6a5c; font-size: 0.78rem; text-align: left; padding: 10px 8px; border-bottom: 1px solid #f0ebe3; white-space: nowrap; }
.import-table td { padding: 8px; border-bottom: 1px solid #f5eee6; vertical-align: top; }
.import-table tr.has-error { background: #fff7f8; }
.import-table input, .import-table textarea { width: 100%; min-width: 92px; border: 1px solid #e0d0bc; border-radius: 6px; padding: 8px 9px; font-size: 0.86rem; background: #fff; }
.import-table textarea { min-height: 38px; resize: vertical; }
.import-table input:focus, .import-table textarea:focus { outline: none; border-color: #b38b59; box-shadow: 0 0 0 2px rgba(179,139,89,0.12); }
.status-ok { color: #15803d; font-weight: 700; white-space: nowrap; }
.status-error { color: #be123c; font-weight: 700; white-space: nowrap; }
.row-errors { margin: 5px 0 0; padding-left: 16px; color: #be123c; font-size: 0.78rem; line-height: 1.5; }
.derived { color: #7b6a5c; font-size: 0.8rem; line-height: 1.6; min-width: 120px; }
.confirm-grid { display: grid; grid-template-columns: minmax(220px, 340px) 1fr; gap: 14px; align-items: end; margin-top: 18px; }
.confirm-grid input { border: 1px solid #e0d0bc; border-radius: 6px; padding: 10px 12px; width: 100%; }
.confirm-help { color: #7b6a5c; font-size: 0.82rem; line-height: 1.7; margin: 6px 0 0; }
.field-error { display: block; color: #be123c; white-space: pre-line; margin-top: 6px; font-size: 0.84rem; }
@media (max-width: 767px) {
    .import-head { display: block; }
    .import-actions { margin-top: 12px; }
    .import-card { padding: 16px; }
    .confirm-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
@php
    $errorCount = collect($rows)->filter(fn ($row) => count($row['errors']) > 0)->count();
@endphp

<div class="import-wrap">
    <div class="import-head">
        <div>
            <h1>CSV登録確認</h1>
            <p class="confirm-help">内容を確認し、データエラーのある行は編集してから登録してください。</p>
        </div>
        <div class="import-actions">
            <a href="{{ route('admin.users') }}" class="btn-sm btn-sm-pw" style="text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> ユーザー管理に戻る
            </a>
        </div>
    </div>

    @if ($errors->has('rows'))
        <div class="alert alert-error">{{ $errors->first('rows') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.import') }}">
        @csrf

        <div class="import-card">
            <div class="import-summary">
                <span class="summary-pill"><i class="fa-solid fa-users"></i> 読み込み {{ count($rows) }}名</span>
                <span class="summary-pill {{ $errorCount > 0 ? 'error' : '' }}">
                    <i class="fa-solid fa-triangle-exclamation"></i> エラー {{ $errorCount }}名
                </span>
            </div>

            <div class="import-table-wrap">
                <table class="import-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>No.</th>
                            <th>姓</th>
                            <th>名</th>
                            <th>ユーザー名</th>
                            <th>関係</th>
                            <th>肩書き1</th>
                            <th>肩書き2</th>
                            <th>判定</th>
                            <th>お言葉</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr class="{{ count($row['errors']) > 0 ? 'has-error' : '' }}">
                                <td>
                                    @if (count($row['errors']) > 0)
                                        <span class="status-error">要修正</span>
                                        <ul class="row-errors">
                                            @foreach ($row['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="status-ok">登録可</span>
                                    @endif
                                </td>
                                <td>{{ $row['line'] }}</td>
                                <td><input type="text" name="rows[{{ $i }}][last_name]" value="{{ $row['last_name'] }}"></td>
                                <td><input type="text" name="rows[{{ $i }}][first_name]" value="{{ $row['first_name'] }}"></td>
                                <td><input type="text" name="rows[{{ $i }}][username]" value="{{ $row['username'] }}" required></td>
                                <td><input type="text" name="rows[{{ $i }}][relationship_text]" value="{{ $row['relationship_text'] }}"></td>
                                <td><input type="text" name="rows[{{ $i }}][title1]" value="{{ $row['title1'] }}"></td>
                                <td><input type="text" name="rows[{{ $i }}][title2]" value="{{ $row['title2'] }}"></td>
                                <td>
                                    <div class="derived">
                                        立場: {{ ['groom' => '新郎側', 'bride' => '新婦側'][$row['guest_side']] ?? '未設定' }}<br>
                                        関係: {{ ['family' => '親族', 'friend' => '友人・知人', 'colleague' => '職場関係', 'other' => 'その他'][$row['relationship']] ?? '未設定' }}
                                    </div>
                                </td>
                                <td><textarea name="rows[{{ $i }}][notes]">{{ $row['notes'] }}</textarea></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="confirm-grid">
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
                        <span class="field-error" style="margin-top:0;">エラー行が残っている場合は、再確認後にこの画面へ戻ります。</span>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
