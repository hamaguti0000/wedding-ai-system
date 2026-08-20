@extends('layouts.app')
@section('title', '思い出写真を投稿 | Wedding')

@push('styles')
<style>
main { padding: 0; text-align: initial; background: #fbfaf7; }
.upload-hero {
    position: relative; min-height: 300px; overflow: hidden; display: flex; align-items: flex-end;
    padding: 112px 20px 34px; box-sizing: border-box;
}
.upload-hero__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(.48) saturate(.85); }
.upload-hero__shade { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(20,12,4,.12), rgba(20,12,4,.72)); }
.upload-hero__inner { position: relative; z-index: 1; width: min(760px, 100%); margin: 0 auto; color: #fff; }
.upload-hero__eyebrow { display: block; margin-bottom: 9px; color: rgba(255,255,255,.72); font-size: .68rem; letter-spacing: 4px; text-transform: uppercase; }
.upload-hero__title { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(2rem, 7vw, 3.6rem); font-weight: 400; line-height: 1.08; }
.upload-hero__lead { margin: 14px 0 0; color: rgba(255,255,255,.86); font-size: .92rem; line-height: 1.8; }

.upload-wrap { width: min(720px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 88px; }
.back-link { display: inline-flex; align-items: center; gap: 7px; color: #8d7660; font-size: .84rem; text-decoration: none; margin-bottom: 18px; }
.upload-card { background: #fff; border: 1px solid #eee6dc; border-radius: 20px; padding: 22px; box-shadow: 0 18px 48px rgba(61,47,37,.08); }
.upload-card__head { display: flex; justify-content: space-between; gap: 14px; align-items: flex-start; margin-bottom: 18px; }
.upload-card__title { margin: 0; color: #3d2f25; font-size: 1.1rem; font-weight: 800; }
.upload-card__desc { margin: 6px 0 0; color: #8a7a68; font-size: .84rem; line-height: 1.7; }
.upload-count { flex: 0 0 auto; min-width: 72px; padding: 9px 12px; border-radius: 12px; background: #f7f1e9; color: #755f48; text-align: center; font-size: .74rem; }
.upload-count strong { display: block; color: #3d2f25; font-size: 1.1rem; line-height: 1; }

.upload-zone {
    position: relative; display: grid; place-items: center; min-height: 190px; border: 2px dashed #dec8ad; border-radius: 18px;
    background: linear-gradient(180deg, #fffdf9, #fbf4ec); text-align: center; cursor: pointer; transition: border-color .18s, transform .18s, background .18s;
}
.upload-zone:hover, .upload-zone.drag-over { border-color: #b38b59; background: #fff8ed; transform: translateY(-1px); }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; z-index: 2; }
.upload-zone__icon { width: 54px; height: 54px; margin: 0 auto 12px; display: grid; place-items: center; border-radius: 999px; background: #3d2f25; color: #fff; font-size: 1.25rem; }
.upload-zone__text { margin: 0; color: #3d2f25; font-size: 1rem; font-weight: 800; }
.upload-zone__sub { margin: 7px 0 0; color: #a69583; font-size: .78rem; }
.photo-previews { display: grid; grid-template-columns: repeat(auto-fill, minmax(104px, 1fr)); gap: 10px; margin: 16px 0 20px; }
.photo-preview-item { position: relative; aspect-ratio: 1; border-radius: 14px; overflow: hidden; background: #efe7dc; border: 1px solid #eee0d0; }
.photo-preview-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.photo-preview-item button { position: absolute; right: 7px; top: 7px; width: 30px; height: 30px; border: 0; border-radius: 999px; color: #fff; background: rgba(20,12,4,.62); cursor: pointer; }
.photo-preview-item span { position: absolute; left: 7px; bottom: 7px; max-width: calc(100% - 14px); padding: 3px 7px; border-radius: 999px; background: rgba(255,255,255,.88); color: #6d5a45; font-size: .68rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.form-group { margin-top: 16px; }
.form-label { display: block; margin-bottom: 7px; color: #5d4635; font-size: .84rem; font-weight: 800; }
.form-label .optional { margin-left: 6px; color: #aa9887; font-size: .75rem; font-weight: 500; }
.form-textarea { width: 100%; min-height: 88px; box-sizing: border-box; padding: 13px 14px; border: 1px solid #e0d4c4; border-radius: 12px; background: #fffdf9; color: #3d2f25; font-size: .92rem; line-height: 1.7; resize: vertical; font-family: inherit; }
.form-textarea:focus { outline: none; border-color: #b38b59; box-shadow: 0 0 0 3px rgba(179,139,89,.12); }
.upload-actions { display: flex; align-items: center; gap: 12px; margin-top: 18px; }
.btn-upload { flex: 1; min-height: 48px; border: 0; border-radius: 999px; background: #b38b59; color: #fff; font-size: .95rem; font-weight: 800; cursor: pointer; }
.btn-upload:disabled { background: #d0c0b0; cursor: not-allowed; }
.upload-status { min-height: 22px; color: #8a7a68; font-size: .82rem; }
.upload-status.is-ok { color: #15803d; }
.upload-status.is-error { color: #dc2626; }
.alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 12px 16px; border-radius: 12px; font-size: .88rem; margin-bottom: 16px; }
.alert-error { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 12px; font-size: .88rem; margin-bottom: 16px; }
@media (max-width: 640px) {
    .upload-hero { min-height: 280px; padding: 104px 18px 28px; }
    .upload-wrap { width: calc(100% - 24px); padding-top: 18px; }
    .upload-card { padding: 18px; border-radius: 18px; }
    .upload-card__head { flex-direction: column; }
    .upload-count { width: 100%; box-sizing: border-box; }
    .photo-previews { grid-template-columns: repeat(2, 1fr); }
    .upload-actions { flex-direction: column; align-items: stretch; }
}
</style>
@endpush

@section('content')
<section class="upload-hero">
    @php $bannerImg = $bannerImage ?? null; @endphp
    @if($bannerImg)
        <img class="upload-hero__img" src="{{ $bannerImg->url }}" alt="">
    @else
        <img class="upload-hero__img" src="{{ asset('img/チャペル.jpg') }}" alt="">
    @endif
    <div class="upload-hero__shade"></div>
    <div class="upload-hero__inner">
        <span class="upload-hero__eyebrow">Share Memories</span>
        <h1 class="upload-hero__title">写真を投稿する</h1>
        <p class="upload-hero__lead">スマホの写真をまとめて投稿できます。投稿後は管理者の確認後にギャラリーへ反映されます。</p>
    </div>
</section>

<div class="upload-wrap">
    <a href="{{ route('gallery') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> ギャラリーに戻る</a>

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
        <div class="upload-card__head">
            <div>
                <h2 class="upload-card__title">写真を選択</h2>
                <p class="upload-card__desc">最大10枚まで一度に投稿できます。選択後に不要な写真だけ外せます。</p>
            </div>
            <div class="upload-count"><strong id="photoCount">0</strong>枚選択中</div>
        </div>

        <form method="POST" action="{{ route('gallery.upload.post') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <div class="upload-zone" id="uploadZone">
                <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" id="photoInput">
                <div>
                    <div class="upload-zone__icon"><i class="fa-solid fa-camera"></i></div>
                    <p class="upload-zone__text">写真を選ぶ / ドロップする</p>
                    <p class="upload-zone__sub">JPG / PNG / WebP・1枚10MB以内</p>
                </div>
            </div>

            <div class="photo-previews" id="photoPreviews"></div>

            <div class="form-group">
                <label class="form-label" for="message">ひとことメッセージ <span class="optional">任意</span></label>
                <textarea id="message" name="message" class="form-textarea" placeholder="写真への一言コメントがあればどうぞ" maxlength="500">{{ old('message') }}</textarea>
            </div>

            <div class="upload-actions">
                <button type="submit" class="btn-upload" id="submitBtn" disabled><i class="fa-solid fa-cloud-arrow-up"></i> 投稿する</button>
                <span class="upload-status" id="uploadStatus" aria-live="polite"></span>
            </div>
        </form>
    </div>
</div>

<script>
const zone = document.getElementById('uploadZone');
const input = document.getElementById('photoInput');
const previews = document.getElementById('photoPreviews');
const submitBtn = document.getElementById('submitBtn');
const photoCount = document.getElementById('photoCount');
const statusEl = document.getElementById('uploadStatus');
const form = document.getElementById('uploadForm');
let selectedFiles = [];

function setStatus(message, type = '') {
    statusEl.textContent = message;
    statusEl.className = `upload-status ${type}`.trim();
}
function syncInput() {
    const dt = new DataTransfer();
    selectedFiles.slice(0, 10).forEach(file => dt.items.add(file));
    input.files = dt.files;
}
function addFiles(files) {
    const incoming = Array.from(files).filter(file => file.type.startsWith('image/'));
    selectedFiles = selectedFiles.concat(incoming).slice(0, 10);
    syncInput();
    renderPreviews();
}
function removeFile(index) {
    selectedFiles.splice(index, 1);
    syncInput();
    renderPreviews();
}
function renderPreviews() {
    previews.innerHTML = '';
    selectedFiles.forEach((file, index) => {
        const url = URL.createObjectURL(file);
        const item = document.createElement('div');
        item.className = 'photo-preview-item';
        item.innerHTML = `<img src="${url}" alt=""><button type="button" aria-label="削除"><i class="fa-solid fa-xmark"></i></button><span>${file.name}</span>`;
        item.querySelector('button').addEventListener('click', () => removeFile(index));
        previews.appendChild(item);
    });
    photoCount.textContent = selectedFiles.length;
    submitBtn.disabled = selectedFiles.length === 0;
    if (selectedFiles.length > 0) setStatus('');
}

input.addEventListener('change', () => addFiles(input.files));
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    addFiles(e.dataTransfer.files);
});
form.addEventListener('submit', async e => {
    e.preventDefault();
    if (selectedFiles.length === 0) return;
    submitBtn.disabled = true;
    setStatus('投稿中です...');
    try {
        const res = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form),
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || !json.success) {
            const errors = json.errors ? Object.values(json.errors).flat().join('\n') : (json.message || '投稿に失敗しました');
            throw new Error(errors);
        }
        selectedFiles = [];
        input.value = '';
        document.getElementById('message').value = '';
        syncInput();
        renderPreviews();
        setStatus(json.message || '投稿しました', 'is-ok');
    } catch (error) {
        setStatus(error.message || '投稿に失敗しました', 'is-error');
        submitBtn.disabled = selectedFiles.length === 0;
    }
});
</script>
@endsection
