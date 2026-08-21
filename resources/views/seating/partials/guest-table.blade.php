@php
    $isMyTable = $myTableId && $table->id === $myTableId;
    /*
      左右の振り分けは「空席も含めた全席」を半分にして決める。以前は空席を先に
      除外してから半分に割っていたため、空席の入り方によって人が本来と逆側に
      表示されていた(2026-08-12、管理画面では右側の木下様がゲスト画面では
      左側に出ていたことで発覚)。管理画面・印刷版(seating-print-table-card)は
      元から全席基準で割っており、そちらに揃える。表示は空席を除いて行う。
    */
    $allSeats = $table->seats->values();
    $leftAll = $allSeats->slice(0, (int) ceil(max($allSeats->count(), 1) / 2))->values();
    $leftSeats = $leftAll->filter(fn($s) => $s->assignment !== null)->values();
    $rightSeats = $allSeats->slice($leftAll->count())
        ->filter(fn($s) => $s->assignment !== null)->values();
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
            <p class="gs-guest__name"><a href="{{ route('people.show', ['user' => $assignedUser, 'from' => 'seating']) }}">{{ $guestName($assignedUser) }}</a> 様</p>
        </div>
        @endforeach
    </div>

    <div class="gs-table__wreath" aria-label="{{ $table->name }}">
        <span class="gs-table__mark">{{ $tableMark }}</span>
    </div>

    <div class="gs-table__guests gs-table__guests--right">
        @foreach ($rightSeats as $seat)
        @php
            $assignedUser = $seat->assignment->user;
            $isMe = $mySeat && $seat->id === $mySeat->id;
        @endphp
        <div class="gs-guest {{ $isMe ? 'is-mine' : '' }}">
            <p class="gs-guest__name"><a href="{{ route('people.show', ['user' => $assignedUser, 'from' => 'seating']) }}">{{ $guestName($assignedUser) }}</a> 様</p>
        </div>
        @endforeach
    </div>
</article>
