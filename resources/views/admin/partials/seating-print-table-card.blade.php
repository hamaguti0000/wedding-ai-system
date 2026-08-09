@php
    $seats = $table->seats->values();
    $leftSeats = $seats->slice(0, (int) ceil(max($seats->count(), 1) / 2))->values();
    $rightSeats = $seats->slice($leftSeats->count())->values();
@endphp
<article class="sxp-table-card">
    <div class="sxp-table-label">{{ $tableMark($table) }}</div>
    <div class="sxp-table-content">
        <header class="sxp-table-card__head">
            <span class="sxp-table-card__name">{{ $table->name }}</span>
        </header>
        <div class="sxp-seat-map">
            <div class="sxp-seat-rail sxp-seat-rail--left">
                @forelse ($leftSeats as $seat)
                @php $assignedUser = $seat->assignment?->user; @endphp
                <div class="sxp-seat {{ $assignedUser ? '' : 'is-empty' }}">
                    <div class="sxp-seat__side sxp-seat__side--left">
                        <span>A</span><span></span><span></span>
                    </div>
                    <div class="sxp-seat__body">
                        <span class="sxp-seat__name">{{ $assignedUser ? $guestName($assignedUser) : '空席' }}</span>
                    </div>
                    <div class="sxp-seat__side sxp-seat__side--right">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                @empty
                <div class="sxp-seat is-empty">
                    <div class="sxp-seat__side sxp-seat__side--left"><span></span><span></span><span></span></div>
                    <div class="sxp-seat__body"><span class="sxp-seat__name">席未設定</span></div>
                    <div class="sxp-seat__side sxp-seat__side--right"><span></span><span></span><span></span></div>
                </div>
                @endforelse
            </div>

            <div class="sxp-seat-rail sxp-seat-rail--right">
                @foreach ($rightSeats as $seat)
                @php $assignedUser = $seat->assignment?->user; @endphp
                <div class="sxp-seat {{ $assignedUser ? '' : 'is-empty' }}">
                    <div class="sxp-seat__side sxp-seat__side--left">
                        <span>A</span><span></span><span></span>
                    </div>
                    <div class="sxp-seat__body">
                        <span class="sxp-seat__name">{{ $assignedUser ? $guestName($assignedUser) : '空席' }}</span>
                    </div>
                    <div class="sxp-seat__side sxp-seat__side--right">
                        <span></span><span></span><span></span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</article>
