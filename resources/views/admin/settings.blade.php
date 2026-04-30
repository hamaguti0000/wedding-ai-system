@extends('layouts.app')
@section('title', '式の情報設定 | Admin')

@push('styles')
<style>
.settings-wrap {
    max-width: 720px;
    margin: 24px auto 80px;
    padding: 0 14px;
    font-family: 'Noto Sans JP', sans-serif;
}
.settings-wrap h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    color: #b38b59;
    margin-bottom: 6px;
}
.settings-wrap .page-desc {
    font-size: 0.85rem;
    color: #999;
    margin-bottom: 24px;
}
@media (min-width: 768px) {
    .settings-wrap { margin-top: 40px; padding: 0 20px; }
    .settings-wrap h1 { font-size: 1.8rem; }
    .settings-wrap .page-desc { margin-bottom: 32px; }
}

.settings-card {
    background: #fff;
    border-radius: 14px;
    padding: 20px 16px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.07);
}
@media (min-width: 480px) {
    .settings-card { padding: 28px 28px; }
}
@media (min-width: 768px) {
    .settings-card { padding: 36px 40px; }
}

.settings-section {
    margin-bottom: 36px;
    padding-bottom: 36px;
    border-bottom: 1px solid #f0ebe3;
}
.settings-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.settings-section h2 {
    font-size: 0.78rem;
    font-weight: 700;
    color: #b38b59;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-group {
    margin-bottom: 18px;
}
.form-group label {
    display: block;
    font-size: 0.82rem;
    color: #7a6a5a;
    margin-bottom: 6px;
    font-weight: 500;
}
.form-group .required {
    color: #c0392b;
    margin-left: 3px;
    font-size: 0.8rem;
}
.form-group input,
.form-group textarea {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid #e0d0bc;
    border-radius: 6px;
    font-size: 0.95rem;
    font-family: 'Noto Sans JP', sans-serif;
    color: #3d2f25;
    background: #fffdf9;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-group input:focus,
.form-group textarea:focus {
    border-color: #b38b59;
    outline: none;
    box-shadow: 0 0 0 3px rgba(179,139,89,0.12);
}
.form-group textarea {
    resize: vertical;
    min-height: 120px;
    line-height: 1.8;
}
.form-group .field-note {
    font-size: 0.78rem;
    color: #b0a090;
    margin-top: 5px;
}
.field-error {
    display: block;
    color: #c0392b;
    font-size: 0.82rem;
    margin-top: 5px;
}

.alert-success {
    background: #eafaf1;
    border: 1px solid #a9dfbf;
    color: #1e8449;
    padding: 13px 18px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 0.92rem;
}
.alert-error-box {
    background: #fdf2f2;
    border: 1px solid #f5b7b1;
    color: #c0392b;
    padding: 13px 18px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 0.92rem;
}

.btn-save {
    padding: 13px 36px;
    background: #b38b59;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.95rem;
    font-family: 'Noto Sans JP', sans-serif;
    font-weight: 500;
    letter-spacing: 1px;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-save:hover { background: #9a7447; }

/* 席次表公開トグル */
.toggle-row {
    display: flex;
    align-items: center;
    gap: 14px;
}
.toggle-switch {
    position: relative;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-track {
    position: absolute;
    inset: 0;
    background: #e0d0bc;
    border-radius: 26px;
    cursor: pointer;
    transition: background 0.2s;
}
.toggle-track::before {
    content: '';
    position: absolute;
    width: 20px; height: 20px;
    left: 3px; bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.2s;
}
.toggle-switch input:checked + .toggle-track { background: #27ae60; }
.toggle-switch input:checked + .toggle-track::before { transform: translateX(22px); }
.toggle-label { font-size: 0.9rem; color: #3d2f25; }
.toggle-note { font-size: 0.78rem; color: #b0a090; margin-top: 6px; }

.admin-nav {
    display: flex;
    gap: 12px;
    margin-bottom: 28px;
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
.admin-nav a:hover {
    background: #b38b59;
    color: #fff;
}

@media (max-width: 767px) {
    .form-row { grid-template-columns: 1fr; }
    .admin-nav a { padding: 7px 12px; font-size: 0.8rem; }
    .btn-save { width: 100%; padding: 13px; }
}
</style>
@endpush

@section('content')
<div class="settings-wrap">

    <h1>式の情報設定</h1>
    <p class="page-desc">ホーム画面に表示される日時・会場・ご挨拶文を編集できます。</p>

    @if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
    <div class="alert-error-box">
        入力内容に誤りがあります。各項目を確認してください。
    </div>
    @endif

    <div class="settings-card">
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            {{-- ── 新郎新婦名 ── --}}
            <div class="settings-section">
                <h2>新郎新婦</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>新郎氏名 <span class="required">*</span></label>
                        <input type="text" name="groom_name"
                            value="{{ old('groom_name', $setting->groom_name) }}"
                            placeholder="例：濵口 翔">
                        @error('groom_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>新婦氏名 <span class="required">*</span></label>
                        <input type="text" name="bride_name"
                            value="{{ old('bride_name', $setting->bride_name) }}"
                            placeholder="例：馬場 弥礼">
                        @error('bride_name')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>新郎ローマ字名</label>
                        <input type="text" name="groom_name_en"
                            value="{{ old('groom_name_en', $setting->groom_name_en) }}"
                            placeholder="例：Kakeru Hamaguchi">
                        <p class="field-note">トップページのヒーロー画像に表示されます</p>
                    </div>
                    <div class="form-group">
                        <label>新婦ローマ字名</label>
                        <input type="text" name="bride_name_en"
                            value="{{ old('bride_name_en', $setting->bride_name_en) }}"
                            placeholder="例：Mirai Baba">
                    </div>
                </div>
            </div>

            {{-- ── 日時 ── --}}
            <div class="settings-section">
                <h2>日時</h2>
                <div class="form-group">
                    <label>挙式日 <span class="required">*</span></label>
                    <input type="date" name="ceremony_date"
                        value="{{ old('ceremony_date', $setting->ceremony_date?->format('Y-m-d')) }}">
                    @error('ceremony_date')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>挙式時刻 <span class="required">*</span></label>
                        <input type="time" name="ceremony_time"
                            value="{{ old('ceremony_time', $setting->ceremonyTimeFormatted()) }}">
                        @error('ceremony_time')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label>披露宴開始時刻 <span class="required">*</span></label>
                        <input type="time" name="reception_time"
                            value="{{ old('reception_time', $setting->receptionTimeFormatted()) }}">
                        @error('reception_time')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

            {{-- ── 会場 ── --}}
            <div class="settings-section">
                <h2>会場</h2>
                <div class="form-group">
                    <label>会場名 <span class="required">*</span></label>
                    <input type="text" name="venue_name"
                        value="{{ old('venue_name', $setting->venue_name) }}"
                        placeholder="例：◯◯チャペル">
                    @error('venue_name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>会場住所 <span class="required">*</span></label>
                    <input type="text" name="venue_address"
                        value="{{ old('venue_address', $setting->venue_address) }}"
                        placeholder="例：東京都渋谷区◯◯1-2-3">
                    @error('venue_address')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>Google Maps URL</label>
                    <input type="url" name="venue_url"
                        value="{{ old('venue_url', $setting->venue_url) }}"
                        placeholder="https://maps.google.com/...">
                    <p class="field-note">入力するとホーム画面に「Google Map →」リンクが表示されます。</p>
                    @error('venue_url')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- ── ご挨拶文 ── --}}
            <div class="settings-section">
                <h2>ご挨拶文</h2>
                <div class="form-group">
                    <label>本文</label>
                    <textarea name="message"
                        placeholder="ホーム画面のメッセージセクションに表示されます。">{{ old('message', $setting->message) }}</textarea>
                    <p class="field-note">改行はそのまま反映されます。</p>
                    @error('message')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- ── 席次表公開設定 ── --}}
            <div class="settings-section">
                <h2>席次表</h2>
                <div class="toggle-row">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_seating_public" value="1"
                            {{ old('is_seating_public', $setting->is_seating_public ?? false) ? 'checked' : '' }}>
                        <span class="toggle-track"></span>
                    </label>
                    <span class="toggle-label">ゲストに席次表を公開する</span>
                </div>
                <p class="toggle-note">オフの場合、ゲストが /seating にアクセスするとホームへリダイレクトされます。</p>
            </div>

            <button type="submit" class="btn-save">保存する</button>

        </form>
    </div>
</div>
@endsection
