@extends('layouts.app')
@section('title', 'ギャラリー管理 | Admin')

@push('styles')
<style>
.upload-zone {
    border: 2px dashed #e8d5b7; border-radius: 10px;
    padding: 32px 20px; text-align: center; background: #fffdf9;
    cursor: pointer; transition: border-color 0.15s, background 0.15s;
    position: relative;
}
.upload-zone:hover, .upload-zone.drag-over { border-color: #b38b59; background: #fef9f0; }
.upload-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.upload-zone__icon { font-size: 2rem; color: #c0b0a0; margin-bottom: 8px; }
.upload-zone__text { font-size: 0.88rem; color: #9b8573; }
.upload-zone__sub  { font-size: 0.74rem; color: #b0a090; margin-top: 4px; }

.photo-previews { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.photo-preview { width: 80px; height: 80px; border-radius: 6px; object-fit: cover; border: 1px solid #e8d5b7; }

.gl-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 14px;
    margin-top: 8px;
}
.gl-admin-item {
    background: #fff; border-radius: 10px; overflow: hidden;
    border: 1px solid #f0ebe3; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex; flex-direction: column; min-width: 0;
}
.gl-admin-item.inactive { opacity: 0.5; }
.gl-admin-item__img { width: 100%; height: 130px; object-fit: cover; display: block; flex: 0 0 auto; }
.gl-admin-item__body { padding: 10px 12px; }
.gl-admin-item__caption { font-size: 0.78rem; color: #7a6a5a; margin: 0 0 8px; line-height: 1.5; min-height: 1.5em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.gl-admin-item__actions { display: flex; gap: 4px; flex-wrap: wrap; }

/* ゲスト投稿承認セクション */
.pending-section {
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;
    padding: 20px 24px; margin-bottom: 32px;
}
.pending-section h3 { font-size: 0.95rem; font-weight: 600; color: #92400e; margin-bottom: 4px; }
.pending-section .section-desc { font-size: 0.8rem; color: #a16207; margin-bottom: 16px; }
.pending-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 14px;
}
.pending-item {
    background: #fff; border-radius: 10px; overflow: hidden;
    border: 1px solid #fde68a; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.pending-item__img { width: 100%; height: 140px; object-fit: cover; display: block; }
.pending-item__body { padding: 10px 12px; }
.pending-item__uploader { font-size: 0.76rem; color: #9b8573; margin-bottom: 4px; }
.pending-item__caption  { font-size: 0.8rem; color: #7a6a5a; line-height: 1.5; margin-bottom: 8px; min-height: 1.2em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
.pending-item__actions  { display: flex; gap: 6px; }
.btn-approve { background: #16a34a; color: #fff; border: none; border-radius: 6px; padding: 6px 14px; font-size: 0.78rem; cursor: pointer; transition: background .15s; }
.btn-approve:hover { background: #15803d; }
.btn-reject  { background: #fff; color: #dc2626; border: 1px solid #fca5a5; border-radius: 6px; padding: 5px 12px; font-size: 0.78rem; cursor: pointer; transition: all .15s; }
.btn-reject:hover { background: #fef2f2; }

/* ── 検索・フィルター ── */
.gl-toolbar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 12px 14px; margin-bottom: 10px;
    background: #fff; border-radius: 10px; border: 1px solid #f0ebe3;
}
.gl-search-wrap { position: relative; flex: 1; min-width: 160px; max-width: 260px; }
.gl-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #c0b0a0; font-size: 0.85rem; pointer-events: none; }
.gl-search { width: 100%; padding: 8px 28px 8px 30px; border: 1px solid #e0d0bc; border-radius: 6px; font-size: 0.85rem; background: #fffdf9; box-sizing: border-box; }
.gl-search:focus { border-color: #b38b59; outline: none; }
.gl-clear { display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #c0b0a0; font-size: 1rem; line-height: 1; }
.gl-clear.visible { display: block; }
.gl-filter-btn { padding: 5px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 500; border: 1px solid #e8d5b7; color: #b38b59; background: #fef9f0; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.gl-filter-btn.active, .gl-filter-btn:hover { background: #b38b59; color: #fff; border-color: #b38b59; }
.gl-result-count { font-size: 0.82rem; color: #999; margin-bottom: 8px; }
.gl-no-results { display: none; text-align: center; padding: 40px 20px; color: #aaa; }
.gl-no-results.visible { display: block; }

/* 承認済み/却下済みゲスト投稿 */
.guest-history-section { margin-bottom: 32px; }
.guest-history-section summary { cursor: pointer; font-size: 0.85rem; font-weight: 600; color: #7a6a5a; padding: 10px 0; user-select: none; }
.gl-admin-item.rejected { opacity: 0.4; }

/* 人物タグ付け */

.gl-tag-panel__head {
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
    margin-bottom: 8px; color: #5d4635; font-size: 0.82rem;
}
.gl-tag-selected-count { color: #b38b59; margin-left: 6px; font-size: 0.74rem; }
.gl-tag-clear {
    border: 1px solid #e8d5b7; background: #fff; color: #9b8573;
    border-radius: 999px; padding: 3px 9px; font-size: 0.72rem; cursor: pointer;
}
.gl-tag-selected { min-height: 28px; max-height: 82px; overflow-y: auto; margin-bottom: 8px; }
.gl-tag-selected:empty::before { content: 'まだ選択されていません'; color: #c0b0a0; font-size: 0.76rem; }
.gl-tag-panel__list label span { display: grid; gap: 1px; }
.gl-tag-panel__list label strong { font-weight: 600; color: #5d4635; }
.gl-tag-panel__list label small { color: #a99888; font-size: 0.68rem; }
.gl-tag-panel__actions { display: flex; align-items: center; gap: 8px; }
.gl-tag-status { color: #9b8573; font-size: 0.76rem; }
.gl-tag-status.is-ok { color: #15803d; }
.gl-tag-status.is-error { color: #dc2626; }
.gl-admin-item__tags {
    min-height: 24px; max-height: 78px; overflow-y: auto; overscroll-behavior: contain;
    padding: 0 12px 10px; font-size: 0.72rem; color: #9b8573; line-height: 1.6;
    background: #fff; flex: 0 0 auto;
}
.gl-tag-chip {
    display: inline-flex; align-items: center; max-width: calc(100% - 8px);
    background: #fef9f0; border: 1px solid #e8d5b7; color: #b38b59; border-radius: 20px;
    padding: 1px 8px; margin: 2px 3px 0 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    vertical-align: top;
}
.gl-tag-panel { display: none; padding: 10px 12px; background: #fef9f0; border-top: 1px solid #e8d5b7; }
.gl-tag-search { width: 100%; box-sizing: border-box; padding: 6px 10px; margin-bottom: 8px; border: 1px solid #e0d0bc; border-radius: 6px; font-size: 0.8rem; background: #fffdf9; }
.gl-tag-search:focus { border-color: #b38b59; outline: none; }
.gl-tag-panel__list { max-height: 160px; overflow-y: auto; border: 1px solid #e0d0bc; border-radius: 6px; padding: 6px 8px; background: #fff; margin-bottom: 8px; }
.gl-tag-panel__list label { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; padding: 3px 0; cursor: pointer; }
.gl-tag-panel__list label.is-hidden { display: none; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-images" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>ギャラリー管理</h1>
    <p class="page-desc">ゲストに公開する写真を管理します。複数枚まとめてアップロードできます。</p>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    {{-- ゲスト投稿承認待ち --}}
    @if ($pending->isNotEmpty())
    <div class="pending-section">
        <h3>📥 ゲスト投稿 — 承認待ち（{{ $pending->count() }}件）</h3>
        <p class="section-desc">ゲストから届いた写真です。承認するとギャラリーに追加されます。</p>
        <div class="pending-grid">
            @foreach ($pending as $photo)
            <div class="pending-item">
                <img src="{{ $photo->url }}" alt="" class="pending-item__img">
                <div class="pending-item__body">
                    <p class="pending-item__uploader">
                        <i class="fa-solid fa-user" style="font-size:0.7rem;"></i>
                        {{ $photo->uploader?->name ?? '不明' }}
                        <span style="color:#c0b0a0;margin-left:4px;">{{ $photo->created_at->format('m/d') }}</span>
                    </p>
                    <p class="pending-item__caption">{{ $photo->caption ?: '（コメントなし）' }}</p>
                    <div class="pending-item__actions">
                        <form method="POST" action="{{ route('admin.gallery.approve', $photo->id) }}">
                            @csrf
                            <button type="submit" class="btn-approve" title="承認してギャラリーに追加">
                                <i class="fa-solid fa-check"></i> 承認
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.gallery.reject', $photo->id) }}"
                              onsubmit="return confirm('却下しますか？')">
                            @csrf
                            <button type="submit" class="btn-reject">却下</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- アップロードフォーム --}}
    <div class="add-card">
        <h3>写真をアップロード</h3>
        <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" id="galleryForm">
            @csrf
            <div class="upload-zone" id="uploadZone">
                <input type="file" name="photos[]" multiple accept="image/*"
                       onchange="previewPhotos(this)">
                <div class="upload-zone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                <p class="upload-zone__text">クリックまたはドラッグ＆ドロップ</p>
                <p class="upload-zone__sub">JPG / PNG / WebP・1枚10MB以内・最大20枚</p>
            </div>
            <div class="photo-previews" id="photoPreviews"></div>

            <div id="captionFields" style="display:none;margin-top:14px;">
                <p style="font-size:0.8rem;color:#9b8573;margin-bottom:8px;">キャプション（任意）</p>
                <div id="captionInputs"></div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top:16px;" id="uploadBtn" disabled>
                <i class="fa-solid fa-upload"></i> アップロードする
            </button>
        </form>
    </div>

    {{-- 公式写真一覧 --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
        <span style="font-size:0.88rem;font-weight:600;color:#3d2f25;">公式写真</span>
    </div>

    @if ($photos->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">🖼️</div>
        <p class="empty-state__title">まだ写真がありません</p>
        <p class="empty-state__desc">上のフォームからアップロードしてください</p>
    </div>
    @else
    {{-- ツールバー --}}
    <div class="gl-toolbar">
        <div class="gl-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="glSearch" class="gl-search" placeholder="キャプションで検索" autocomplete="off">
            <button type="button" id="glClear" class="gl-clear" aria-label="クリア">✕</button>
        </div>
        <button class="gl-filter-btn active" data-active="all">すべて</button>
        <button class="gl-filter-btn" data-active="1">表示中</button>
        <button class="gl-filter-btn" data-active="0">非表示</button>
    </div>
    <div class="gl-result-count" id="glCount"><strong>{{ $photos->count() }}</strong>枚</div>
    <div class="gl-admin-grid" id="galleryGrid">
        @foreach ($photos as $photo)
        <div class="gl-admin-item {{ $photo->is_active ? '' : 'inactive' }}"
             data-caption="{{ strtolower($photo->caption ?? '') }}"
             data-active="{{ $photo->is_active ? '1' : '0' }}"
             data-id="{{ $photo->id }}">
            <img src="{{ $photo->url }}" alt="" class="gl-admin-item__img">
            <div class="gl-admin-item__body">
                <p class="gl-admin-item__caption" title="{{ $photo->caption }}">{{ $photo->caption ?: '—' }}</p>
                <div class="gl-admin-item__actions">
                    <form method="POST" action="{{ route('admin.gallery.move-up', $photo->id) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="上へ"><i class="fa-solid fa-chevron-up"></i></button></form>
                    <form method="POST" action="{{ route('admin.gallery.move-down', $photo->id) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" title="下へ"><i class="fa-solid fa-chevron-down"></i></button></form>
                    <button class="btn-sm btn-sm-pw" onclick="toggleEdit({{ $photo->id }})" title="編集"><i class="fa-solid fa-pen"></i></button>
                    <button class="btn-sm btn-sm-pw" onclick="toggleTag({{ $photo->id }})" title="人物タグ"><i class="fa-solid fa-user-tag"></i></button>
                    <form method="POST" action="{{ route('admin.gallery.destroy', $photo->id) }}" onsubmit="return confirm('削除しますか？')">@csrf @method('DELETE')<button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button></form>
                </div>
            </div>
            @if ($photo->taggedUsers->isNotEmpty())
            <div class="gl-admin-item__tags">
                @foreach ($photo->taggedUsers as $tagged)
                <span class="gl-tag-chip">{{ $tagged->guestProfile?->fullName() ?: $tagged->name }}</span>
                @endforeach
            </div>
            @endif
            <div id="edit-{{ $photo->id }}" style="display:none;padding:10px 12px;background:#fef9f0;border-top:1px solid #e8d5b7;">
                <form method="POST" action="{{ route('admin.gallery.update', $photo->id) }}">
                    @csrf @method('PATCH')
                    <div class="form-group" style="margin-bottom:8px;">
                        <label style="font-size:0.76rem;">キャプション</label>
                        <input type="text" name="caption" value="{{ $photo->caption }}" maxlength="200" placeholder="写真の説明">
                    </div>
                    <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;margin-bottom:8px;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1" {{ $photo->is_active ? 'checked' : '' }}>
                        表示する
                    </label>
                    <button type="submit" class="btn-primary" style="padding:6px 16px;font-size:0.82rem;">保存</button>
                </form>
            </div>
            @include('admin.partials.gallery-tag-panel', ['photo' => $photo, 'taggableGuests' => $taggableGuests])
        </div>
        @endforeach
    </div>{{-- #galleryGrid --}}
    <div class="gl-no-results" id="glNoResults">
        <div style="font-size:2rem;margin-bottom:8px;">🔍</div>
        <p style="font-weight:600;color:#888;">該当する写真が見つかりません</p>
    </div>
    @endif

    {{-- 承認済み・却下済みゲスト投稿（折りたたみ） --}}
    @if ($guestApproved->isNotEmpty())
    <details class="guest-history-section" style="margin-top:32px;">
        <summary>ゲスト投稿の承認・却下済み（{{ $guestApproved->count() }}件）</summary>
        <div class="gl-admin-grid" style="margin-top:12px;">
            @foreach ($guestApproved as $photo)
            <div class="gl-admin-item {{ $photo->status === 'rejected' ? 'rejected' : '' }}">
                <img src="{{ $photo->url }}" alt="" class="gl-admin-item__img">
                <div class="gl-admin-item__body">
                    <p class="gl-admin-item__caption" title="{{ $photo->caption }}">
                        {{ $photo->uploader?->name ?? '不明' }} ·
                        <span style="color:{{ $photo->status==='approved' ? '#16a34a' : '#dc2626' }};">
                            {{ $photo->statusLabel() }}
                        </span>
                    </p>
                    <div class="gl-admin-item__actions">
                        @if ($photo->status === 'approved')
                        <button class="btn-sm btn-sm-pw" onclick="toggleTag({{ $photo->id }})" title="人物タグ"><i class="fa-solid fa-user-tag"></i></button>
                        @endif
                        <form method="POST" action="{{ route('admin.gallery.destroy', $photo->id) }}" onsubmit="return confirm('削除しますか？')">
                            @csrf @method('DELETE')
                            <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
                @if ($photo->taggedUsers->isNotEmpty())
                <div class="gl-admin-item__tags">
                    @foreach ($photo->taggedUsers as $tagged)
                    <span class="gl-tag-chip">{{ $tagged->guestProfile?->fullName() ?: $tagged->name }}</span>
                    @endforeach
                </div>
                @endif
                @if ($photo->status === 'approved')
                @include('admin.partials.gallery-tag-panel', ['photo' => $photo, 'taggableGuests' => $taggableGuests])
                @endif
            </div>
            @endforeach
        </div>
    </details>
    @endif
</div>

<script>
// ── ギャラリー検索・フィルター ────────────────────────────
(function () {
    const state  = { q: '', active: 'all' };
    const grid   = document.getElementById('galleryGrid');
    const srch   = document.getElementById('glSearch');
    const clrBtn = document.getElementById('glClear');
    const countEl= document.getElementById('glCount');
    const noRes  = document.getElementById('glNoResults');
    if (!grid) return;

    const getItems = () => Array.from(grid.querySelectorAll('.gl-admin-item'));

    function applyAll() {
        const items = getItems();
        let visible = 0;
        items.forEach(item => {
            const d = item.dataset;
            let show = true;
            if (state.q && !d.caption.includes(state.q)) show = false;
            if (state.active !== 'all' && d.active !== state.active) show = false;
            item.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (countEl) countEl.innerHTML = `<strong>${visible}</strong>枚`;
        if (noRes)   noRes.classList.toggle('visible', visible === 0);
    }

    srch?.addEventListener('input', () => {
        state.q = srch.value.toLowerCase().trim();
        clrBtn?.classList.toggle('visible', state.q.length > 0);
        applyAll();
    });
    clrBtn?.addEventListener('click', () => {
        srch.value = ''; state.q = '';
        clrBtn.classList.remove('visible');
        srch.focus(); applyAll();
    });
    document.querySelectorAll('.gl-filter-btn[data-active]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.gl-filter-btn[data-active]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.active = btn.dataset.active;
            applyAll();
        });
    });
    applyAll();
})();

function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleTag(id) {
    const el = document.getElementById('tag-' + id);
    if (!el) return;
    el.style.display = el.style.display === 'none' || !el.style.display ? 'block' : 'none';
    el.querySelector('.gl-tag-search')?.focus();
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, ch => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[ch]));
}

function galleryTagNames(form) {
    return Array.from(form.querySelectorAll('.gl-tag-panel__list input[type="checkbox"]:checked')).map(input => {
        const label = input.closest('label');
        return { id: input.value, name: label?.dataset.label || label?.textContent.trim() || '' };
    });
}

function renderSelectedTags(form) {
    const selected = form.querySelector('.gl-tag-selected');
    const count = form.querySelector('.gl-tag-selected-count');
    const names = galleryTagNames(form);
    if (selected) {
        selected.innerHTML = names.map(tag => `<span class="gl-tag-chip" data-user-id="${escapeHtml(tag.id)}">${escapeHtml(tag.name)}</span>`).join('');
    }
    if (count) count.textContent = `${names.length}名選択中`;
}

function updateCardTags(photoId, tags) {
    const card = document.querySelector(`.gl-admin-item[data-id="${photoId}"]`) || document.querySelector(`#tag-${photoId}`)?.closest('.gl-admin-item');
    if (!card) return;
    let holder = card.querySelector('.gl-admin-item__tags');
    const panel = card.querySelector(`#tag-${photoId}`);
    if (!holder) {
        holder = document.createElement('div');
        holder.className = 'gl-admin-item__tags';
        card.insertBefore(holder, panel || null);
    }
    holder.innerHTML = tags.length
        ? tags.map(tag => `<span class="gl-tag-chip">${escapeHtml(tag.name)}</span>`).join('')
        : '';
}

document.querySelectorAll('.gl-tag-form').forEach(form => {
    const search = form.querySelector('.gl-tag-search');
    const list = form.querySelector('.gl-tag-panel__list');
    const status = form.querySelector('.gl-tag-status');

    search?.addEventListener('input', () => {
        const q = search.value.toLowerCase().trim();
        list.querySelectorAll('label[data-name]').forEach(label => {
            label.classList.toggle('is-hidden', q.length > 0 && !label.dataset.name.includes(q));
        });
    });

    form.querySelectorAll('.gl-tag-panel__list input[type="checkbox"]').forEach(input => {
        input.addEventListener('change', () => {
            renderSelectedTags(form);
            if (status) {
                status.textContent = '未保存の変更があります';
                status.className = 'gl-tag-status';
            }
        });
    });

    form.querySelector('.gl-tag-clear')?.addEventListener('click', () => {
        form.querySelectorAll('.gl-tag-panel__list input[type="checkbox"]').forEach(input => input.checked = false);
        renderSelectedTags(form);
        if (status) {
            status.textContent = '未保存の変更があります';
            status.className = 'gl-tag-status';
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('.gl-tag-save');
        const data = new FormData(form);
        if (status) {
            status.textContent = '保存中...';
            status.className = 'gl-tag-status';
        }
        if (button) button.disabled = true;

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: data,
            });
            const json = await res.json();
            if (!res.ok || !json.success) throw new Error(json.message || '保存に失敗しました');
            updateCardTags(json.photo_id, json.tags || []);
            if (status) {
                status.textContent = '保存しました';
                status.className = 'gl-tag-status is-ok';
            }
        } catch (error) {
            if (status) {
                status.textContent = error.message || '保存に失敗しました';
                status.className = 'gl-tag-status is-error';
            }
        } finally {
            if (button) button.disabled = false;
        }
    });

    renderSelectedTags(form);
});

function filterTagList(input) {
    const q = input.value.toLowerCase().trim();
    const list = input.closest('form').querySelector('.gl-tag-panel__list');
    list.querySelectorAll('label[data-name]').forEach(label => {
        label.classList.toggle('is-hidden', q.length > 0 && !label.dataset.name.includes(q));
    });
}
function previewPhotos(input) {
    const previews = document.getElementById('photoPreviews');
    const captionFields = document.getElementById('captionFields');
    const captionInputs = document.getElementById('captionInputs');
    const btn = document.getElementById('uploadBtn');
    previews.innerHTML = '';
    captionInputs.innerHTML = '';
    if (!input.files.length) { btn.disabled = true; captionFields.style.display = 'none'; return; }
    Array.from(input.files).forEach((f, i) => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'photo-preview';
            previews.appendChild(img);
        };
        reader.readAsDataURL(f);
        const div = document.createElement('div');
        div.style.cssText = 'margin-bottom:8px;';
        div.innerHTML = `<label style="font-size:0.76rem;color:#9b8573;display:block;margin-bottom:3px;">${f.name}</label><input type="text" name="captions[]" placeholder="キャプション（任意）" style="width:100%;padding:7px 10px;border:1px solid #e0d0bc;border-radius:5px;font-size:0.85rem;">`;
        captionInputs.appendChild(div);
    });
    btn.disabled = false;
    captionFields.style.display = 'block';
}
</script>
@endsection
