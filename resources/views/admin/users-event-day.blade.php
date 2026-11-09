@extends('layouts.app')
@section('title', '参加日振り分け | Admin')

@push('styles')
<style>
.event-day-page { max-width: 1180px; }
.event-day-hero {
    display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; align-items: end;
    padding: 26px; border: 1px solid #eadfce; border-radius: 18px;
    background: linear-gradient(135deg, #fffdf8 0%, #f8f1e8 100%);
    box-shadow: 0 14px 34px rgba(72, 52, 37, .08); margin-bottom: 20px;
}
.event-day-kicker { margin: 0 0 8px; color: #b38b59; font-size: .78rem; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
.event-day-hero h1 { margin: 0 0 8px; font-size: clamp(1.55rem, 4vw, 2.2rem); color: #342820; letter-spacing: .04em; }
.event-day-hero p { margin: 0; color: #7e7064; line-height: 1.8; }
.event-day-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
.event-day-link, .event-day-primary {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 42px;
    padding: 10px 14px; border-radius: 999px; text-decoration: none; font-weight: 800; font-size: .9rem;
}
.event-day-link { border: 1px solid #d8c3a6; color: #7b5d35; background: rgba(255,255,255,.75); }
.event-day-primary { border: 0; background: #b38b59; color: #fff; cursor: pointer; }
.event-day-primary:disabled { opacity: .42; cursor: not-allowed; }
.event-day-stats { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
.event-day-stat { background: #fff; border: 1px solid #eee4d5; border-radius: 14px; padding: 14px 16px; box-shadow: 0 8px 24px rgba(54,40,28,.05); }
.event-day-stat span { display: block; color: #9b8a79; font-size: .78rem; font-weight: 800; margin-bottom: 6px; }
.event-day-stat strong { color: #342820; font-size: 1.45rem; }
.event-day-panel { background: #fff; border: 1px solid #eee4d5; border-radius: 18px; box-shadow: 0 12px 30px rgba(54,40,28,.06); overflow: hidden; }
.event-day-toolbar { display: grid; grid-template-columns: minmax(220px, 1fr) auto; gap: 14px; padding: 18px; border-bottom: 1px solid #f0e6d8; background: #fffaf3; }
.event-day-search { position: relative; }
.event-day-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #b7a48f; }
.event-day-search input { width: 100%; height: 46px; padding: 0 42px; border: 1px solid #decbb0; border-radius: 999px; background: #fff; font-size: .95rem; }
.event-day-search input:focus { outline: none; border-color: #b38b59; box-shadow: 0 0 0 3px rgba(179,139,89,.13); }
.event-day-filter-grid { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
.event-chip { border: 1px solid #decbb0; border-radius: 999px; padding: 9px 13px; background: #fff; color: #7b6a5c; font-weight: 800; cursor: pointer; }
.event-chip.active { background: #3c2f28; color: #fff; border-color: #3c2f28; }
.event-day-bulk { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; border-bottom: 1px solid #f0e6d8; }
.event-day-bulk__left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; color: #7b6a5c; }
.event-day-bulk strong { color: #342820; }
.event-day-bulk__buttons { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
.event-day-secondary { border: 1px solid #decbb0; background: #fff; color: #7b5d35; border-radius: 999px; padding: 9px 13px; font-weight: 800; cursor: pointer; }
.event-day-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 18px; }
.event-guest { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 14px; border: 1px solid #eee4d5; border-radius: 14px; background: #fff; }
.event-guest.is-hidden { display: none; }
.event-guest input { width: 20px; height: 20px; accent-color: #b38b59; }
.event-guest__name { display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; color: #342820; font-weight: 900; font-size: 1rem; }
.event-guest__username { color: #a49483; font-size: .78rem; font-weight: 800; }
.event-guest__meta { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 7px; }
.event-badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; padding: 5px 8px; font-size: .75rem; font-weight: 800; background: #f7f0e7; color: #7b6a5c; }
.event-badge.day1 { background: #ecf4ff; color: #27608f; }
.event-badge.day2 { background: #fff3dd; color: #8a5c18; }
.event-badge.no-email { background: #fff0f0; color: #a33b3b; }
.event-badge.email { background: #eef8ef; color: #367347; }
.event-guest__day { min-width: 54px; text-align: center; font-weight: 900; color: #4a382d; }
.event-empty { display: none; padding: 42px 18px; text-align: center; color: #9b8a79; }
.event-empty.visible { display: block; }
@media (max-width: 900px) {
    .event-day-hero, .event-day-toolbar { grid-template-columns: 1fr; }
    .event-day-actions, .event-day-filter-grid { justify-content: flex-start; }
    .event-day-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .event-day-list { grid-template-columns: 1fr; padding: 12px; }
    .event-day-bulk { align-items: stretch; flex-direction: column; }
    .event-day-bulk__buttons { justify-content: stretch; }
    .event-day-bulk__buttons button { flex: 1; }
}
</style>
@endpush

@section('content')
<div class="event-day-page">
    <div class="event-day-hero">
        <div>
            <p class="event-day-kicker">Guest Schedule</p>
            <h1>参加日振り分け</h1>
            <p>基本は全員2日目。1日目に来る人だけ選んでまとめて移動できます。メールアドレス未登録の人もここで分けて確認できます。</p>
        </div>
        <div class="event-day-actions">
            <a href="{{ route('admin.users') }}" class="event-day-link"><i class="fa-solid fa-user-pen"></i> ユーザー管理へ</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <div class="event-day-stats">
        <div class="event-day-stat"><span>全ゲスト</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="event-day-stat"><span>1日目</span><strong>{{ $stats['day1'] }}</strong></div>
        <div class="event-day-stat"><span>2日目</span><strong>{{ $stats['day2'] }}</strong></div>
        <div class="event-day-stat"><span>メールあり</span><strong>{{ $stats['with_email'] }}</strong></div>
        <div class="event-day-stat"><span>メールなし</span><strong>{{ $stats['without_email'] }}</strong></div>
    </div>

    <form method="POST" action="{{ route('admin.users.bulk-event-day') }}" id="eventDayBulkForm">
        @csrf
        @method('PATCH')
        <input type="hidden" name="event_day" id="eventDayTarget" value="day1">
        <input type="hidden" name="return_to" value="admin.users.event-day">

        <div class="event-day-panel">
            <div class="event-day-toolbar">
                <div class="event-day-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="eventDaySearch" placeholder="氏名・ふりがな・ユーザー名・メールで検索" autocomplete="off">
                </div>
                <div class="event-day-filter-grid" aria-label="絞り込み">
                    <button type="button" class="event-chip active" data-filter="day" data-value="all">全日程</button>
                    <button type="button" class="event-chip" data-filter="day" data-value="day1">1日目</button>
                    <button type="button" class="event-chip" data-filter="day" data-value="day2">2日目</button>
                    <button type="button" class="event-chip active" data-filter="email" data-value="all">メール全て</button>
                    <button type="button" class="event-chip" data-filter="email" data-value="yes">メールあり</button>
                    <button type="button" class="event-chip" data-filter="email" data-value="no">メールなし</button>
                </div>
            </div>

            <div class="event-day-bulk">
                <div class="event-day-bulk__left">
                    <label style="display:inline-flex;align-items:center;gap:8px;font-weight:800;color:#4a382d;">
                        <input type="checkbox" id="selectVisibleGuests" style="width:18px;height:18px;accent-color:#b38b59;">
                        表示中をすべて選択
                    </label>
                    <span><strong id="selectedCount">0</strong> 名選択中 / 表示 <strong id="visibleCount">{{ $users->count() }}</strong> 名</span>
                </div>
                <div class="event-day-bulk__buttons">
                    <button type="button" class="event-day-secondary" id="clearSelection">選択解除</button>
                    <button type="button" class="event-day-primary" id="moveDay1" data-day="day1" disabled><i class="fa-solid fa-calendar-day"></i> 1日目にする</button>
                    <button type="button" class="event-day-primary" id="moveDay2" data-day="day2" disabled><i class="fa-solid fa-calendar-check"></i> 2日目に戻す</button>
                </div>
            </div>

            <div class="event-day-list" id="eventDayList">
                @foreach ($users as $user)
                    @php
                        $p = $user->guestProfile;
                        $fullName = $p ? trim(($p->last_name ?? '') . ' ' . ($p->first_name ?? '')) : '';
                        $furigana = $p?->furigana() ?? '';
                        $day = $p?->event_day ?: 'day2';
                        $emailState = $user->email ? 'yes' : 'no';
                        $searchText = strtolower(trim($user->username . ' ' . $user->name . ' ' . $fullName . ' ' . $furigana . ' ' . ($user->email ?? '')));
                    @endphp
                    <label class="event-guest"
                           data-day="{{ $day }}"
                           data-email="{{ $emailState }}"
                           data-search="{{ e($searchText) }}">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="event-user-check">
                        <span>
                            <span class="event-guest__name">
                                {{ $fullName ?: $user->name ?: $user->username }}
                                <span class="event-guest__username">{{ $user->username }}</span>
                            </span>
                            <span class="event-guest__meta">
                                <span class="event-badge {{ $day }}"><i class="fa-solid fa-calendar-day"></i>{{ $day === 'day1' ? '1日目' : '2日目' }}</span>
                                @if ($user->email)
                                    <span class="event-badge email"><i class="fa-solid fa-envelope"></i>{{ $user->email }}</span>
                                @else
                                    <span class="event-badge no-email"><i class="fa-solid fa-envelope-open"></i>メールなし</span>
                                @endif
                                @if ($p?->participation)
                                    <span class="event-badge">{{ ['attending' => '出席', 'declining' => '欠席', 'pending' => '未回答'][$p->participation] ?? $p->participation }}</span>
                                @endif
                            </span>
                        </span>
                        <span class="event-guest__day">{{ $day === 'day1' ? 'DAY 1' : 'DAY 2' }}</span>
                    </label>
                @endforeach
            </div>
            <div class="event-empty" id="eventDayEmpty">条件に合うゲストがいません。</div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const search = document.getElementById('eventDaySearch');
    const rows = Array.from(document.querySelectorAll('.event-guest'));
    const visibleCount = document.getElementById('visibleCount');
    const selectedCount = document.getElementById('selectedCount');
    const empty = document.getElementById('eventDayEmpty');
    const selectVisible = document.getElementById('selectVisibleGuests');
    const clearSelection = document.getElementById('clearSelection');
    const target = document.getElementById('eventDayTarget');
    const form = document.getElementById('eventDayBulkForm');
    const submitButtons = [document.getElementById('moveDay1'), document.getElementById('moveDay2')];
    const filters = { day: 'all', email: 'all' };

    function visibleRows() {
        return rows.filter(row => !row.classList.contains('is-hidden'));
    }

    function updateCounts() {
        const visible = visibleRows();
        const checked = rows.filter(row => row.querySelector('.event-user-check').checked);
        visibleCount.textContent = visible.length;
        selectedCount.textContent = checked.length;
        empty.classList.toggle('visible', visible.length === 0);
        submitButtons.forEach(button => button.disabled = checked.length === 0);
        selectVisible.checked = visible.length > 0 && visible.every(row => row.querySelector('.event-user-check').checked);
        selectVisible.indeterminate = !selectVisible.checked && visible.some(row => row.querySelector('.event-user-check').checked);
    }

    function applyFilters() {
        const keyword = (search.value || '').trim().toLowerCase();
        rows.forEach(row => {
            const dayOk = filters.day === 'all' || row.dataset.day === filters.day;
            const emailOk = filters.email === 'all' || row.dataset.email === filters.email;
            const searchOk = !keyword || row.dataset.search.includes(keyword);
            row.classList.toggle('is-hidden', !(dayOk && emailOk && searchOk));
        });
        updateCounts();
    }

    document.querySelectorAll('.event-chip').forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            filters[filter] = button.dataset.value;
            document.querySelectorAll(`.event-chip[data-filter="${filter}"]`).forEach(item => item.classList.remove('active'));
            button.classList.add('active');
            applyFilters();
        });
    });

    search.addEventListener('input', applyFilters);
    rows.forEach(row => row.querySelector('.event-user-check').addEventListener('change', updateCounts));

    selectVisible.addEventListener('change', () => {
        visibleRows().forEach(row => row.querySelector('.event-user-check').checked = selectVisible.checked);
        updateCounts();
    });

    clearSelection.addEventListener('click', () => {
        rows.forEach(row => row.querySelector('.event-user-check').checked = false);
        updateCounts();
    });

    submitButtons.forEach(button => {
        button.addEventListener('click', () => {
            const count = rows.filter(row => row.querySelector('.event-user-check').checked).length;
            if (!count) return;
            target.value = button.dataset.day;
            const label = button.dataset.day === 'day1' ? '1日目' : '2日目';
            if (confirm(`${count}名を${label}に変更します。よろしいですか？`)) {
                form.submit();
            }
        });
    });

    applyFilters();
})();
</script>
@endpush
