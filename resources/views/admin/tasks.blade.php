@extends('layouts.app')
@section('title', '当日の役割管理 | Admin')

@push('styles')
<style>
.task-item {
    background: #fff; border-radius: 10px; padding: 16px 20px;
    margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #f0ebe3;
}
.task-item__head { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 6px; }
.task-item__name { font-size: 0.98rem; font-weight: 600; color: #3d2f25; flex: 1; }
.task-item__home-badge {
    font-size: 0.68rem; padding: 2px 8px; border-radius: 3px;
    background: #eafaf1; color: #1e6e3b; border: 1px solid #a9dfbf;
    white-space: nowrap; flex-shrink: 0;
}
.task-item__home-badge--off {
    background: #f5f5f5; color: #aaa; border-color: #ddd;
}
.task-item__desc { font-size: 0.84rem; color: #7a6a5a; line-height: 1.7; white-space: pre-line; }
.task-item__members { margin-top: 10px; display: flex; flex-wrap: wrap; gap: 6px; }
.task-item__member {
    font-size: 0.75rem; padding: 3px 10px; border-radius: 20px;
    background: #fef9f0; color: #b38b59; border: 1px solid #e8d5b7;
}
.task-item__member-time { opacity: 0.75; margin-left: 4px; }
.task-item__actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; align-items: center; }

.add-card { background: #fff; border-radius: 12px; padding: 22px 24px; box-shadow: 0 3px 12px rgba(0,0,0,0.07); margin-bottom: 28px; border: 2px dashed #e8d5b7; }
.add-card h3 { font-size: 0.78rem; font-weight: 700; color: #b38b59; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 16px; }

.home-toggle { display: flex; align-items: center; gap: 10px; }
.home-toggle input[type="checkbox"] { width: 16px; height: 16px; accent-color: #b38b59; }
.home-toggle label { font-size: 0.88rem; color: #6b5b4e; cursor: pointer; }

.prog-list { margin-top: 10px; }
.prog-list-item {
    display: flex; align-items: center; gap: 8px;
    padding: 7px 12px; background: #fff; border: 1px solid #f0ebe3;
    border-radius: 6px; margin-bottom: 6px; font-size: 0.84rem;
}
.prog-list-item__time { color: #b38b59; font-weight: 600; width: 56px; flex-shrink: 0; font-size: 0.78rem; }
.prog-list-item__title { flex: 1; color: #3d2f25; }
.prog-list-item__actions { display: flex; gap: 4px; flex-shrink: 0; }
.prog-panel { display:none; margin-top:10px; background:#fef9f0; border-radius:8px; padding:16px; border:1px solid #e8d5b7; }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-clipboard-list" style="font-size:1.2rem;opacity:0.7;margin-right:8px;"></i>当日の役割管理</h1>
    <p class="page-desc">受付・スピーチなど当日ゲストにお願いする役割を登録します。ユーザー編集画面から各ゲストに割り当てられます。</p>

    @if (session('success'))
    <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif

    {{-- 追加フォーム --}}
    <div class="add-card">
        <h3>新しい役割を追加</h3>
        <form method="POST" action="{{ route('admin.tasks.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label>役割名 <span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           placeholder="例：受付、友人代表スピーチ、乾杯の音頭" required>
                    @error('name')<span class="field-error">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label>内容・お願い文</label>
                <textarea name="description" rows="3"
                    placeholder="例：式の30分前から会場入口でご案内をお願いします。&#10;不明な点は担当者（◯◯）までお声がけください。">{{ old('description') }}</textarea>
                <p class="field-note">ゲストのプロフィール画面・ホーム画面に表示されます</p>
            </div>
            <div class="home-toggle" style="margin-bottom:16px;">
                <input type="checkbox" name="display_on_home" id="display_on_home" value="1" checked>
                <label for="display_on_home">ホーム画面の「当日のお願い」にも表示する</label>
            </div>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-plus"></i> 追加する
            </button>
        </form>
    </div>

    {{-- 一覧 --}}
    <div style="margin-bottom:8px;font-size:0.82rem;color:#999;">{{ $tasks->count() }}件</div>

    @if ($tasks->isEmpty())
    <div class="empty-state">
        <div class="empty-state__icon">📋</div>
        <p class="empty-state__title">まだ役割がありません</p>
        <p class="empty-state__desc">上のフォームから追加してください</p>
    </div>
    @else
    @foreach ($tasks as $task)
    <div class="task-item">
        <div class="task-item__head">
            <span class="task-item__name">{{ $task->name }}</span>
            <span class="task-item__home-badge {{ $task->display_on_home ? '' : 'task-item__home-badge--off' }}">
                {{ $task->display_on_home ? 'ホーム表示' : 'ホーム非表示' }}
            </span>
        </div>
        @if ($task->description)
        <p class="task-item__desc">{{ $task->description }}</p>
        @endif

        {{-- 担当者一覧 --}}
        @if ($task->assignments->count())
        <div class="task-item__members">
            @foreach ($task->assignments as $a)
            <span class="task-item__member">
                {{ $a->user?->name ?? $a->user?->username }}
                @if ($a->custom_time)
                <span class="task-item__member-time">{{ $a->custom_time }}</span>
                @endif
            </span>
            @endforeach
        </div>
        @else
        <p style="font-size:0.78rem;color:#bbb;margin-top:8px;">担当者未設定</p>
        @endif

        <div class="task-item__actions">
            <form method="POST" action="{{ route('admin.tasks.move-up', $task->id) }}">
                @csrf @method('PATCH')
                <button class="btn-sm btn-sm-pw" title="上へ"><i class="fa-solid fa-chevron-up"></i></button>
            </form>
            <form method="POST" action="{{ route('admin.tasks.move-down', $task->id) }}">
                @csrf @method('PATCH')
                <button class="btn-sm btn-sm-pw" title="下へ"><i class="fa-solid fa-chevron-down"></i></button>
            </form>
            <button class="btn-sm btn-sm-pw" onclick="toggleEdit({{ $task->id }})">
                <i class="fa-solid fa-pen"></i> 編集
            </button>
            <button class="btn-sm btn-sm-pw" onclick="toggleProg({{ $task->id }})">
                <i class="fa-solid fa-list-ol"></i> プログラム
                @if ($task->programItems->count())
                <span style="background:#b38b59;color:#fff;border-radius:10px;padding:1px 6px;font-size:0.7rem;margin-left:2px;">{{ $task->programItems->count() }}</span>
                @endif
            </button>
            <form method="POST" action="{{ route('admin.tasks.destroy', $task->id) }}"
                  onsubmit="return confirm('削除しますか？担当者の割り当ても解除されます。')">
                @csrf @method('DELETE')
                <button class="btn-sm btn-sm-del"><i class="fa-solid fa-trash"></i></button>
            </form>
        </div>

        {{-- プログラムパネル --}}
        <div id="prog-{{ $task->id }}" class="prog-panel">
            <p style="font-size:0.75rem;font-weight:700;color:#b38b59;letter-spacing:2px;text-transform:uppercase;margin-bottom:12px;">当日のプログラム</p>

            {{-- 既存項目 --}}
            @forelse ($task->programItems as $pi)
            <div class="prog-list-item">
                <span class="prog-list-item__time">{{ $pi->start_time ?? '—' }}</span>
                <span class="prog-list-item__title">
                    {{ $pi->title }}
                    @if ($pi->description)
                    <span style="color:#9b8573;font-size:0.78rem;margin-left:6px;">{{ $pi->description }}</span>
                    @endif
                </span>
                <div class="prog-list-item__actions">
                    <form method="POST" action="{{ route('admin.tasks.program.move-up', [$task->id, $pi->id]) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" style="padding:3px 7px;font-size:0.7rem;"><i class="fa-solid fa-chevron-up"></i></button></form>
                    <form method="POST" action="{{ route('admin.tasks.program.move-down', [$task->id, $pi->id]) }}">@csrf @method('PATCH')<button class="btn-sm btn-sm-pw" style="padding:3px 7px;font-size:0.7rem;"><i class="fa-solid fa-chevron-down"></i></button></form>
                    <button class="btn-sm btn-sm-pw" style="padding:3px 7px;font-size:0.7rem;" onclick="toggleProgEdit('pe-{{ $pi->id }}')"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" action="{{ route('admin.tasks.program.destroy', [$task->id, $pi->id]) }}" onsubmit="return confirm('削除？')">@csrf @method('DELETE')<button class="btn-sm btn-sm-del" style="padding:3px 7px;font-size:0.7rem;"><i class="fa-solid fa-trash"></i></button></form>
                </div>
            </div>
            {{-- 編集フォーム --}}
            <div id="pe-{{ $pi->id }}" style="display:none;margin:-6px 0 6px;background:#fff;border:1px solid #e8d5b7;border-radius:6px;padding:12px;">
                <form method="POST" action="{{ route('admin.tasks.program.update', [$task->id, $pi->id]) }}">
                    @csrf @method('PATCH')
                    <div style="display:grid;grid-template-columns:90px 1fr;gap:8px;margin-bottom:8px;">
                        <div class="form-group"><label style="font-size:0.75rem;">時間</label><input type="text" name="start_time" value="{{ $pi->start_time }}" placeholder="12:30"></div>
                        <div class="form-group"><label style="font-size:0.75rem;">タイトル</label><input type="text" name="title" value="{{ $pi->title }}" required></div>
                    </div>
                    <div class="form-group" style="margin-bottom:8px;"><label style="font-size:0.75rem;">説明</label><input type="text" name="description" value="{{ $pi->description }}" placeholder="任意"></div>
                    <div style="display:flex;gap:6px;"><button type="submit" class="btn-primary" style="padding:6px 14px;font-size:0.8rem;">保存</button><button type="button" onclick="toggleProgEdit('pe-{{ $pi->id }}')" class="btn-secondary" style="padding:6px 10px;font-size:0.8rem;">×</button></div>
                </form>
            </div>
            @empty
            <p style="font-size:0.8rem;color:#bbb;margin-bottom:12px;">プログラム項目がありません</p>
            @endforelse

            {{-- 追加フォーム --}}
            <form method="POST" action="{{ route('admin.tasks.program.store', $task->id) }}" style="margin-top:12px;padding-top:12px;border-top:1px solid #e8d5b7;">
                @csrf
                <p style="font-size:0.72rem;color:#b38b59;letter-spacing:1px;margin-bottom:8px;font-weight:600;">＋ 項目を追加</p>
                <div style="display:grid;grid-template-columns:90px 1fr;gap:8px;margin-bottom:8px;">
                    <div class="form-group"><label style="font-size:0.75rem;">時間</label><input type="text" name="start_time" placeholder="12:30"></div>
                    <div class="form-group"><label style="font-size:0.75rem;">タイトル <span class="req">*</span></label><input type="text" name="title" placeholder="例：受付開始" required></div>
                </div>
                <div class="form-group" style="margin-bottom:8px;"><label style="font-size:0.75rem;">説明（任意）</label><input type="text" name="description" placeholder="例：会場入口にてゲストをご案内ください"></div>
                <button type="submit" class="btn-primary" style="padding:7px 16px;font-size:0.82rem;"><i class="fa-solid fa-plus"></i> 追加</button>
            </form>
        </div>
    </div>

    {{-- 編集フォーム --}}
    <div id="task-edit-{{ $task->id }}" style="display:none;background:#fef9f0;border-radius:0 0 10px 10px;padding:16px 20px;margin-top:-10px;margin-bottom:10px;border:1px solid #e8d5b7;border-top:none;">
        <form method="POST" action="{{ route('admin.tasks.update', $task->id) }}">
            @csrf @method('PATCH')
            <div class="form-group" style="margin-bottom:10px;">
                <label>役割名</label>
                <input type="text" name="name" value="{{ $task->name }}" required>
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>内容・お願い文</label>
                <textarea name="description" rows="3">{{ $task->description }}</textarea>
            </div>
            <div class="home-toggle" style="margin-bottom:14px;">
                <input type="hidden" name="display_on_home" value="0">
                <input type="checkbox" name="display_on_home" id="home_{{ $task->id }}" value="1"
                       {{ $task->display_on_home ? 'checked' : '' }}>
                <label for="home_{{ $task->id }}">ホーム画面に表示する</label>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-primary" style="padding:8px 20px;font-size:0.85rem;">保存</button>
                <button type="button" class="btn-secondary" onclick="toggleEdit({{ $task->id }})" style="padding:8px 14px;font-size:0.85rem;">キャンセル</button>
            </div>
        </form>
    </div>
    @endforeach
    @endif
</div>

<script>
function toggleEdit(id) {
    const el = document.getElementById('task-edit-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleProg(id) {
    const el = document.getElementById('prog-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
function toggleProgEdit(id) {
    const el = document.getElementById(id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
@endsection
