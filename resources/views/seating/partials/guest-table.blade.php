@php
    $isMyTable = $myTableId && $table->id === $myTableId;
    $occupiedSeats = $table->seats->filter(fn($s) => $s->assignment !== null)->values();
    $leftSeats = $occupiedSeats->slice(0, (int) ceil(max($occupiedSeats->count(), 1) / 2))->values();
    $rightSeats = $occupiedSeats->slice($leftSeats->count())->values();
    $tableMark = $tableMark ?? 'T';
@endphp
<article class="gs-table {{ $isMyTable ? 'gs-table--mine' : '' }}" id="gst-{{ $table->id }}">
    <div class="gs-table__guests gs-table__guests--left">
        @foreach ($leftSeats as $seat)
        @php
            $assignedUser = $seat->assignment->user;
            $isMe = $mySeat && $seat->id === $mySeat->id;
        @endphp
        <div class="gs-guest {{ $isMe ? 'is-mine' : '' }}">
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
            $isMe = $mySeat && $seat->id === $mySeat->id;
        @endphp
        <div class="gs-guest {{ $isMe ? 'is-mine' : '' }}">
            <p class="gs-guest__name">{{ $guestName($assignedUser) }} 様</p>
        </div>
        @endforeach
    </div>
</article>
