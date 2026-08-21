@extends('layouts.app')
@section('title', 'リマインダーメール | Admin')

@push('styles')
<style>
.reminder-form-card { background:#fff; border:1px solid #f0ebe3; border-radius:12px; padding:28px; box-shadow:0 2px 12px rgba(61,47,37,.05); margin-bottom:32px; }
.reminder-form-card h3 { font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:400; color:#3d2f25; margin-bottom:20px; border-bottom:1px solid #f0ebe3; padding-bottom:12px; }

.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:600px){ .form-row { grid-template-columns:1fr; } }

.form-group { margin-bottom:16px; }
.form-label { display:block; font-size:0.82rem; font-weight:600; color:#5a4535; margin-bottom:6px; }
.form-label .optional { font-size:0.75rem; font-weight:400; color:#9b8573; margin-left:6px; }
.form-input, .form-select, .form-textarea {
    width:100%; padding:10px 12px; border:1px solid #e0d4c4; border-radius:8px;
    font-size:0.88rem; color:#3d2f25; background:#fffdf9; box-sizing:border-box;
    font-family:inherit; transition:border-color .15s;
}
.form-input:focus, .form-select:focus, .form-textarea:focus { outline:none; border-color:#b38b59; }
.form-textarea { resize:vertical; min-height:120px; }

.send-options { display:flex; gap:10px; flex-wrap:wrap; margin-top:8px; }
.btn-send-now   { background:#b38b59; color:#fff; border:none; border-radius:8px; padding:11px 24px; font-size:0.88rem; font-weight:500; cursor:pointer; transition:background .15s; }
.btn-send-now:hover { background:#9a7548; }
.btn-schedule   { background:#fff; color:#b38b59; border:2px solid #b38b59; border-radius:8px; padding:9px 22px; font-size:0.88rem; font-weight:500; cursor:pointer; transition:all .15s; }
.btn-schedule:hover { background:#fef9f0; }

/* 予約日時フォールドアウト */
.schedule-box { background:#fdfaf6; border:1px solid #e8d5b7; border-radius:8px; padding:16px; margin-top:12px; display:none; }
.schedule-box.open { display:block; }

/* 一覧 */
.reminder-list { display:flex; flex-direction:column; gap:14px; }
.reminder-item { background:#fff; border:1px solid #f0ebe3; border-radius:10px; padding:18px 20px; box-shadow:0 1px 6px rgba(61,47,37,.04); }
.reminder-item__head { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; margin-bottom:10px; }
.reminder-item__title { font-size:0.95rem; font-weight:600; color:#3d2f25; }
.reminder-item__meta  { font-size:0.78rem; color:#9b8573; margin-top:4px; display:flex; flex-wrap:wrap; gap:8px; }
.reminder-item__body  { font-size:0.83rem; color:#7a6a5a; line-height:1.8; background:#fdfaf6; border-radius:6px; padding:10px 12px; white-space:pre-line; max-height:80px; overflow:hidden; position:relative; }
.reminder-item__body::after { content:''; position:absolute; bottom:0; left:0; right:0; height:20px; background:linear-gradient(transparent,#fdfaf6); }
.reminder-item__actions { display:flex; gap:8px; flex-wrap:wrap; margin-top:12px; }

/* バッジ */
.badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; }
.badge-success { background:#dcfce7; color:#166534; }
.badge-warning { background:#fef9c3; color:#854d0e; }
.badge-info    { background:#dbeafe; color:#1e40af; }
.badge-muted   { background:#f3f4f6; color:#6b7280; }

.empty-state { text-align:center; padding:48px 20px; color:#b0a090; }
.empty-state__icon { font-size:2.5rem; margin-bottom:12px; }

.recipient-panel { display:none; margin-top:12px; border:1px solid #eadfce; border-radius:10px; background:#fffdf9; overflow:hidden; }
.recipient-panel.open { display:block; }
.recipient-tools { display:flex; gap:10px; flex-wrap:wrap; align-items:center; padding:12px; border-bottom:1px solid #f0ebe3; }
.recipient-search { flex:1 1 220px; min-width:0; }
.recipient-count { color:#7a6654; font-size:.82rem; font-weight:600; }
.recipient-count strong { color:#b38b59; font-size:1rem; }
.recipient-actions { display:flex; gap:8px; flex-wrap:wrap; }
.recipient-action { border:1px solid #e1d4c3; background:#fff; color:#6d5843; border-radius:999px; padding:7px 12px; font-size:.76rem; cursor:pointer; }
.recipient-action:hover { border-color:#b38b59; color:#8b683d; }
.recipient-list { max-height:340px; overflow:auto; padding:10px; display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:8px; }
.recipient-item { display:flex; gap:10px; align-items:flex-start; border:1px solid #f0e7dc; border-radius:10px; background:#fff; padding:10px; cursor:pointer; }
.recipient-item:hover { border-color:#d5bd9c; background:#fffaf3; }
.recipient-item.is-disabled { opacity:.48; cursor:not-allowed; }
.recipient-name { color:#3d2f25; font-size:.86rem; font-weight:700; line-height:1.35; }
.recipient-meta { margin-top:3px; color:#9b8573; font-size:.72rem; line-height:1.55; }
.recipient-badge { display:inline-flex; margin-left:4px; padding:1px 6px; border-radius:999px; background:#f5eadb; color:#8b683d; font-size:.66rem; font-weight:700; }
.recipient-note { padding:0 12px 12px; color:#9b8573; font-size:.76rem; line-height:1.6; }
.recipient-warning { color:#b45309; font-weight:700; }
@media(max-width:600px){ .recipient-list { grid-template-columns:1fr; max-height:420px; } }
</style>
@endpush

@section('content')
<div class="admin-wrap">
    <h1><i class="fa-solid fa-envelope-open-text" style="font-size:1.1rem;opacity:.7;margin-right:8px;"></i>リマインダーメール</h1>
    <p class="page-desc">ゲストへのリマインドメールを手動送信または日時指定で予約送信できます。</p>

    @if(session('success'))
        <div class="alert-success" style="margin-bottom:20px;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-error" style="margin-bottom:20px;">
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
    @endif

    {{-- 新規作成フォーム --}}
    <div class="reminder-form-card">
        <h3>✉️ 新しいリマインダーを作成</h3>

        <form method="POST" action="{{ route('admin.reminders.store') }}" id="reminderForm">
            @csrf

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="title">管理用タイトル</label>
                    <input id="title" name="title" type="text" class="form-input"
                           placeholder="例）RSVP締め切り前リマインド"
                           value="{{ old('title') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="target">送信対象</label>
                    <select id="target" name="target" class="form-select">
                        <option value="all"           {{ old('target')=='all'           ? 'selected' : '' }}>👥 全ゲスト</option>
                        <option value="attending"     {{ old('target')=='attending'     ? 'selected' : '' }}>✅ 出席予定者のみ</option>
                        <option value="not_responded" {{ old('target')=='not_responded' ? 'selected' : '' }}>❓ 未回答者のみ</option>
                        <option value="selected"      {{ old('target')=='selected'      ? 'selected' : '' }}>☑️ ゲストを選択</option>
                    </select>
                </div>
            </div>

            <div class="recipient-panel{{ old('target') === 'selected' ? ' open' : '' }}" id="recipientPanel">
                <div class="recipient-tools">
                    <input type="search" class="form-input recipient-search" id="recipientSearch" placeholder="名前・ふりがな・メールで検索">
                    <div class="recipient-count"><strong id="selectedRecipientCount">0</strong>人選択中</div>
                    <div class="recipient-actions">
                        <button type="button" class="recipient-action" data-recipient-action="visible">表示中を選択</button>
                        <button type="button" class="recipient-action" data-recipient-action="clear">解除</button>
                    </div>
                </div>
                <div class="recipient-list" id="recipientList">
                    @php $oldSelectedUserIds = collect(old('selected_user_ids', []))->map(fn($id) => (int) $id)->all(); @endphp
                    @foreach($guests as $guest)
                        @php
                            $profile = $guest->guestProfile;
                            $hasEmail = filled($guest->email);
                            $participation = $profile?->participationLabel() ?? '未回答';
                            $side = $profile?->guestSideLabel() ?? '—';
                            $furigana = $profile?->furigana() ?: '';
                        @endphp
                        <label class="recipient-item{{ $hasEmail ? '' : ' is-disabled' }}"
                               data-recipient-item
                               data-search="{{ Str::lower($guest->name . ' ' . $furigana . ' ' . $guest->email . ' ' . $participation . ' ' . $side) }}">
                            <input type="checkbox" name="selected_user_ids[]" value="{{ $guest->id }}"
                                   data-recipient-checkbox
                                   {{ in_array($guest->id, $oldSelectedUserIds, true) ? 'checked' : '' }}
                                   {{ $hasEmail ? '' : 'disabled' }}>
                            <span>
                                <span class="recipient-name">{{ $guest->name }}</span>
                                <span class="recipient-badge">{{ $participation }}</span>
                                <span class="recipient-meta">
                                    {{ $side }} @if($furigana) / {{ $furigana }} @endif<br>
                                    {{ $hasEmail ? $guest->email : 'メール未登録のため送信不可' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <p class="recipient-note" id="recipientNote">無料枠の目安は100通/日です。選択人数が100人を超える場合は分割送信をおすすめします。</p>
            </div>


            <div class="form-group">
                <label class="form-label" for="subject">件名</label>
                <input id="subject" name="subject" type="text" class="form-input"
                       placeholder="例）【K&M Wedding】出欠のご回答をお願いします"
                       value="{{ old('subject') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="message">本文</label>
                <textarea id="message" name="message" class="form-textarea"
                          placeholder="ゲストへのメッセージを入力してください。&#10;&#10;例）いつもお世話になっております。&#10;結婚式の出欠回答の締め切りが近づいています。..."
                          required>{{ old('message') }}</textarea>
                <p style="font-size:0.75rem;color:#9b8573;margin-top:4px;">※ メール冒頭に「○○ 様」の宛名が自動で入ります。本文のみ入力してください。</p>
            </div>

            {{-- 送信方法 --}}
            <div class="form-group">
                <label class="form-label">送信方法</label>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;color:#3d2f25;">
                        <input type="radio" name="send_type" value="now" checked onchange="toggleSchedule(this)">
                        <span>今すぐ送信</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;color:#3d2f25;">
                        <input type="radio" name="send_type" value="schedule" onchange="toggleSchedule(this)">
                        <span>日時を指定して予約</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;color:#3d2f25;">
                        <input type="radio" name="send_type" value="draft" onchange="toggleSchedule(this)">
                        <span>下書き保存</span>
                    </label>
                </div>

                <div class="schedule-box" id="scheduleBox">
                    <label class="form-label" for="scheduled_at">送信日時</label>
                    <input id="scheduled_at" name="scheduled_at" type="datetime-local"
                           class="form-input" style="max-width:280px;"
                           value="{{ old('scheduled_at') }}">
                    <p style="font-size:0.75rem;color:#9b8573;margin-top:4px;">※ 本番サーバーで cron が動いていれば自動送信されます</p>
                </div>
            </div>

            <input type="hidden" name="send_now" id="sendNowInput" value="1">

            <button type="submit" class="btn-primary" id="submitBtn">
                <i class="fa-solid fa-paper-plane"></i> <span id="submitLabel">今すぐ送信する</span>
            </button>
        </form>
    </div>

    {{-- 送信履歴・予約一覧 --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h2 style="font-size:1rem;font-weight:600;color:#3d2f25;">
            <i class="fa-solid fa-clock-rotate-left" style="opacity:.6;margin-right:6px;"></i>送信履歴 / 予約一覧
        </h2>
        <span style="font-size:0.78rem;color:#9b8573;">{{ $reminders->count() }}件</span>
    </div>

    @if($reminders->isEmpty())
        <div class="empty-state">
            <div class="empty-state__icon">📭</div>
            <p>まだリマインダーはありません</p>
        </div>
    @else
        <div class="reminder-list">
            @foreach($reminders as $r)
            <div class="reminder-item">
                <div class="reminder-item__head">
                    <div>
                        <div class="reminder-item__title">{{ $r->title }}</div>
                        <div class="reminder-item__meta">
                            <span>{{ $r->targetLabel() }}</span>
                            <span>·</span>
                            <span>件名: {{ Str::limit($r->subject, 40) }}</span>
                            @if($r->scheduled_at && $r->isPending())
                                <span>·</span>
                                <span>📅 {{ $r->scheduled_at->format('Y/m/d H:i') }} 送信予定</span>
                            @endif
                            @if($r->sent_at)
                                <span>·</span>
                                <span>✅ {{ $r->sent_at->format('Y/m/d H:i') }} 送信済み（{{ $r->sent_count }}件）</span>
                            @endif
                            @if($r->creator)
                                <span>·</span>
                                <span>作成: {{ $r->creator->name }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="badge {{ $r->statusBadgeClass() }}">{{ $r->statusLabel() }}</span>
                </div>

                <div class="reminder-item__body">{{ $r->message }}</div>

                <div class="reminder-item__actions">
                    @if($r->isPending())
                        {{-- 今すぐ送信 --}}
                        <form method="POST" action="{{ route('admin.reminders.send', $r->id) }}"
                              onsubmit="return confirm('今すぐ送信しますか？')">
                            @csrf
                            <button type="submit" class="btn-small btn-primary">
                                <i class="fa-solid fa-paper-plane"></i> 今すぐ送信
                            </button>
                        </form>
                        {{-- キャンセル --}}
                        <form method="POST" action="{{ route('admin.reminders.cancel', $r->id) }}"
                              onsubmit="return confirm('予約をキャンセルしますか？')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-small btn-secondary">キャンセル</button>
                        </form>
                    @endif
                    {{-- 削除 --}}
                    <form method="POST" action="{{ route('admin.reminders.destroy', $r->id) }}"
                          onsubmit="return confirm('削除しますか？')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-small btn-danger">削除</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<script>
const targetSelect = document.getElementById('target');
const recipientPanel = document.getElementById('recipientPanel');
const recipientSearch = document.getElementById('recipientSearch');
const recipientItems = Array.from(document.querySelectorAll('[data-recipient-item]'));
const recipientCheckboxes = Array.from(document.querySelectorAll('[data-recipient-checkbox]'));
const selectedRecipientCount = document.getElementById('selectedRecipientCount');
const recipientNote = document.getElementById('recipientNote');

function updateRecipientPanel() {
    if (!targetSelect || !recipientPanel) return;
    recipientPanel.classList.toggle('open', targetSelect.value === 'selected');
    updateRecipientCount();
}

function updateRecipientCount() {
    const count = recipientCheckboxes.filter(cb => cb.checked && !cb.disabled).length;
    if (selectedRecipientCount) selectedRecipientCount.textContent = count;
    if (recipientNote) {
        recipientNote.innerHTML = count > 100
            ? '<span class="recipient-warning">100人を超えています。</span> 無料枠では日を分けて送信してください。'
            : '無料枠の目安は100通/日です。選択人数が100人を超える場合は分割送信をおすすめします。';
    }
}

function filterRecipients() {
    const query = (recipientSearch?.value || '').trim().toLowerCase();
    recipientItems.forEach(item => {
        item.style.display = !query || item.dataset.search.includes(query) ? '' : 'none';
    });
}

targetSelect?.addEventListener('change', updateRecipientPanel);
recipientSearch?.addEventListener('input', filterRecipients);
recipientCheckboxes.forEach(cb => cb.addEventListener('change', updateRecipientCount));
document.querySelectorAll('[data-recipient-action]').forEach(button => {
    button.addEventListener('click', () => {
        if (button.dataset.recipientAction === 'clear') {
            recipientCheckboxes.forEach(cb => cb.checked = false);
        } else {
            recipientItems.forEach(item => {
                if (item.style.display === 'none') return;
                const cb = item.querySelector('[data-recipient-checkbox]');
                if (cb && !cb.disabled) cb.checked = true;
            });
        }
        updateRecipientCount();
    });
});
updateRecipientPanel();

function toggleSchedule(radio) {
    const box       = document.getElementById('scheduleBox');
    const sendNow   = document.getElementById('sendNowInput');
    const label     = document.getElementById('submitLabel');
    const schedAt   = document.getElementById('scheduled_at');

    if (radio.value === 'now') {
        box.classList.remove('open');
        sendNow.value  = '1';
        schedAt.removeAttribute('required');
        label.textContent = '今すぐ送信する';
    } else if (radio.value === 'schedule') {
        box.classList.add('open');
        sendNow.value  = '0';
        schedAt.setAttribute('required', 'required');
        label.textContent = '予約を保存する';
    } else { // draft
        box.classList.remove('open');
        sendNow.value  = '0';
        schedAt.removeAttribute('required');
        schedAt.value  = '';
        label.textContent = '下書きを保存する';
    }
}
</script>
@endsection
