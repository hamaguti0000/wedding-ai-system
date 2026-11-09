@extends('layouts.app')
@section('title', 'ゲストブック管理 | Admin')

@push('styles')
<style>
/* ── 検索・フィルター ── */
.gb-toolbar {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    padding: 14px 16px; margin-bottom: 14px;
    background: #fff; border-radius: 10px; border: 1px solid #f0ebe3;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.gb-search-wrap { position: relative; flex: 1; min-width: 180px; max-width: 300px; }
.gb-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #c0b0a0; font-size: 0.85rem; pointer-events: none; }
.gb-search { width: 100%; padding: 8px 30px 8px 32px; border: 1px solid #e0d0bc; border-radius: 6px; font-size: 0.85rem; background: #fffdf9; box-sizing: border-box; }
.gb-search:focus { border-color: #b38b59; outline: none; }
.gb-clear { display: none; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #c0b0a0; font-size: 1rem; line-height: 1; }
.gb-clear.visible { display: block; }
.gb-filter-btn { padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; border: 1px solid #e8d5b7; color: #b38b59; background: #fef9f0; cursor: pointer; transition: background 0.15s; white-space: nowrap; }
.gb-filter-btn.active, .gb-filter-btn:hover { background: #b38b59; color: #fff; border-color: #b38b59; }
.gb-sort-select { padding: 7px 10px; border: 1px solid #e0d0bc; border-radius: 6px; font-size: 0.82rem; color: #7a6a5a; background: #fffdf9; cursor: pointer; }
.gb-sort-select:focus { border-color: #b38b59; outline: none; }
.gb-result-count { font-size: 0.82rem; color: #999; margin-bottom: 10px; }
.gb-no-results { display: none; text-align: center; padding: 40px 20px; color: #aaa; }
.gb-no-results.visible { display: block; }

.gb-msg-item {
    background: #fff; border-radius: 10px; padding: 16px 20px;
    margin-bottom: 10px; border: 1px solid #f0ebe3;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: flex; gap: 14px; align-items: flex-start;
}
.gb-msg-item.hidden-msg { opacity: 0.55; }
.gb-msg-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #b38b59, #d4a870);
    color: #fff; font-size: 1rem; font-family: 'Playfair Display', serif;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden; border: 2px solid #f0e4d0;
}
.gb-msg-avatar img { width: 100%; height: 100%; object-fit: cover; }
.gb-msg-body { flex: 1; min-width: 0; }
.gb-msg-header { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; flex-wrap: wrap; }
.gb-msg-name { font-size: 0.9rem; font-weight: 600; color: #3d2f25; }
.gb-msg-date { font-size: 0.74rem; color: #b0a090; }
.gb-msg-sticker { font-size: 1.2rem; }
.gb-msg-text { font-size: 0.88rem; color: #6b5b4e; white-space: pre-wrap; line-height: 1.7; }
.gb-msg-actions { display: flex; gap: 6px; margin-top: 8px; align-items: center; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-comment-dots" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>ゲストブック管理</h1>
    <p class="page-desc">ゲストが投稿したメッセージを管理します。非公開にしたり削除できます。</p>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    @if ($messages->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">💌</div>
        <p class="empty-state__title">まだメッセージがありません</p>
    </div>
    @else
    {{-- ツールバー --}}
    <div class="gb-toolbar">
        <div class="gb-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="gbSearch" class="gb-search" placeholder="名前・メッセージで検索" autocomplete="off">
            <button type="button" id="gbClear" class="gb-clear" aria-label="クリア">✕</button>
        </div>
        <button class="gb-filter-btn active" data-pub="all">すべて</button>
        <button class="gb-filter-btn" data-pub="1">公開</button>
        <button class="gb-filter-btn" data-pub="0">非公開</button>
        <select id="gbSort" class="gb-sort-select">
            <option value="desc">新しい順</option>
            <option value="asc">古い順</option>
        </select>
    </div>
    <div class="gb-result-count" id="gbCount">
        <strong>{{ $messages->count() }}</strong>件（公開: {{ $messages->where('is_public', true)->count() }}件）
    </div>
    <div id="gbList">
    @foreach ($messages as $msg)
    @php
        $user = $msg->user;
        $avatarType = $user?->avatarType() ?? 'initial';
        $imgUrl  = $user?->avatarImageUrl();
        $emoji   = $user?->avatar_emoji;
        $bgColor = $user?->avatarBackgroundColor() ?? 'linear-gradient(135deg,#b38b59,#d4a870)';
        $borderColor = $user?->avatarBorderColor() ?? '#f0e4d0';
    @endphp
    <div class="gb-msg-item {{ $msg->is_public ? '' : 'hidden-msg' }}"
         data-public="{{ $msg->is_public ? '1' : '0' }}"
         data-name="{{ strtolower($user?->name ?? '') }}"
         data-username="{{ strtolower($user?->username ?? '') }}"
         data-message="{{ strtolower($msg->message) }}"
         data-timestamp="{{ $msg->created_at->timestamp }}">
        <div class="gb-msg-avatar" style="border-color:{{ $borderColor }};{{ $avatarType !== 'emoji' ? "background:{$bgColor};" : '' }}">
            @if ($avatarType === 'photo' && $imgUrl)
                <img src="{{ $imgUrl }}" alt="">
            @elseif ($avatarType === 'emoji' && $emoji)
                <span style="font-size:1rem;line-height:1;background:{{ $bgColor }};width:100%;height:100%;display:flex;align-items:center;justify-content:center;">{{ $emoji }}</span>
            @else
                {{ $user?->avatarInitial() ?? '?' }}
            @endif
        </div>
        <div class="gb-msg-body">
            <div class="gb-msg-header">
                <span class="gb-msg-name">{{ $user?->name ?? '削除済みユーザー' }}</span>
                <span class="gb-msg-date">{{ $msg->created_at->format('Y.m.d H:i') }}</span>
                @if ($msg->sticker)<span class="gb-msg-sticker">{{ $msg->sticker }}</span>@endif
                @if (!$msg->is_public)
                <span style="font-size:0.7rem;color:#aaa;border:1px solid #ddd;padding:1px 6px;border-radius:3px;">非公開</span>
                @endif
            </div>
            <p class="gb-msg-text">{{ $msg->message }}</p>
            <div class="gb-msg-actions">
                <form method="POST" action="{{ route('admin.guestbook.update', $msg->id) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="is_public" value="{{ $msg->is_public ? '0' : '1' }}">
                    <button class="btn-sm btn-sm-pw">
                        {{ $msg->is_public ? '非公開にする' : '公開する' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.guestbook.destroy', $msg->id) }}" onsubmit="return confirm('削除しますか？')">
                    @csrf @method('DELETE')
                    <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
    </div>{{-- #gbList --}}
    <div class="gb-no-results" id="gbNoResults">
        <div style="font-size:2rem;margin-bottom:8px;">🔍</div>
        <p style="font-weight:600;color:#888;">該当するメッセージが見つかりません</p>
    </div>
    @endif
</div>

<script>
(function () {
    const state  = { q: '', pub: 'all', sort: 'desc' };
    const list   = document.getElementById('gbList');
    const srch   = document.getElementById('gbSearch');
    const clrBtn = document.getElementById('gbClear');
    const countEl= document.getElementById('gbCount');
    const noRes  = document.getElementById('gbNoResults');
    if (!list) return;

    const getItems = () => Array.from(list.querySelectorAll('.gb-msg-item'));

    function applyAll() {
        const items = getItems();
        let visible = 0;
        items.forEach(item => {
            const d = item.dataset;
            let show = true;
            if (state.q && !d.name.includes(state.q) && !d.username.includes(state.q) && !d.message.includes(state.q)) show = false;
            if (state.pub !== 'all' && d.public !== state.pub) show = false;
            item.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        // ソート
        items.filter(i => i.style.display !== 'none')
             .sort((a, b) => {
                 const ta = parseInt(a.dataset.timestamp) || 0;
                 const tb = parseInt(b.dataset.timestamp) || 0;
                 return state.sort === 'desc' ? tb - ta : ta - tb;
             })
             .forEach(item => list.appendChild(item));
        if (countEl) countEl.innerHTML = `<strong>${visible}</strong>件`;
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
    document.querySelectorAll('.gb-filter-btn[data-pub]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.gb-filter-btn[data-pub]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            state.pub = btn.dataset.pub;
            applyAll();
        });
    });
    document.getElementById('gbSort')?.addEventListener('change', e => {
        state.sort = e.target.value;
        applyAll();
    });
    applyAll();
})();
</script>
@endsection
