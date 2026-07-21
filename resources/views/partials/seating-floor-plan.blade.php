{{-- 会場配置図（読み取り専用）
     $tables: SeatingTable コレクション（pos_x/pos_y を使用）
     $myTableId: （任意）ハイライトする自分の卓ID --}}
@php
    $fpRoomW = 1700;
    $fpRoomH = 760;
@endphp
<div class="fp-scroll">
    <div class="fp-room" style="width:{{ $fpRoomW }}px; height:{{ $fpRoomH }}px;">
        @foreach ($tables as $table)
        @php $isMine = isset($myTableId) && $myTableId === $table->id; @endphp
        <div class="fp-table {{ $isMine ? 'fp-table--mine' : '' }}"
             style="left:{{ $table->pos_x }}px; top:{{ $table->pos_y }}px;">
            <span class="fp-table__name">{{ $table->name }}</span>
        </div>
        @endforeach
    </div>
</div>
