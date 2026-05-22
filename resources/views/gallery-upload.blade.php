@extends('layouts.app')
@section('title', '思い出写真を投稿 | Wedding')

@push('styles')
<style>
main { padding: 0; text-align: initial; }

/* バナー */
.upload-banner {
    position: relative; height: 28vh; min-height: 180px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.upload-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.upload-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.upload-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.upload-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; }
.upload-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.5rem, 4.5vw, 2.4rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

/* コンテンツ */
.upload-wrap { max-width: 640px; margin: 0 auto; padding: 48px 20px 80px; }
.upload-intro { text-align: center; margin-bottom: 40px; }
.upload-intro__title { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 400; color: #3d2f25; margin-bottom: 12px; }
.upload-intro__desc { font-size: 0.9rem; color: #7a6a5a; line-height: 1.9; }

/* アップロードゾーン */
.upload-zone {
    border: 2px dashed #e8d5b7; border-radius: 12px;
    padding: 40px 24px; text-align: center; background: #fffdf9;
    cursor: pointer; transition: border-color 0.2s, background 0.2s;
    position: relative; margin-bottom: 20px;
}
.upload-zone:hover, .upload-zone.drag-over { border-color: #b38b59; background: #fef9f0; }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; z-index: 2; }
.upload-zone__icon { font-size: 2.5rem; margin-bottom: 12px; }
.upload-zone__text { font-size: 1rem; color: #5a4535; font-weight: 500; margin-bottom: 6px; }
.upload-zone__sub  { font-size: 0.78rem; color: #b0a090; }

/* プレビュー */
.photo-previews { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 8px; margin-bottom: 20px; }
.photo-preview-item { position: relative; border-radius: 8px; overflow: hidden; aspect-ratio: 1; }
.photo-preview-item img { width: 100%; height: 100%; object-fit: cover; display: block; border: 1px solid #e8d5b7; border-radius: 8px; }

/* フォームカード */
.upload-card { background: #fff; border: 1px solid #f0ebe3; border-radius: 12px; padding: 28px; box-shadow: 0 2px 12px rgba(61,47,37,0.05); }
.upload-card h2 { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 400; color: #3d2f25; margin-bottom: 20px; }
.form-group { margin-bottom: 18px; }
.form-label { display: block; font-size: 0.82rem; font-weight: 600; color: #5a4535; margin-bottom: 6px; }
.form-label .optional { font-size: 0.75rem; font-weight: 400; color: #9b8573; margin-left: 6px; }
.form-textarea { width: 100%; padding: 12px 14px; border: 1px solid #e0d4c4; border-radius: 8px; font-size: 0.9rem; color: #3d2f25; background: #fffdf9; resize: vertical; min-height: 80px; font-family: inherit; box-sizing: border-box; transition: border-color 0.15s; }
.form-textarea:focus { outline: none; border-color: #b38b59; }

.btn-upload { display: block; width: 100%; padding: 14px; background: #b38b59; color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 500; letter-spacing: 1px; cursor: pointer; transition: background 0.15s; margin-top: 8px; }
.btn-upload:hover { background: #9a7548; }
.btn-upload:disabled { background: #d0c0b0; cursor: not-allowed; }

.back-link { display: inline-flex; align-items: center; gap: 6px; color: #9b8573; font-size: 0.82rem; text-decoration: none; margin-bottom: 24px; }
.back-link:hover { color: #b38b59; }

.alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px; }
.alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 8px; font-size: 0.88rem; margin-bottom: 20px; }
</style>
@endpush

@section('content')

{{-- バナー --}}
<section class="upload-banner">
    @php
        $bannerImg = \App\Models\SiteImage::forDisplay('banner_gallery');
    @endphp
    @if($bannerImg)
        <img class="upload-banner__img" src="{{ asset('storage/'.$bannerImg->file_path) }}" alt="">
    @else
        <img class="upload-banner__img" src="{{ asset('img/チャペル.jpg') }}" alt="">
    @endif
    <div class="upload-banner__overlay"></div>
    <div class="upload-banner__text">
        <span class="upload-banner__eyebrow">Gallery</span>
        <h1 class="upload-banner__title">Share Your Memories</h1>
    </div>
</section>

<div class="upload-wrap">

    <a href="{{ route('gallery') }}" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> ギャラリーに戻る
    </a>

    <div class="upload-intro">
        <h2 class="upload-intro__title">思い出の写真を投稿する</h2>
        <p class="upload-intro__desc">
            当日撮った写真をみんなと共有しましょう。<br>
            投稿後、管理者の確認を経てギャラリーに掲載されます。
        </p>
    </div>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="upload-card">
        <h2>📸 写真を選ぶ</h2>

        <form method="POST" action="{{ route('gallery.upload.post') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <div class="form-group">
                <div class="upload-zone" id="uploadZone">
                    <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp"
                           id="photoInput" onchange="handleFiles(this.files)">
                    <div class="upload-zone__icon">📷</div>
                    <p class="upload-zone__text">クリックまたはドラッグ＆ドロップ</p>
                    <p class="upload-zone__sub">JPG / PNG / WebP・1枚10MB以内・最大10枚</p>
                </div>

                <div class="photo-previews" id="photoPreviews"></div>
            </div>

            <div class="form-group">
                <label class="form-label" for="message">
                    ひとことメッセージ <span class="optional">任意</span>
                </label>
                <textarea id="message" name="message" class="form-textarea"
                          placeholder="写真への一言コメントがあればどうぞ♪"
                          maxlength="500">{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="btn-upload" id="submitBtn" disabled>
                <i class="fa-solid fa-cloud-arrow-up"></i> 投稿する
            </button>
        </form>
    </div>

</div>

<script>
const zone     = document.getElementById('uploadZone');
const previews = document.getElementById('photoPreviews');
const submitBtn = document.getElementById('submitBtn');
let selectedFiles = [];

function handleFiles(files) {
    selectedFiles = Array.from(files);
    renderPreviews();
}

function renderPreviews() {
    previews.innerHTML = '';
    selectedFiles.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const item = document.createElement('div');
            item.className = 'photo-preview-item';
            item.innerHTML = `<img src="${e.target.result}" alt="">`;
            previews.appendChild(item);
        };
        reader.readAsDataURL(file);
    });
    submitBtn.disabled = selectedFiles.length === 0;
}

// ドラッグ＆ドロップ
zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', (e) => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const input = document.getElementById('photoInput');
    // DataTransfer を input に反映させる（直接 files セットできないので DataTransfer で代替）
    const dt = new DataTransfer();
    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
    input.files = dt.files;
    handleFiles(e.dataTransfer.files);
});
</script>
@endsection
