@extends('layouts.app')
@section('title', 'プロフィール管理 | Admin')

@push('styles')
<style>
.pf-admin-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 28px;
}
.pf-admin-card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 3px 14px rgba(0,0,0,0.07);
    border-top: 3px solid #b38b59;
}
.pf-admin-card h2 {
    font-size: 0.78rem;
    font-weight: 700;
    color: #b38b59;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pf-photo-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e8d5b7;
    display: block;
    margin: 0 auto 16px;
}
.pf-photo-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: #fdfaf6;
    border: 2px dashed #e0d0bc;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    color: #d4b896;
    font-size: 2.5rem;
}
.pf-photo-hint {
    font-size: 0.75rem;
    color: #b0a090;
    text-align: center;
    margin-bottom: 16px;
}

@media (max-width: 640px) {
    .pf-admin-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="settings-wrap">

    <h1>プロフィール管理</h1>
    <p class="page-desc">新郎新婦それぞれの写真とプロフィール文を編集できます。</p>

    @if (session('success'))
    <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
    <div class="alert-error-box">入力内容に誤りがあります。各項目を確認してください。</div>
    @endif

    <form method="POST" action="{{ route('admin.profiles.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="pf-admin-grid">

            {{-- ── 新郎 ── --}}
            <div class="pf-admin-card">
                <h2><i class="fa-solid fa-user"></i>新郎プロフィール</h2>

                {{-- 現在の写真 --}}
                @if ($setting->groom_photo)
                    <img src="{{ asset('storage/' . $setting->groom_photo) }}"
                         alt="新郎" class="pf-photo-preview" id="groomPreview">
                @else
                    <div class="pf-photo-placeholder" id="groomPlaceholder">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <img src="" alt="" class="pf-photo-preview" id="groomPreview" style="display:none;">
                @endif
                <p class="pf-photo-hint">JPEG / PNG / WebP｜最大 5MB</p>

                <div class="form-group">
                    <label>プロフィール写真</label>
                    <input type="file" name="groom_photo" accept="image/*"
                           onchange="previewPhoto(this, 'groomPreview', 'groomPlaceholder')">
                    @error('groom_photo')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>プロフィール文</label>
                    <textarea name="groom_bio" rows="8"
                        placeholder="新郎のプロフィールや自己紹介文を入力してください。&#10;&#10;例：趣味、好きな食べ物、出身地など自由に書いてください。改行はそのまま反映されます。">{{ old('groom_bio', $setting->groom_bio) }}</textarea>
                    <p class="field-note">改行はそのまま反映されます（最大2000文字）</p>
                    @error('groom_bio')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

            {{-- ── 新婦 ── --}}
            <div class="pf-admin-card">
                <h2><i class="fa-solid fa-user"></i>新婦プロフィール</h2>

                @if ($setting->bride_photo)
                    <img src="{{ asset('storage/' . $setting->bride_photo) }}"
                         alt="新婦" class="pf-photo-preview" id="bridePreview">
                @else
                    <div class="pf-photo-placeholder" id="bridePlaceholder">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <img src="" alt="" class="pf-photo-preview" id="bridePreview" style="display:none;">
                @endif
                <p class="pf-photo-hint">JPEG / PNG / WebP｜最大 5MB</p>

                <div class="form-group">
                    <label>プロフィール写真</label>
                    <input type="file" name="bride_photo" accept="image/*"
                           onchange="previewPhoto(this, 'bridePreview', 'bridePlaceholder')">
                    @error('bride_photo')<span class="field-error">{{ $message }}</span>@enderror
                </div>

                <div class="form-group">
                    <label>プロフィール文</label>
                    <textarea name="bride_bio" rows="8"
                        placeholder="新婦のプロフィールや自己紹介文を入力してください。&#10;&#10;例：趣味、好きな食べ物、出身地など自由に書いてください。改行はそのまま反映されます。">{{ old('bride_bio', $setting->bride_bio) }}</textarea>
                    <p class="field-note">改行はそのまま反映されます（最大2000文字）</p>
                    @error('bride_bio')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>

        </div>

        <button type="submit" class="btn-save">保存する</button>

    </form>
</div>

<script>
function previewPhoto(input, previewId, placeholderId) {
    const preview     = document.getElementById(previewId);
    const placeholder = placeholderId ? document.getElementById(placeholderId) : null;
    if (!input.files || !input.files[0]) return;

    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.style.display = 'block';
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}
</script>
@endsection
