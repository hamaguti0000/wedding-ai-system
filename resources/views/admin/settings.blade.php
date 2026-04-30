@extends('layouts.app')
@section('title', '式の情報設定 | Admin')

@push('styles')
<style>
/* 設定画面固有スタイル */
.settings-section {
    margin-bottom: 32px;
    padding-bottom: 32px;
    border-bottom: 1px solid #f0ebe3;
}
.settings-section:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
.settings-section h2 {
    font-size: 0.78rem; font-weight: 700; color: #b38b59;
    letter-spacing: 2px; text-transform: uppercase; margin-bottom: 18px;
}

/* 席次表公開トグル */
.toggle-row { display: flex; align-items: center; gap: 14px; }
.toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-track {
    position: absolute; inset: 0; background: #e0d0bc;
    border-radius: 26px; cursor: pointer; transition: background 0.2s;
}
.toggle-track::before {
    content: ''; position: absolute; width: 20px; height: 20px;
    left: 3px; bottom: 3px; background: #fff;
    border-radius: 50%; transition: transform 0.2s;
}
.toggle-switch input:checked + .toggle-track { background: #27ae60; }
.toggle-switch input:checked + .toggle-track::before { transform: translateX(22px); }
.toggle-label { font-size: 0.9rem; color: #3d2f25; }
.toggle-note { font-size: 0.78rem; color: #b0a090; margin-top: 6px; }

@media (max-width: 767px) {
    .btn-save { width: 100%; }
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
