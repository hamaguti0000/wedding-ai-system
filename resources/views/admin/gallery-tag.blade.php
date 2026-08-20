@extends('layouts.app')
@section('title', '写真のタグ付け | Admin')

@push('styles')
<style>
/* ── レイアウト ───────────────────────────────── */
.tag-wrap { max-width: 1100px; margin: 0 auto; padding: 0 14px 120px; }

.tag-bar {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 12px 0 14px;
}
.tag-bar__back {
    display: inline-flex; align-items: center; gap: 6px;
    color: #8a642e; text-decoration: none; font-size: .84rem; font-weight: 700;
}
.tag-bar__back:hover { text-decoration: underline; }
.tag-progress { text-align: right; font-size: .78rem; color: #9b8573; line-height: 1.5; }
.tag-progress strong { color: #3d2f25; font-size: .92rem; }
.tag-progress em { font-style: normal; color: #b8791f; font-weight: 800; }

.tag-nav { display: flex; gap: 8px; }
.tag-nav a, .tag-nav span {
    min-width: 44px; min-height: 40px; padding: 0 12px;
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    border: 1px solid #e8d5b7; border-radius: 10px; background: #fffdf9;
    color: #8a642e; text-decoration: none; font-size: .82rem; font-weight: 700;
}
.tag-nav span { opacity: .35; }
.tag-nav a:hover { background: #fef3e3; }

/* ── 本体 ───────────────────────────────────── */
.tag-main { display: grid; grid-template-columns: 1fr; gap: 16px; }

.tag-photo {
    background: #201a14; border-radius: 16px; overflow: hidden;
    display: grid; place-items: center; position: relative;
}
.tag-photo img { width: 100%; max-height: 46vh; object-fit: contain; display: block; }
.tag-photo__meta {
    position: absolute; left: 10px; top: 10px;
    background: rgba(255,255,255,.92); color: #7a5b32;
    border-radius: 999px; padding: 5px 12px; font-size: .76rem; font-weight: 800;
}

.tag-panel {
    background: #fff; border: 1px solid #eee4d8; border-radius: 16px;
    box-shadow: 0 12px 30px rgba(61,47,37,.07); overflow: hidden;
}
.tag-panel__head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 14px 16px; border-bottom: 1px solid #f3ebe0;
}
.tag-panel__head strong { color: #3d2f25; font-size: .92rem; }
.tag-panel__count { display: block; margin-top: 2px; color: #9b8573; font-size: .76rem; }
.tag-clear {
    border: 1px solid #e8d5b7; background: #fffdf9; color: #a3714a;
    border-radius: 999px; padding: 7px 14px; font-size: .78rem; font-weight: 700; cursor: pointer;
}
.tag-clear:hover { background: #fef3e3; }

/* 選択済みチップ */
.tag-chips { display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 16px 0; }
.tag-chips:empty { display: none; }
.tag-chip {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f6ead8; color: #7a5b32; border: 1px solid #e8d5b7;
    border-radius: 999px; padding: 6px 10px 6px 12px; font-size: .8rem; font-weight: 700;
    cursor: pointer;
}
.tag-chip--group { background: #e8f0f8; border-color: #c9dcec; color: #3f6285; }
.tag-chip i { opacity: .55; font-size: .74rem; }
.tag-chip:hover i { opacity: 1; }

/* 検索 */
.tag-search-wrap { position: relative; padding: 12px 16px; }
.tag-search-wrap > i {
    position: absolute; left: 28px; top: 50%; transform: translateY(-50%);
    color: #c0b0a0; font-size: .85rem; pointer-events: none;
}
.tag-search {
    width: 100%; box-sizing: border-box; padding: 12px 14px 12px 38px;
    border: 1px solid #e0d0bc; border-radius: 12px; background: #fffdf9;
    font-size: 16px; /* iOSで拡大しないように */
}
.tag-search:focus { border-color: #b38b59; outline: none; }

/* リスト */
.tag-list { max-height: 44vh; overflow-y: auto; padding: 0 8px 8px; -webkit-overflow-scrolling: touch; }
.tag-list__label {
    position: sticky; top: 0; z-index: 1;
    padding: 10px 8px 6px; background: #fff;
    font-size: .72rem; font-weight: 800; letter-spacing: 1px; color: #b38b59;
}
.tag-option {
    display: flex; align-items: center; gap: 11px;
    padding: 11px 10px; border-radius: 10px; cursor: pointer;
    border: 1px solid transparent;
}
.tag-option:hover { background: #fffaf2; }
.tag-option.is-hidden { display: none; }
.tag-option input { width: 20px; height: 20px; accent-color: #b38b59; flex: 0 0 auto; }
.tag-option strong { display: block; color: #3d2f25; font-size: .88rem; font-weight: 600; }
.tag-option small { display: block; color: #b0a090; font-size: .72rem; margin-top: 1px; }
.tag-option:has(input:checked) { background: #fdf4e6; border-color: #ecd9bb; }
.tag-option--group:has(input:checked) { background: #eef4fa; border-color: #cfe0ee; }

.tag-empty { padding: 22px 16px; text-align: center; color: #b7a897; font-size: .82rem; }
.tag-empty.is-hidden { display: none; }

/* ── 保存バー（画面下に固定） ─────────────────── */
.tag-actions {
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 50;
    display: flex; gap: 10px; align-items: center;
    padding: 12px 14px calc(12px + env(safe-area-inset-bottom));
    background: rgba(255,253,249,.97); border-top: 1px solid #eadccd;
    box-shadow: 0 -8px 24px rgba(61,47,37,.08);
}
.tag-actions__inner { max-width: 1100px; margin: 0 auto; width: 100%; display: flex; gap: 10px; }
.tag-btn {
    flex: 1; min-height: 50px; border-radius: 12px; border: 1px solid #e8d5b7;
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    background: #fff; color: #8a642e; font-size: .88rem; font-weight: 800; cursor: pointer;
    text-decoration: none;
}
.tag-btn--primary { background: #b38b59; border-color: #b38b59; color: #fff; flex: 1.4; }
.tag-btn--primary:hover { background: #a37c4c; }

@media (min-width: 900px) {
    .tag-main { grid-template-columns: 1fr 1fr; align-items: start; }
    .tag-photo { position: sticky; top: 84px; }
    .tag-photo img { max-height: 62vh; }
    .tag-list { max-height: 40vh; }
}
</style>
@endpush

@section('content')
@php
    $taggedIds = $photo->taggedUsers->pluck('id')->all();
    $taggedGroupIds = $photo->taggedGroups->pluck('id')->all();
    $taggedGroupNames = $photo->taggedGroups->map(fn($g) => $g->galleryDisplayName())->unique()->values()->all();
    $selectedCount = count($taggedIds) + count(array_unique($taggedGroupNames));
@endphp

<div class="tag-wrap">

    <div class="tag-bar">
        <a href="{{ route('admin.gallery') }}" class="tag-bar__back">
            <i class="fa-solid fa-arrow-left"></i> 写真一覧
        </a>
        <div class="tag-progress">
            <strong>{{ $position }} / {{ $totalCount }}</strong>枚目
            @if ($untaggedCount > 0)
            <br>未タグ <em>{{ $untaggedCount }}</em> 枚
            @else
            <br>すべてタグ付け済み 🎉
            @endif
        </div>
    </div>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:14px;">{{ session('success') }}</div>
    @endif

    <div class="tag-main">
        <div class="tag-photo">
            <span class="tag-photo__meta">#{{ $position }}</span>
            <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? '写真' }}">
        </div>

        <form method="POST" action="{{ route('admin.gallery.tag', $photo->id) }}" id="tagForm">
            @csrf
            <input type="hidden" name="next_photo_id" id="nextPhotoId" value="">
            <input type="hidden" name="after_save" id="afterSave" value="">

            <div class="tag-panel">
                <div class="tag-panel__head">
                    <div>
                        <strong>写っている人を選ぶ</strong>
                        <span class="tag-panel__count"><span id="tagCount">{{ $selectedCount }}</span>件を選択中</span>
                    </div>
                    <button type="button" class="tag-clear" id="tagClear">全解除</button>
                </div>

                <div class="tag-chips" id="tagChips"></div>

                <div class="tag-search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" class="tag-search" id="tagSearch"
                           placeholder="名前・ふりがなで検索" autocomplete="off">
                </div>

                <div class="tag-list" id="tagList">
                    @if ($taggableGroups->isNotEmpty())
                    <div class="tag-list__label">グループ</div>
                    @foreach ($taggableGroups as $group)
                    @php $groupName = $group->galleryDisplayName(); @endphp
                    <label class="tag-option tag-option--group"
                           data-name="{{ strtolower($groupName . ' ' . $group->id) }}"
                           data-label="{{ $groupName }}" data-kind="group">
                        <input type="checkbox" name="group_ids[]" value="{{ $group->id }}"
                               {{ in_array($group->id, $taggedGroupIds, true) || in_array($groupName, $taggedGroupNames, true) ? 'checked' : '' }}>
                        <span><strong>{{ $groupName }}</strong><small>グループ全員に届きます</small></span>
                    </label>
                    @endforeach
                    @endif

                    <div class="tag-list__label">ゲスト</div>
                    @forelse ($taggableGuests as $guest)
                    @php
                        $guestName = $guest->guestProfile?->fullName() ?: $guest->name;
                        $furigana = $guest->guestProfile?->furigana() ?? '';
                    @endphp
                    <label class="tag-option"
                           data-name="{{ strtolower($guestName . ' ' . $furigana . ' ' . ($guest->username ?? '')) }}"
                           data-label="{{ $guestName }}" data-kind="user">
                        <input type="checkbox" name="user_ids[]" value="{{ $guest->id }}"
                               {{ in_array($guest->id, $taggedIds, true) ? 'checked' : '' }}>
                        <span>
                            <strong>{{ $guestName }}</strong>
                            @if ($furigana)<small>{{ $furigana }}</small>@endif
                        </span>
                    </label>
                    @empty
                    <p class="tag-empty">ゲストが登録されていません</p>
                    @endforelse

                    <p class="tag-empty is-hidden" id="tagNoResults">該当する人が見つかりません</p>
                </div>
            </div>

            <div class="tag-nav" style="margin-top:14px;">
                @if ($prevPhoto)
                <a href="{{ route('admin.gallery.tag.edit', $prevPhoto->id) }}"><i class="fa-solid fa-chevron-left"></i> 前の写真</a>
                @else
                <span><i class="fa-solid fa-chevron-left"></i> 前の写真</span>
                @endif
                @if ($nextPhoto)
                <a href="{{ route('admin.gallery.tag.edit', $nextPhoto->id) }}">次の写真 <i class="fa-solid fa-chevron-right"></i></a>
                @else
                <span>次の写真 <i class="fa-solid fa-chevron-right"></i></span>
                @endif
            </div>

            <div class="tag-actions">
                <div class="tag-actions__inner">
                    <button type="submit" class="tag-btn" id="saveAndList">
                        <i class="fa-solid fa-check"></i> 保存して一覧へ
                    </button>
                    @if ($nextUntagged)
                    <button type="submit" class="tag-btn tag-btn--primary" id="saveAndNext"
                            data-next="{{ $nextUntagged->id }}">
                        保存して次の未タグへ <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    @elseif ($nextPhoto)
                    <button type="submit" class="tag-btn tag-btn--primary" id="saveAndNext"
                            data-next="{{ $nextPhoto->id }}">
                        保存して次へ <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const list      = document.getElementById('tagList');
    const chipsEl   = document.getElementById('tagChips');
    const countEl   = document.getElementById('tagCount');
    const searchEl  = document.getElementById('tagSearch');
    const noResults = document.getElementById('tagNoResults');
    const options   = Array.from(list.querySelectorAll('.tag-option'));

    function renderChips() {
        const checked = options.filter(o => o.querySelector('input').checked);
        countEl.textContent = checked.length;
        chipsEl.innerHTML = '';
        checked.forEach(opt => {
            const chip = document.createElement('span');
            chip.className = 'tag-chip' + (opt.dataset.kind === 'group' ? ' tag-chip--group' : '');
            chip.textContent = opt.dataset.label;
            const x = document.createElement('i');
            x.className = 'fa-solid fa-xmark';
            chip.appendChild(x);
            // チップをタップしたらその選択を外す
            chip.addEventListener('click', () => {
                opt.querySelector('input').checked = false;
                renderChips();
            });
            chipsEl.appendChild(chip);
        });
    }

    function applySearch() {
        const q = searchEl.value.toLowerCase().trim();
        let visible = 0;
        options.forEach(opt => {
            const show = !q || opt.dataset.name.includes(q);
            opt.classList.toggle('is-hidden', !show);
            if (show) visible++;
        });
        // 検索中は見出しを隠して結果だけ見せる
        list.querySelectorAll('.tag-list__label').forEach(el => {
            el.style.display = q ? 'none' : '';
        });
        noResults.classList.toggle('is-hidden', visible > 0);
    }

    list.addEventListener('change', e => {
        if (e.target.matches('input[type=checkbox]')) renderChips();
    });
    searchEl.addEventListener('input', applySearch);

    document.getElementById('tagClear').addEventListener('click', () => {
        options.forEach(o => { o.querySelector('input').checked = false; });
        renderChips();
    });

    // 押したボタンによって保存後の遷移先を変える
    document.getElementById('saveAndList')?.addEventListener('click', () => {
        document.getElementById('afterSave').value = 'index';
        document.getElementById('nextPhotoId').value = '';
    });
    document.getElementById('saveAndNext')?.addEventListener('click', e => {
        document.getElementById('nextPhotoId').value = e.currentTarget.dataset.next;
        document.getElementById('afterSave').value = '';
    });

    renderChips();
})();
</script>
@endsection
