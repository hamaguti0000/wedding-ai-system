@extends('layouts.app')
@section('title', '参加者一覧 | Wedding')

@push('styles')
<style>
main { padding: 0; text-align: initial; }

.gl-banner {
    position: relative; height: 32vh; min-height: 200px;
    overflow: hidden; display: flex; align-items: center; justify-content: center; text-align: center;
    padding-top: 60px; box-sizing: border-box;
}
.gl-banner__img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: brightness(0.38) saturate(0.7); }
.gl-banner__overlay { position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(20,12,4,0.1), rgba(20,12,4,0.55)); }
.gl-banner__text { position: relative; z-index: 2; color: #fff; padding: 0 20px; }
.gl-banner__eyebrow { display: block; font-size: 0.6rem; letter-spacing: 5px; text-transform: uppercase; color: rgba(255,255,255,0.6); margin-bottom: 10px; font-family: 'Noto Sans JP', sans-serif; }
.gl-banner__title { font-family: 'Playfair Display', serif; font-size: clamp(1.6rem, 5vw, 2.6rem); font-weight: 400; letter-spacing: 2px; margin: 0; }

.gl-wrap { max-width: 720px; margin: 0 auto; padding: 44px 20px 80px; }
.gl-intro { text-align: center; margin-bottom: 32px; }
.gl-section-en { display: block; font-size: 0.65rem; letter-spacing: 5px; text-transform: uppercase; color: #b38b59; margin-bottom: 6px; font-family: 'Noto Sans JP', sans-serif; }
.gl-section-ja { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 400; color: #3d2f25; margin: 0 0 10px; }
.gl-rule { width: 40px; height: 1px; background: #b38b59; margin: 0 auto; }

/* ── 検索・絞り込み ── */
.ppl-toolbar { position: sticky; top: 56px; z-index: 5; background: #fdfaf6; padding: 10px 0 14px; }
.ppl-search-wrap { position: relative; margin-bottom: 10px; }
.ppl-search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #c0b0a0; font-size: 0.85rem; pointer-events: none; }
.ppl-search {
    width: 100%; box-sizing: border-box; padding: 11px 36px; border: 1px solid #e8d5b7; border-radius: 24px;
    font-size: 0.9rem; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.03);
}
.ppl-search:focus { border-color: #b38b59; outline: none; }
.ppl-clear { display: none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #c0b0a0; font-size: 1rem; line-height: 1; }
.ppl-clear.visible { display: block; }

.ppl-tabs { display: flex; gap: 8px; }
.ppl-tab {
    flex: 1; padding: 7px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;
    border: 1px solid #e8d5b7; color: #b38b59; background: #fef9f0; cursor: pointer; text-align: center;
    transition: background 0.15s, color 0.15s;
}
.ppl-tab.active { background: #b38b59; color: #fff; border-color: #b38b59; }

/* ── 一覧 ── */
.ppl-group { margin-top: 22px; }
.ppl-group__label {
    font-size: 0.72rem; letter-spacing: 2px; color: #b38b59; font-weight: 600;
    padding: 0 4px 8px; border-bottom: 1px solid #f0ebe3; margin-bottom: 4px;
}
.ppl-list { background: #fff; border: 1px solid #f0ebe3; border-radius: 14px; overflow: hidden; }
.ppl-row {
    display: flex; align-items: center; gap: 14px; padding: 13px 16px;
    text-decoration: none; border-bottom: 1px solid #f5f0e8; transition: background 0.12s;
}
.ppl-row:last-child { border-bottom: none; }
.ppl-row:hover, .ppl-row:active { background: #fef9f0; }
.ppl-row.is-hidden { display: none; }

.ppl-avatar {
    width: 46px; height: 46px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Playfair Display', serif; font-size: 1.05rem; color: #b38b59;
    background: linear-gradient(135deg, #b38b59 0%, #d4a870 100%);
    border-style: solid;
}
.ppl-avatar img { width: 100%; height: 100%; object-fit: cover; }

.ppl-info { flex: 1; min-width: 0; }
.ppl-name { font-size: 0.92rem; color: #3d2f25; font-weight: 500; line-height: 1.4; }
.ppl-meta { font-size: 0.72rem; color: #b0a090; margin-top: 2px; }
.ppl-side-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; margin-right: 5px; vertical-align: middle; }
.ppl-side-dot--groom { background: #7a9cc6; }
.ppl-side-dot--bride { background: #d98ca6; }

.ppl-chevron { color: #d8c9b0; font-size: 0.85rem; flex-shrink: 0; }

.ppl-no-results { display: none; text-align: center; padding: 40px 20px; color: #b0a090; }
.ppl-no-results.visible { display: block; }
.ppl-no-results i { font-size: 2rem; opacity: 0.4; display: block; margin-bottom: 10px; }

.gl-empty { text-align: center; padding: 60px 20px; color: #c0b0a0; }
.gl-empty i { font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 16px; }

@media (min-width: 768px) {
    .gl-banner { padding-top: 80px; }
}
</style>
@endpush

@section('content')
<section class="gl-banner">
    <img src="{{ ($bannerImage?->url ?? asset('img/チャペル.jpg')) }}" alt="" class="gl-banner__img">
    <div class="gl-banner__overlay"></div>
    <div class="gl-banner__text">
        <span class="gl-banner__eyebrow">People · 参加者一覧</span>
        <h1 class="gl-banner__title">参加者一覧</h1>
    </div>
</section>

<div class="gl-wrap">
    <div class="gl-intro">
        <span class="gl-section-en">People</span>
        <h2 class="gl-section-ja">みんなの写真を見る</h2>
        <div class="gl-rule"></div>
        <p style="font-size:0.85rem;color:#9b8573;margin-top:14px;">気になる人をタップすると、その人が写っている写真だけを見返せます</p>
    </div>

    @if ($people->isEmpty())
    <div class="gl-empty">
        <i class="fa-regular fa-user"></i>
        <p>参加者情報はまだありません</p>
    </div>
    @else
    @php
        $sideLabels = ['groom' => '新郎', 'bride' => '新婦', 'other' => 'ゲスト'];
        $relLabels  = ['family' => '親族', 'friend' => '友人', 'colleague' => '職場', 'other' => 'その他'];

        $groupKey = function ($p) {
            $profile = $p->guestProfile;
            $side = $profile?->guest_side ?? 'other';
            $rel  = $profile?->relationship ?? 'other';
            return "{$side}_{$rel}";
        };

        $groups = $people->groupBy($groupKey);

        // 新郎→新婦→側不明、それぞれ 親族→友人→職場→その他 の順に並べる
        $orderedKeys = [];
        foreach (['groom', 'bride', 'other'] as $side) {
            foreach (['family', 'friend', 'colleague', 'other'] as $rel) {
                $orderedKeys[] = "{$side}_{$rel}";
            }
        }
        $groups = collect($orderedKeys)
            ->filter(fn($key) => $groups->has($key))
            ->mapWithKeys(fn($key) => [$key => $groups->get($key)]);

        $groupLabel = fn($side, $rel) => $sideLabels[$side] . $relLabels[$rel];
        $groomCount = $people->filter(fn($p) => ($p->guestProfile?->guest_side ?? 'other') === 'groom')->count();
        $brideCount = $people->filter(fn($p) => ($p->guestProfile?->guest_side ?? 'other') === 'bride')->count();
    @endphp

    <div class="ppl-toolbar">
        <div class="ppl-search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" id="pplSearch" class="ppl-search" placeholder="名前で検索" autocomplete="off">
            <button type="button" id="pplClear" class="ppl-clear" aria-label="クリア">✕</button>
        </div>
        <div class="ppl-tabs" id="pplTabs">
            <button class="ppl-tab active" data-side="all">すべて（{{ $people->count() }}）</button>
            @if ($groomCount > 0)
            <button class="ppl-tab" data-side="groom">新郎側（{{ $groomCount }}）</button>
            @endif
            @if ($brideCount > 0)
            <button class="ppl-tab" data-side="bride">新婦側（{{ $brideCount }}）</button>
            @endif
        </div>
    </div>

    <div id="pplGroups">
        @foreach ($groups as $key => $members)
        @php [$side, $rel] = explode('_', $key, 2); @endphp
        <div class="ppl-group" data-side-group="{{ $side }}">
            <div class="ppl-group__label">{{ $groupLabel($side, $rel) }} · {{ $members->count() }}名</div>
            <div class="ppl-list">
                @foreach ($members as $person)
                @php $profile = $person->guestProfile; @endphp
                <a href="{{ route('people.show', $person) }}" class="ppl-row"
                   data-side="{{ $side }}"
                   data-name="{{ strtolower(($profile?->fullName() ?: $person->name) . ' ' . $profile?->furigana()) }}">
                    <div class="ppl-avatar"
                         style="border-width:{{ $person->avatarBorderWidth() }}px; border-color:{{ $person->avatarBorderColor() }}; @if ($person->avatarType() === 'emoji') background: {{ $person->avatarBackgroundColor() }}; @endif">
                        @if ($person->avatarType() === 'photo' && $person->avatarImageUrl())
                            <img src="{{ $person->avatarImageUrl() }}" alt="">
                        @elseif ($person->avatarType() === 'emoji' && $person->avatar_emoji)
                            <span>{{ $person->avatar_emoji }}</span>
                        @else
                            {{ $person->avatarInitial() }}
                        @endif
                    </div>
                    <div class="ppl-info">
                        <div class="ppl-name">{{ $profile?->fullName() ?: $person->name }}</div>
                        @if ($profile?->relationship)
                        <div class="ppl-meta">{{ $profile->relationshipLabel() }}</div>
                        @endif
                    </div>
                    <i class="fa-solid fa-chevron-right ppl-chevron"></i>
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="ppl-no-results" id="pplNoResults">
        <i class="fa-solid fa-magnifying-glass"></i>
        <p>該当する人が見つかりません</p>
    </div>
    @endif
</div>

<script>
(function () {
    const state = { q: '', side: 'all' };
    const rows = Array.from(document.querySelectorAll('.ppl-row'));
    const groups = Array.from(document.querySelectorAll('.ppl-group'));
    const searchInput = document.getElementById('pplSearch');
    const clearBtn = document.getElementById('pplClear');
    const noResults = document.getElementById('pplNoResults');
    if (!rows.length) return;

    function applyAll() {
        let visible = 0;
        rows.forEach(row => {
            const matchesSide = state.side === 'all' || row.dataset.side === state.side;
            const matchesQuery = !state.q || row.dataset.name.includes(state.q);
            const show = matchesSide && matchesQuery;
            row.classList.toggle('is-hidden', !show);
            if (show) visible++;
        });
        groups.forEach(group => {
            const hasVisible = group.querySelector('.ppl-row:not(.is-hidden)') !== null;
            group.style.display = hasVisible ? '' : 'none';
        });
        if (noResults) noResults.classList.toggle('visible', visible === 0);
    }

    searchInput?.addEventListener('input', () => {
        state.q = searchInput.value.toLowerCase().trim();
        clearBtn?.classList.toggle('visible', state.q.length > 0);
        applyAll();
    });
    clearBtn?.addEventListener('click', () => {
        searchInput.value = ''; state.q = '';
        clearBtn.classList.remove('visible');
        searchInput.focus(); applyAll();
    });
    document.querySelectorAll('.ppl-tab[data-side]').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.ppl-tab[data-side]').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            state.side = tab.dataset.side;
            applyAll();
        });
    });
})();
</script>
@endsection
