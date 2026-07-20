@extends('layouts.app')
@section('title', '席次表 | ' . ($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? ''))

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/seating-guest.css') }}">
@endpush

@section('content')

@php
    $guestName = function ($user) {
        $p = $user->guestProfile;
        return $p ? trim($p->last_name . ' ' . $p->first_name) : $user->name;
    };
    $guestSide = fn($p) => match($p?->guest_side) { 'groom' => '新郎', 'bride' => '新婦', default => '' };
    $guestRel  = fn($p) => match($p?->relationship) {
        'friend' => '友人', 'family' => '親族', 'colleague' => '職場', 'other' => 'その他', default => ''
    };
    $tableCount = $tables->count();
    $seatCount = $tables->sum(fn($table) => $table->seats->count());
    $assignedCount = $tables->sum(fn($table) => $table->seats->filter(fn($seat) => $seat->assignment !== null)->count());
@endphp

<div class="gs-page">

    @if (!$isPublished)

        {{-- ── 未公開 ── --}}
        <div class="gs-empty">
            <div class="gs-empty__panel">
                <div class="gs-empty__icon"><i class="fa-regular fa-clock"></i></div>
                <h2 class="gs-empty__title">席次表は準備中です</h2>
                <p class="gs-empty__desc">席次が確定次第、ここにテーブル名とお名前が表示されます。</p>
            </div>
        </div>

    @else

        {{-- ── 自席バナー ── --}}
        @php
            $myTable = $myTableId ? $tables->firstWhere('id', $myTableId) : null;
        @endphp

        <header class="gs-hero">
            <div class="gs-hero__sparkle" aria-hidden="true"></div>
            <p class="gs-hero__eyebrow">Seating Chart</p>
            <div class="gs-hero__frame">
                <div class="gs-hero__frame-inner">
                    @if ($setting?->groom_name || $setting?->bride_name)
                    <p class="gs-hero__role">新郎　　　　新婦</p>
                    @endif
                    <h1 class="gs-hero__title">
                        <span>{{ $setting?->groom_name ?? '　' }}</span>
                        <span class="gs-hero__amp">&amp;</span>
                        <span>{{ $setting?->bride_name ?? '　' }}</span>
                    </h1>
                </div>
            </div>
            <p class="gs-hero__meta">
                @if ($setting?->ceremony_date)
                    {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y年n月j日') }}
                    @if ($setting?->venue_name) ・{{ $setting->venue_name }}@endif
                @elseif ($setting?->venue_name)
                    {{ $setting->venue_name }}
                @endif
            </p>
        </header>

        @if ($myTable)
        <section class="gs-focus" id="gsBanner">
            <div class="gs-focus__inner">
                <span class="gs-focus__star">&#10022;</span>
                <div class="gs-focus__copy">
                    <p class="gs-focus__label">あなたの席</p>
                    <p class="gs-focus__value">{{ $myTable->name }}</p>
                </div>
            </div>
            <button class="gs-focus__btn" id="scrollToMyTable" type="button">席を見る</button>
        </section>
        @else
        <section class="gs-focus gs-focus--pending">
            <div class="gs-focus__inner">
                <span class="gs-focus__star">&#10022;</span>
                <div class="gs-focus__copy">
                    <p class="gs-focus__label">あなたの席</p>
                    <p class="gs-focus__value">未配置</p>
                </div>
            </div>
            <p class="gs-focus__note">席はまだ確定していません。確定次第ご案内します。</p>
        </section>
        @endif

        {{-- ── テーブル一覧 ── --}}
        <section class="gs-sheet">
            <main class="gs-columns" id="gsGrid">
                @foreach ($tables as $table)
                @php
                    $isMyTable = $myTableId && $table->id === $myTableId;
                    $occupiedSeats = $table->seats->filter(fn($s) => $s->assignment !== null)->values();
                @endphp
                <article class="gs-col {{ $isMyTable ? 'gs-col--mine' : '' }}"
                     id="gst-{{ $table->id }}"
                     style="animation-delay: {{ $loop->index * 26 }}ms">

                    <div class="gs-col__badge">
                        <span class="gs-col__badge-moon">&#9789;</span>
                        <span class="gs-col__badge-name">{{ $table->name }}</span>
                    </div>

                    <div class="gs-col__list">
                        @forelse ($occupiedSeats as $seat)
                        @php
                            $assignedUser = $seat->assignment->user;
                            $p            = $assignedUser->guestProfile;
                            $isMe         = $mySeat && $seat->id === $mySeat->id;
                            $label        = trim($guestSide($p) . $guestRel($p));
                        @endphp
                        <div class="gs-guest {{ $isMe ? 'is-mine' : '' }}">
                            @if ($label)
                            <p class="gs-guest__label">{{ $label }}</p>
                            @endif
                            <p class="gs-guest__name">
                                @if ($isMe)<span class="gs-guest__mark">&#10022;</span>@endif
                                {{ $guestName($assignedUser) }}
                            </p>
                        </div>
                        @empty
                        <p class="gs-col__empty">まだ配置がありません</p>
                        @endforelse
                    </div>
                </article>
                @endforeach
            </main>
        </section>

        <p class="gs-footnote">ご来場を心よりお待ちしております</p>

    @endif

</div>

@endsection

@push('scripts')
<script>
(function () {
    const myTable = document.querySelector('.gs-col--mine');

    document.getElementById('scrollToMyTable')?.addEventListener('click', function () {
        myTable?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    if (myTable) {
        setTimeout(function () {
            myTable.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
})();
</script>
@endpush
