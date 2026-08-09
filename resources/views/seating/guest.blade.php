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
    $coupleNames = trim(($setting?->groom_name ?? '') . ' & ' . ($setting?->bride_name ?? ''));
@endphp

<div class="gs-page">

    @if (!$isPublished)

        <div class="gs-empty">
            <div class="gs-empty__panel">
                <div class="gs-empty__icon"><i class="fa-regular fa-clock"></i></div>
                <h2 class="gs-empty__title">席次表は準備中です</h2>
                <p class="gs-empty__desc">席次が確定次第、ここにテーブル名とお名前が表示されます。</p>
            </div>
        </div>

    @else

        @php
            $myTable = $myTableId ? $tables->firstWhere('id', $myTableId) : null;
        @endphp

        <section class="gs-paper-shell" aria-label="結婚式席次表">
            <div class="gs-paper">
                <span class="gs-corner gs-corner--tl" aria-hidden="true"></span>
                <span class="gs-corner gs-corner--tr" aria-hidden="true"></span>
                <span class="gs-corner gs-corner--bl" aria-hidden="true"></span>
                <span class="gs-corner gs-corner--br" aria-hidden="true"></span>

                <header class="gs-hero">
                    <div class="gs-hero__family">
                        <span>{{ $setting?->groom_name ? mb_substr($setting->groom_name, 0, 1) : 'K' }}</span>
                        <span>{{ $setting?->bride_name ? mb_substr($setting->bride_name, 0, 1) : 'M' }}</span>
                        <small>Wedding Reception Seating Chart</small>
                    </div>

                    <div class="gs-hero__center">
                        <p class="gs-hero__eyebrow">Seating Chart</p>
                        <h1 class="gs-hero__title">
                            <span>{{ $setting?->groom_name ?? '新郎' }}</span>
                            <span class="gs-hero__amp">&amp;</span>
                            <span>{{ $setting?->bride_name ?? '新婦' }}</span>
                        </h1>
                        @if ($coupleNames)
                        <p class="gs-hero__script">{{ $coupleNames }}</p>
                        @endif
                    </div>

                    <p class="gs-hero__meta">
                        @if ($setting?->ceremony_date)
                            {{ \Carbon\Carbon::parse($setting->ceremony_date)->format('Y.n.j') }}
                        @endif
                        @if ($setting?->venue_name)
                            <span>於 {{ $setting->venue_name }}</span>
                        @endif
                    </p>
                </header>

                @if ($myTable)
                <section class="gs-focus" id="gsBanner">
                    <div>
                        <p class="gs-focus__label">Your Seat</p>
                        <p class="gs-focus__value">{{ $myTable->name }}</p>
                    </div>
                    <button class="gs-focus__btn" id="scrollToMyTable" type="button">自分の席へ</button>
                </section>
                @else
                <section class="gs-focus gs-focus--pending">
                    <div>
                        <p class="gs-focus__label">Your Seat</p>
                        <p class="gs-focus__value">未配置</p>
                    </div>
                    <p class="gs-focus__note">席はまだ確定していません。</p>
                </section>
                @endif

                <div class="gs-board-scroll" id="gsGrid">
                    <div class="gs-board">
                        <div class="gs-stage gs-stage--head">
                            <span>高砂</span>
                        </div>

                        <div class="gs-table-grid">
                            @foreach ($tables as $table)
                            @php
                                $isMyTable = $myTableId && $table->id === $myTableId;
                                $occupiedSeats = $table->seats->filter(fn($s) => $s->assignment !== null)->values();
                                $leftSeats = $occupiedSeats->slice(0, (int) ceil(max($occupiedSeats->count(), 1) / 2))->values();
                                $rightSeats = $occupiedSeats->slice($leftSeats->count())->values();
                                $tableMark = trim($table->name ?? '') !== '' ? mb_substr(trim($table->name), 0, 1) : 'T';
                            @endphp
                            <article class="gs-table {{ $isMyTable ? 'gs-table--mine' : '' }}" id="gst-{{ $table->id }}">
                                <div class="gs-table__guests gs-table__guests--left">
                                    @foreach ($leftSeats as $seat)
                                    @php
                                        $assignedUser = $seat->assignment->user;
                                        $assignedProfile = $assignedUser->guestProfile;
                                        $isMe = $mySeat && $seat->id === $mySeat->id;
                                        $guestMeta = trim(($assignedProfile?->guestSideLabel() ?? '') . ' ' . ($assignedProfile?->relationshipLabel() ?? ''));
                                    @endphp
                                    <div class="gs-guest {{ $isMe ? 'is-mine' : '' }}">
                                        @if ($guestMeta)<p class="gs-guest__meta">{{ $guestMeta }}</p>@endif
                                        <p class="gs-guest__name">{{ $guestName($assignedUser) }} 様</p>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="gs-table__wreath" aria-label="{{ $table->name }}">
                                    <span class="gs-table__mark">{{ $tableMark }}</span>
                                    <span class="gs-table__name">{{ $table->name }}</span>
                                </div>

                                <div class="gs-table__guests gs-table__guests--right">
                                    @foreach ($rightSeats as $seat)
                                    @php
                                        $assignedUser = $seat->assignment->user;
                                        $assignedProfile = $assignedUser->guestProfile;
                                        $isMe = $mySeat && $seat->id === $mySeat->id;
                                        $guestMeta = trim(($assignedProfile?->guestSideLabel() ?? '') . ' ' . ($assignedProfile?->relationshipLabel() ?? ''));
                                    @endphp
                                    <div class="gs-guest {{ $isMe ? 'is-mine' : '' }}">
                                        @if ($guestMeta)<p class="gs-guest__meta">{{ $guestMeta }}</p>@endif
                                        <p class="gs-guest__name">{{ $guestName($assignedUser) }} 様</p>
                                    </div>
                                    @endforeach
                                </div>
                            </article>
                            @endforeach
                        </div>

                        <div class="gs-stage gs-stage--entrance">
                            <span>受付・入口</span>
                        </div>
                    </div>
                </div>

                <p class="gs-footnote">御席の不順、ご芳名に誤字がございましたら深くお詫び申し上げます</p>
            </div>
        </section>

    @endif

</div>

@endsection

@push('scripts')
<script>
(function () {
    const myTable = document.querySelector('.gs-table--mine');
    const scroller = document.getElementById('gsGrid');

    document.getElementById('scrollToMyTable')?.addEventListener('click', function () {
        myTable?.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
    });

    if (myTable && scroller) {
        setTimeout(function () {
            myTable.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }, 700);
    }
})();
</script>
@endpush
