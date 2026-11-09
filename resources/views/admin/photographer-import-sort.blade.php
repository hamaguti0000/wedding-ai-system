@extends('layouts.app')
@section('title', 'カメラマン写真仕分け | Admin')

@push('styles')
<style>
.ps-wrap { max-width: 1180px; margin: 0 auto; padding: 24px 14px 70px; }
.ps-toolbar { position: sticky; top: 72px; z-index: 20; background: rgba(255,253,249,.95); backdrop-filter: blur(12px); border:1px solid #eadbc8; border-radius:18px; padding:14px; box-shadow:0 12px 28px rgba(61,47,37,.08); margin-bottom:16px; }
.ps-head { display:flex; justify-content:space-between; gap:14px; align-items:flex-start; margin-bottom:12px; }
.ps-title { margin:0; color:#2f261f; font-size:1.22rem; }
.ps-sub { margin:4px 0 0; color:#7f7165; font-size:.82rem; line-height:1.6; }
.ps-tabs, .ps-actions { display:flex; flex-wrap:wrap; gap:8px; align-items:center; }
.ps-tab, .ps-btn { border-radius:999px; padding:9px 13px; border:1px solid #dfcbb2; background:#fff; color:#73593d; font-weight:900; text-decoration:none; cursor:pointer; }
.ps-tab.active { background:#b38b59; color:#fff; border-color:#b38b59; }
.ps-btn.accept { background:#2f7d4f; border-color:#2f7d4f; color:#fff; }
.ps-btn.reject { background:#fff; border-color:#e2a0a0; color:#b42318; }
.ps-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap:12px; }
.ps-card { position:relative; border:1px solid #eadbc8; border-radius:14px; background:#fff; overflow:hidden; box-shadow:0 8px 22px rgba(61,47,37,.07); }
.ps-card input[type=checkbox] { position:absolute; top:8px; left:8px; width:24px; height:24px; z-index:2; accent-color:#b38b59; }
.ps-img { width:100%; aspect-ratio:1; object-fit:cover; display:block; background:#eee5da; }
.ps-body { padding:9px; }
.ps-name { display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#4b3b30; font-size:.74rem; font-weight:800; }
.ps-status { display:inline-flex; margin-top:6px; border-radius:999px; padding:4px 8px; font-size:.68rem; font-weight:900; }
.ps-status.pending { background:#fff7d8; color:#8b6509; }
.ps-status.accepted { background:#eaf8ef; color:#237944; }
.ps-status.rejected { background:#fff0f0; color:#bd3429; }
.ps-row { display:grid; grid-template-columns:1fr 1fr; gap:7px; margin-top:8px; }
.ps-mini { border:1px solid #e4d3bd; background:#fffaf4; color:#73593d; border-radius:9px; padding:7px 4px; font-size:.72rem; font-weight:900; cursor:pointer; }
.ps-mini.reject { border-color:#edc5c5; background:#fffafa; color:#b42318; }
.ps-empty { padding:36px 16px; text-align:center; border:1px dashed #dfcbb2; border-radius:16px; color:#89796a; background:#fffdf9; }
.ps-pages { margin-top:18px; display:flex; flex-direction:column; gap:10px; align-items:center; color:#77675b; font-size:.86rem; }
.ps-pager { display:flex; flex-wrap:wrap; justify-content:center; gap:6px; width:100%; }
.ps-page { min-width:38px; min-height:38px; display:inline-flex; align-items:center; justify-content:center; border:1px solid #dfcbb2; border-radius:999px; background:#fff; color:#73593d; font-weight:900; text-decoration:none; line-height:1; }
.ps-page.current { background:#b38b59; border-color:#b38b59; color:#fff; }
.ps-page.disabled { color:#c8b9a9; background:#f8f3ed; }
.ps-page.nav { min-width:88px; padding:0 14px; }
.ps-page-info { text-align:center; }
.pi-alert { border-radius:14px; padding:12px 14px; margin-bottom:14px; font-weight:800; }
.pi-alert.success { background:#edf8f0; color:#247346; border:1px solid #ccebd5; }
@media (max-width: 760px) {
  .ps-toolbar { top: 64px; border-radius:14px; }
  .ps-head { display:block; }
  .ps-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap:10px; }
  .ps-actions .ps-btn { flex:1; text-align:center; }
}
</style>
@endpush

@section('content')
<div class="ps-wrap">
    @if (session('success')) <div class="pi-alert success">{{ session('success') }}</div> @endif

    <form method="POST" action="{{ route('admin.gallery.imports.bulk', $batch) }}" id="bulkForm">
        @csrf
        <div class="ps-toolbar">
            <div class="ps-head">
                <div>
                    <h1 class="ps-title">{{ $batch->name }}</h1>
                    <p class="ps-sub">未仕分け {{ $batch->pending_count }}枚 / 公開 {{ $batch->accepted_count }}枚 / 除外 {{ $batch->rejected_count }}枚</p>
                </div>
                <a class="ps-tab" href="{{ route('admin.gallery.imports') }}"><i class="fa-solid fa-arrow-left"></i> 取り込みへ</a>
            </div>
            <div class="ps-tabs" style="margin-bottom:10px">
                <a class="ps-tab @class(['active' => $status === 'pending'])" href="{{ route('admin.gallery.imports.sort', [$batch, 'status' => 'pending']) }}">未仕分け</a>
                <a class="ps-tab @class(['active' => $status === 'accepted'])" href="{{ route('admin.gallery.imports.sort', [$batch, 'status' => 'accepted']) }}">公開</a>
                <a class="ps-tab @class(['active' => $status === 'rejected'])" href="{{ route('admin.gallery.imports.sort', [$batch, 'status' => 'rejected']) }}">除外</a>
                <a class="ps-tab @class(['active' => $status === 'all'])" href="{{ route('admin.gallery.imports.sort', [$batch, 'status' => 'all']) }}">すべて</a>
            </div>
            <div class="ps-actions">
                <button type="button" class="ps-btn" data-check-all>表示中を全選択</button>
                <button type="submit" name="decision" value="accept" class="ps-btn accept">選択を公開</button>
                <button type="submit" name="decision" value="reject" class="ps-btn reject">選択を除外</button>
            </div>
        </div>

        @if ($items->count())
            <div class="ps-grid">
                @foreach ($items as $item)
                    <article class="ps-card" id="item-{{ $item->id }}">
                        <input type="checkbox" name="item_ids[]" value="{{ $item->id }}" aria-label="{{ $item->original_name }}を選択">
                        <img class="ps-img" src="{{ $item->url }}" alt="">
                        <div class="ps-body">
                            <span class="ps-name" title="{{ $item->original_name }}">{{ $item->original_name }}</span>
                            <span class="ps-status {{ $item->status }}">
                                @switch($item->status)
                                    @case('accepted') 公開 @break
                                    @case('rejected') 除外 @break
                                    @default 未仕分け
                                @endswitch
                            </span>
                            <div class="ps-row">
                                <button type="button" class="ps-mini" data-decision="accept" data-url="{{ route('admin.gallery.imports.items.decide', [$batch, $item]) }}">公開</button>
                                <button type="button" class="ps-mini reject" data-decision="reject" data-url="{{ route('admin.gallery.imports.items.decide', [$batch, $item]) }}">除外</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="ps-pages" aria-label="ページ移動">
                <div class="ps-pager">
                    @if ($items->onFirstPage())
                        <span class="ps-page nav disabled">前へ</span>
                    @else
                        <a class="ps-page nav" href="{{ $items->previousPageUrl() }}">前へ</a>
                    @endif

                    @foreach ($items->getUrlRange(1, $items->lastPage()) as $page => $url)
                        @if ($page === 1 || $page === $items->lastPage() || abs($page - $items->currentPage()) <= 2)
                            @if ($page === $items->currentPage())
                                <span class="ps-page current">{{ $page }}</span>
                            @else
                                <a class="ps-page" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @elseif (abs($page - $items->currentPage()) === 3)
                            <span class="ps-page disabled">...</span>
                        @endif
                    @endforeach

                    @if ($items->hasMorePages())
                        <a class="ps-page nav" href="{{ $items->nextPageUrl() }}">次へ</a>
                    @else
                        <span class="ps-page nav disabled">次へ</span>
                    @endif
                </div>
                <div class="ps-page-info">{{ $items->firstItem() }}〜{{ $items->lastItem() }}枚目 / 全{{ $items->total() }}枚</div>
            </div>
        @else
            <div class="ps-empty">この条件の写真はありません。</div>
        @endif
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    document.querySelector('[data-check-all]')?.addEventListener('click', () => {
        const boxes = Array.from(document.querySelectorAll('input[name="item_ids[]"]'));
        const shouldCheck = boxes.some((box) => !box.checked);
        boxes.forEach((box) => box.checked = shouldCheck);
    });

    document.querySelectorAll('[data-decision]').forEach((button) => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            try {
                const response = await fetch(button.dataset.url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ decision: button.dataset.decision }),
                });
                if (!response.ok) throw new Error('failed');
                const json = await response.json();
                const card = button.closest('.ps-card');
                const badge = card.querySelector('.ps-status');
                badge.className = 'ps-status ' + json.status;
                badge.textContent = json.status === 'accepted' ? '公開' : '除外';
                card.querySelector('input[type=checkbox]').checked = false;
            } catch (e) {
                alert('更新に失敗しました。再読み込みしてもう一度試してください。');
            } finally {
                button.disabled = false;
            }
        });
    });
})();
</script>
@endpush
