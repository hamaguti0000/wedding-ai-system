@extends('layouts.app')
@section('title', 'お知らせ管理 | Admin')

@push('styles')
<style>
/* ── 一覧 ── */
.news-item {
    background: #fff; border-radius: 10px; padding: 16px 20px;
    margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f0ebe3;
}
.news-item.inactive { opacity: 0.55; }
.news-item__head { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap; }
.news-item__date { font-size: 0.8rem; color: #a89282; white-space: nowrap; }
.news-item__layout-badge {
    font-size: 0.64rem; padding: 2px 8px; border-radius: 3px;
    background: #f0f0f8; color: #6060a0; border: 1px solid #d8d8f0; letter-spacing: 0.5px;
}
.news-item__tag {
    font-size: 0.7rem; padding: 2px 10px; border-radius: 2px;
    background: #fef9f0; color: #b38b59; border: 1px solid #e8d5b7; letter-spacing: 1px;
}
.news-item__title { font-size: 0.95rem; font-weight: 600; color: #3d2f25; margin-bottom: 2px; }
.news-item__body { font-size: 0.88rem; color: #6b5b4e; white-space: pre-wrap; }
.news-item__thumb {
    width: 80px; height: 56px; object-fit: cover; border-radius: 5px;
    border: 1px solid #e8d5b7; flex-shrink: 0;
}
.news-item__actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; align-items: center; }

/* ── 追加フォーム ── */
.add-card { background: #fff; border-radius: 12px; padding: 22px 24px; box-shadow: 0 3px 12px rgba(0,0,0,0.07); margin-bottom: 28px; border: 2px dashed #e8d5b7; }
.add-card h3 { font-size: 0.78rem; font-weight: 700; color: #b38b59; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 16px; }
.tag-presets { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.tag-preset { padding: 4px 12px; border: 1px solid #e0d0bc; border-radius: 20px; cursor: pointer; background: #fffdf9; font-size: 0.78rem; color: #9b8573; transition: all 0.15s; }
.tag-preset:hover, .tag-preset.sel { background: #fef9f0; border-color: #b38b59; color: #b38b59; }

/* ── レイアウト選択 ── */
.layout-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 8px; }
.layout-opt { display: none; }
.layout-label {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 10px 8px; border: 2px solid #e0d0bc; border-radius: 8px;
    cursor: pointer; background: #fffdf9; transition: all 0.15s; text-align: center;
}
.layout-label:hover { border-color: #b38b59; background: #fef9f0; }
.layout-opt:checked + .layout-label { border-color: #b38b59; background: #fef4e4; color: #9a7447; }
.layout-icon { font-size: 1.3rem; line-height: 1; }
.layout-name { font-size: 0.72rem; font-weight: 600; color: #6b5b4e; letter-spacing: 0.5px; }
.layout-opt:checked + .layout-label .layout-name { color: #b38b59; }

/* ── 画像アップロード ── */
.img-upload-area {
    border: 2px dashed #e0d0bc; border-radius: 8px; padding: 20px;
    text-align: center; background: #fffdf9; cursor: pointer;
    transition: border-color 0.15s, background 0.15s;
    position: relative;
}
.img-upload-area:hover, .img-upload-area.drag-over {
    border-color: #b38b59; background: #fef9f0;
}
.img-upload-area input[type=file] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.img-upload-area__icon { font-size: 1.6rem; color: #c0b0a0; margin-bottom: 6px; }
.img-upload-area__text { font-size: 0.82rem; color: #9b8573; }
.img-upload-area__sub { font-size: 0.72rem; color: #b0a090; margin-top: 4px; }
.img-preview-wrap { margin-top: 10px; display: none; }
.img-preview { max-width: 100%; max-height: 180px; border-radius: 6px; object-fit: cover; border: 1px solid #e8d5b7; }
.img-remove-btn { margin-top: 6px; font-size: 0.75rem; color: #c0392b; background: none; border: none; cursor: pointer; text-decoration: underline; }

@media (max-width: 767px) {
    .layout-grid { grid-template-columns: repeat(3, 1fr); }
}
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-bullhorn" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>お知らせ管理</h1>
    <p class="page-desc">ゲストのホーム画面に表示するお知らせを管理します。画像付き・テキストのみ など6種類のデザインを選択できます。</p>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    {{-- ══ 追加フォーム ══ --}}
    <div class="add-card">
        <h3>新しいお知らせを追加</h3>
        <form method="POST" action="{{ route('admin.news.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- 日付・タグ --}}
            <div class="form-row" style="margin-bottom:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>日付 <span class="req">*</span></label>
                    <input type="date" name="published_date"
                           value="{{ old('published_date', now()->format('Y-m-d')) }}" required>
                    @error('published_date')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>タグ</label>
                    <input type="text" name="tag" id="tagInputAdd"
                           value="{{ old('tag') }}" placeholder="New / Info / 重要 など" maxlength="30">
                    <div class="tag-presets">
                        @foreach(['New','Info','重要','更新','写真'] as $t)
                        <span class="tag-preset" onclick="setTag('tagInputAdd','{{ $t }}',this)">{{ $t }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- タイトル --}}
            <div class="form-group" style="margin-bottom:12px;">
                <label>見出し（任意）</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       placeholder="大きく表示される見出し（画像付きレイアウト推奨）" maxlength="100">
            </div>

            {{-- 本文 --}}
            <div class="form-group" style="margin-bottom:16px;">
                <label>本文 <span class="req">*</span></label>
                <textarea name="body" rows="3" placeholder="ゲストへのメッセージを入力">{{ old('body') }}</textarea>
                @error('body')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            {{-- レイアウト選択 --}}
            <div class="form-group" style="margin-bottom:16px;">
                <label>デザイン</label>
                <div class="layout-grid" id="layoutGridAdd">
                    @foreach([
                        ['text',  'fa-align-left',       'テキストのみ'],
                        ['top',   'fa-image',            '画像+テキスト（縦）'],
                        ['left',  'fa-table-columns',    '画像左・テキスト右'],
                        ['right', 'fa-object-ungroup',   '画像右・テキスト左'],
                        ['hero',  'fa-panorama',         'フルイメージ'],
                        ['card',  'fa-id-card',          'カード型'],
                    ] as [$val, $icon, $label])
                    <input type="radio" name="layout" id="la-add-{{ $val }}" class="layout-opt"
                           value="{{ $val }}" {{ (old('layout','text') === $val) ? 'checked' : '' }}>
                    <label class="layout-label" for="la-add-{{ $val }}">
                        <span class="layout-icon"><i class="fa-solid {{ $icon }}"></i></span>
                        <span class="layout-name">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- 画像アップロード --}}
            <div class="form-group" style="margin-bottom:16px;" id="imgFieldAdd">
                <label>画像（JPG/PNG/WebP・5MB以内）</label>
                <div class="img-upload-area" id="uploadAreaAdd">
                    <input type="file" name="image" accept="image/*"
                           onchange="previewImage(this,'previewAdd','previewWrapAdd')">
                    <div class="img-upload-area__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <p class="img-upload-area__text">クリックまたはドラッグ＆ドロップ</p>
                    <p class="img-upload-area__sub">JPG / PNG / WebP・最大 5MB</p>
                </div>
                <div class="img-preview-wrap" id="previewWrapAdd">
                    <img id="previewAdd" class="img-preview" src="" alt="プレビュー">
                    <br><button type="button" class="img-remove-btn"
                        onclick="clearImage(document.querySelector('#uploadAreaAdd input'),'previewAdd','previewWrapAdd')">
                        画像を削除
                    </button>
                </div>
                @error('image')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn-primary"><i class="fa-solid fa-plus"></i> 追加する</button>
        </form>
    </div>

    {{-- ══ 一覧 ══ --}}
    <div style="margin-bottom:8px;font-size:0.82rem;color:#999;">{{ $news->count() }}件</div>

    @if ($news->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">📢</div>
        <p class="empty-state__title">まだお知らせがありません</p>
    </div>
    @else
    @foreach ($news as $item)
    <div class="news-item {{ $item->is_active ? '' : 'inactive' }}">
        <div class="news-item__head">
            <span class="news-item__date">{{ $item->published_date->format('Y.m.d') }}</span>
            <span class="news-item__layout-badge">{{ ['text'=>'テキスト','top'=>'画像縦','left'=>'画像左','right'=>'画像右','hero'=>'フルイメージ','card'=>'カード'][$item->layout ?? 'text'] }}</span>
            @if ($item->tag)
            <span class="news-item__tag">{{ $item->tag }}</span>
            @endif
            @if (!$item->is_active)
            <span style="font-size:0.7rem;color:#aaa;border:1px solid #ddd;padding:2px 7px;border-radius:3px;">非表示</span>
            @endif
        </div>
        <div style="display:flex;gap:12px;align-items:flex-start;">
            @if ($item->image_url)
            <img src="{{ $item->image_url }}" alt="" class="news-item__thumb">
            @endif
            <div style="flex:1;min-width:0;">
                @if ($item->title)
                <p class="news-item__title">{{ $item->title }}</p>
                @endif
                <p class="news-item__body">{{ Str::limit($item->body, 80) }}</p>
            </div>
        </div>
        <div class="news-item__actions">
            <form method="POST" action="{{ route('admin.news.move-up', $item->id) }}">
                @csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="上へ"><i class="fa-solid fa-chevron-up"></i></button>
            </form>
            <form method="POST" action="{{ route('admin.news.move-down', $item->id) }}">
                @csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="下へ"><i class="fa-solid fa-chevron-down"></i></button>
            </form>
            <button class="btn-sm btn-sm-pw" onclick="toggleEdit({{ $item->id }})">
                <i class="fa-solid fa-pen"></i> 編集
            </button>
            <form method="POST" action="{{ route('admin.news.destroy', $item->id) }}"
                  onsubmit="return confirm('削除しますか？')">
                @csrf @method('DELETE')
                <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
            </form>
        </div>
    </div>

    {{-- 編集フォーム --}}
    <div id="edit-{{ $item->id }}" style="display:none;background:#fef9f0;border-radius:0 0 10px 10px;padding:18px 20px;margin-top:-10px;margin-bottom:10px;border:1px solid #e8d5b7;border-top:none;">
        <form method="POST" action="{{ route('admin.news.update', $item->id) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="form-row" style="margin-bottom:10px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label>日付</label>
                    <input type="date" name="published_date" value="{{ $item->published_date->format('Y-m-d') }}" required>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>タグ</label>
                    <input type="text" name="tag" id="tagInputEdit{{ $item->id }}"
                           value="{{ $item->tag }}" placeholder="New / Info など" maxlength="30">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>見出し</label>
                <input type="text" name="title" value="{{ $item->title }}" maxlength="100">
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>本文</label>
                <textarea name="body" rows="3">{{ $item->body }}</textarea>
            </div>

            {{-- レイアウト --}}
            <div class="form-group" style="margin-bottom:12px;">
                <label>デザイン</label>
                <div class="layout-grid">
                    @foreach([
                        ['text',  'fa-align-left',       'テキストのみ'],
                        ['top',   'fa-image',            '画像縦'],
                        ['left',  'fa-table-columns',    '画像左'],
                        ['right', 'fa-object-ungroup',   '画像右'],
                        ['hero',  'fa-panorama',         'フルイメージ'],
                        ['card',  'fa-id-card',          'カード型'],
                    ] as [$val, $icon, $label])
                    <input type="radio" name="layout" id="le-{{ $item->id }}-{{ $val }}" class="layout-opt"
                           value="{{ $val }}" {{ ($item->layout ?? 'text') === $val ? 'checked' : '' }}>
                    <label class="layout-label" for="le-{{ $item->id }}-{{ $val }}">
                        <span class="layout-icon"><i class="fa-solid {{ $icon }}"></i></span>
                        <span class="layout-name">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- 現在の画像 --}}
            @if ($item->image_url)
            <div style="margin-bottom:10px;">
                <label style="font-size:0.82rem;color:#7a6a5a;font-weight:500;display:block;margin-bottom:6px;">現在の画像</label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <img src="{{ $item->image_url }}" style="height:60px;border-radius:4px;border:1px solid #e8d5b7;" alt="">
                    <label style="display:flex;align-items:center;gap:5px;font-size:0.82rem;cursor:pointer;">
                        <input type="checkbox" name="remove_image" value="1"> 画像を削除する
                    </label>
                </div>
            </div>
            @endif

            {{-- 新しい画像 --}}
            <div class="form-group" style="margin-bottom:12px;">
                <label>{{ $item->image_url ? '画像を差し替え' : '画像を追加' }}</label>
                <div class="img-upload-area">
                    <input type="file" name="image" accept="image/*"
                           onchange="previewImage(this,'previewEdit{{ $item->id }}','previewWrapEdit{{ $item->id }}')">
                    <div class="img-upload-area__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                    <p class="img-upload-area__text">クリックまたはドラッグ</p>
                </div>
                <div class="img-preview-wrap" id="previewWrapEdit{{ $item->id }}">
                    <img id="previewEdit{{ $item->id }}" class="img-preview" src="" alt="">
                </div>
            </div>

            <div style="display:flex;gap:8px;align-items:center;">
                <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                    表示する
                </label>
                <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:0.85rem;">保存</button>
                <button type="button" class="btn-secondary" onclick="toggleEdit({{ $item->id }})" style="padding:8px 14px;font-size:0.85rem;">キャンセル</button>
            </div>
        </form>
    </div>
    @endforeach
    @endif
</div>

<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function setTag(inputId, val, el) {
    document.getElementById(inputId).value = val;
    el.closest('.tag-presets').querySelectorAll('.tag-preset').forEach(e => e.classList.remove('sel'));
    el.classList.add('sel');
}
function previewImage(input, previewId, wrapId) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById(previewId).src = e.target.result;
        document.getElementById(wrapId).style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
}
function clearImage(input, previewId, wrapId) {
    input.value = '';
    document.getElementById(previewId).src = '';
    document.getElementById(wrapId).style.display = 'none';
}

// テキストのみのとき画像フィールドを非表示（追加フォーム）
document.querySelectorAll('input[name="layout"]').forEach(r => {
    if (!r.id.startsWith('la-add-')) return;
    r.addEventListener('change', () => {
        const imgField = document.getElementById('imgFieldAdd');
        if (imgField) imgField.style.display = r.value === 'text' ? 'none' : 'block';
    });
});
// 初期状態
(function() {
    const checked = document.querySelector('input[name="layout"][id^="la-add-"]:checked');
    const imgField = document.getElementById('imgFieldAdd');
    if (imgField && checked) imgField.style.display = checked.value === 'text' ? 'none' : 'block';
})();
</script>
@endsection
