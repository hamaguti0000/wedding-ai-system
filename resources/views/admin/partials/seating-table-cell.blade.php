<input type="text" class="sx-table-name" data-table-id="{{ $table->id }}" value="{{ $table->name }}" maxlength="50">
<div class="sx-table-actions">
    <button type="button" class="sx-add-seat" data-table-id="{{ $table->id }}" title="席を追加"><i class="fa-solid fa-plus"></i>席</button>
    <button type="button" class="sx-del-table" data-table-id="{{ $table->id }}" title="テーブルを削除"><i class="fa-solid fa-trash"></i></button>
</div>

@if ($table->assignedGroups->isNotEmpty())
<div class="sx-table-groups">
    @foreach ($table->assignedGroups as $group)
    <span class="sx-table-group-chip">{{ $group->displayName() }}</span>
    @endforeach
</div>
@endif
