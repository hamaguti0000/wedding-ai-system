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
                    <p class="field-note">ホーム・アクセスページに「Google Maps で開く」ボタンとして表示されます。</p>
                    @error('venue_url')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>最寄り駅</label>
                    <input type="text" name="venue_nearest_station"
                        value="{{ old('venue_nearest_station', $setting->venue_nearest_station) }}"
                        placeholder="例：○○線 ○○駅 徒歩5分">
                </div>
            </div>

            {{-- ── アクセス情報 ── --}}
            <div class="settings-section">
                <h2>アクセス情報</h2>
                <div class="form-row" style="margin-bottom:16px;">
                    <div class="form-group">
                        <label>電車でお越しの方</label>
                        <textarea name="access_train" rows="3"
                            placeholder="例：○○線 ○○駅 西口より徒歩5分&#10;○○バス 終点下車すぐ">{{ old('access_train', $setting->access_train) }}</textarea>
                        <p class="field-note">アクセスページの「電車」カードに表示されます。</p>
                    </div>
                    <div class="form-group">
                        <label>お車でお越しの方</label>
                        <textarea name="access_car" rows="3"
                            placeholder="例：○○ICより車で10分&#10;カーナビ住所：○○市○○町1-2-3">{{ old('access_car', $setting->access_car) }}</textarea>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label>駐車場情報</label>
                    <input type="text" name="access_parking"
                        value="{{ old('access_parking', $setting->access_parking) }}"
                        placeholder="例：会場に無料駐車場あり（50台）">
                </div>
                <div class="form-group">
                    <label>Google Maps 埋め込みコード</label>
                    <textarea name="venue_map_embed" rows="4"
                        placeholder='Google Maps の「共有」→「地図を埋め込む」からコピーした &lt;iframe&gt; コードを貼り付けてください。'>{{ old('venue_map_embed', $setting->venue_map_embed) }}</textarea>
                    <p class="field-note">アクセスページに地図が埋め込み表示されます。Google Maps → 共有 → 地図を埋め込む → HTMLをコピー</p>
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

            {{-- ── 出欠回答 締め切り ── --}}
            <div class="settings-section">
                <h2>出欠回答</h2>
                <div class="form-group">
                    <label>締め切り日</label>
                    <input type="date" name="rsvp_deadline"
                        value="{{ old('rsvp_deadline', $setting->rsvp_deadline?->format('Y-m-d')) }}">
                    <p class="field-note">設定した日の翌日から招待状フォームへの送信ができなくなります。空欄の場合は制限なし。</p>
                    @error('rsvp_deadline')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- ── シャトルバス ── --}}
            <div class="settings-section">
                <h2>シャトルバス</h2>
                <div class="toggle-row" style="margin-bottom:12px;">
                    <label class="toggle-switch">
                        <input type="checkbox" name="shuttle_bus_enabled" value="1"
                            {{ old('shuttle_bus_enabled', $setting->shuttle_bus_enabled ?? false) ? 'checked' : '' }}>
                        <span class="toggle-track"></span>
                    </label>
                    <span class="toggle-label">シャトルバス案内をホームに表示する</span>
                </div>
                <p class="toggle-note" style="margin-bottom:20px;">オンにすると、ホーム画面にシャトルバスの案内カードが表示されます。</p>
                <div class="form-group">
                    <label>乗り場（出発地点）</label>
                    <input type="text" name="shuttle_bus_departure"
                        value="{{ old('shuttle_bus_departure', $setting->shuttle_bus_departure) }}"
                        placeholder="例：ＪＲ長崎駅西口（出島メッセ前一般乗場）">
                </div>
                <div class="form-group">
                    <label>出発時間</label>
                    <input type="text" name="shuttle_bus_times"
                        value="{{ old('shuttle_bus_times', $setting->shuttle_bus_times) }}"
                        placeholder="例：15:00 ／ 15:30 ／ 16:00 ／ 16:30">
                    <p class="field-note">スラッシュや読点で区切って入力してください。</p>
                </div>
                <div class="form-group">
                    <label>補足・ご注意</label>
                    <input type="text" name="shuttle_bus_note"
                        value="{{ old('shuttle_bus_note', $setting->shuttle_bus_note) }}"
                        placeholder="例：ご結婚式参加の方は予約なしでご利用いただけます">
                </div>
                <div class="form-group">
                    <label>乗り場の Google Maps URL</label>
                    <input type="url" name="shuttle_bus_map_url"
                        value="{{ old('shuttle_bus_map_url', $setting->shuttle_bus_map_url) }}"
                        placeholder="https://maps.google.com/...">
                    <p class="field-note">設定するとホームに「地図を見る」ボタンが表示されます。Google Maps で乗り場を検索 → 共有 → リンクをコピー。</p>
                    @error('shuttle_bus_map_url')<span class="field-error">{{ $message }}</span>@enderror
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
