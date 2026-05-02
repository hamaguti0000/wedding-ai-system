/* ================================================================
   seating.js — 席次レイアウトツール
   - 仮想キャンバス 1000×660（A4横比率）
   - CSS transform scale でスケーリング、ドラッグ座標をスケール補正
   - .is-preview クラスで編集/印刷プレビュー分離
   ================================================================ */
(function () {
    'use strict';

    /* ── 定数 ────────────────────────────────────────────────── */
    const CANVAS_W  = 1400;
    const CANVAS_H  = 900;
    const TABLE_MIN_MARGIN = 40;  // キャンバス端からの最小余白

    const CSRF     = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const TYPE_CFG = window.SEAT_TYPE_CONFIG ?? {};
    const canvas   = document.getElementById('seatingCanvas');
    const pool     = document.getElementById('unassignedPool');
    if (!canvas || !pool) return;

    /* ── スケール管理 ────────────────────────────────────────── */
    let _scale = 1;

    function getScale() {
        const area = document.getElementById('canvasArea');
        if (!area) return 1;
        // プレビュー時はウィンドウ全体に合わせる、編集時はエリア幅に合わせる
        const isPreview = document.getElementById('stApp')?.classList.contains('is-preview');
        const padX = isPreview ? 48 : 48;
        const padY = isPreview ? 48 : 48;
        const scaleX = (area.clientWidth  - padX) / CANVAS_W;
        const scaleY = (area.clientHeight - padY) / CANVAS_H;
        return Math.min(1, Math.max(0.35, isPreview ? Math.min(scaleX, scaleY) : scaleX));
    }

    function applyScale() {
        const scaler = document.getElementById('canvasScaler');
        if (!scaler) return;
        _scale = getScale();
        scaler.style.transform       = `scale(${_scale})`;
        scaler.style.transformOrigin = 'top left';
        // transform は layout に影響しないため marginBottom/Right で補正
        scaler.style.marginRight  = ((_scale - 1) * CANVAS_W) + 'px';
        scaler.style.marginBottom = ((_scale - 1) * CANVAS_H) + 'px';
    }

    window.addEventListener('resize', applyScale);

    /* ── アプリ状態 ───────────────────────────────────────────── */
    const state = {
        selectedSeat:  null,
        saveStatus:    'saved',
        filterSide:    'all',
        searchQuery:   '',
        guestDnD:      null,
        tableDrag:     null,
        seatDrag:      { active: false },
        isPreview:     false,
    };

    /* ── API ──────────────────────────────────────────────────── */
    const api = {
        async call(method, url, body) {
            setSaveStatus('saving');
            try {
                const r = await fetch(url, {
                    method,
                    headers: body
                        ? { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() }
                        : { 'X-CSRF-TOKEN': CSRF() },
                    body: body ? JSON.stringify(body) : undefined,
                });
                const data = await r.json();
                setSaveStatus(r.ok ? 'saved' : 'error');
                return { ok: r.ok, data };
            } catch {
                setSaveStatus('error');
                return { ok: false, data: {} };
            }
        },
        post:  (url, b) => api.call('POST', url, b),
        patch: (url, b) => api.call('PATCH', url, b),
        del:   (url)    => api.call('DELETE', url),

        _timers: {},
        debounce(key, fn, ms = 600) {
            clearTimeout(this._timers[key]);
            this._timers[key] = setTimeout(fn, ms);
        },
        saveTablePos(id, x, y) {
            this.debounce(`tp-${id}`, () => this.patch(`/admin/seating/tables/${id}/position`, { x, y }));
        },
        saveSeatPos(id, x, y) {
            this.debounce(`sp-${id}`, () => this.patch(`/admin/seating/seats/${id}`, { pos_x: x, pos_y: y }));
        },
    };

    /* ── UI ユーティリティ ────────────────────────────────────── */
    const esc = s => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

    function setSaveStatus(s) {
        state.saveStatus = s;
        const el = document.getElementById('saveStatus');
        if (!el) return;
        const map = {
            saving: { text: '保存中…',    cls: 'is-saving', icon: '' },
            saved:  { text: '自動保存済み', cls: 'is-saved',  icon: '✓ ' },
            error:  { text: '保存失敗',    cls: 'is-error',  icon: '⚠ ' },
        };
        const m = map[s] ?? map.saved;
        el.textContent = m.icon + m.text;
        el.className   = `st-save-status ${m.cls}`;
    }

    let _tt;
    function toast(msg, type = 'default') {
        let el = document.getElementById('stToast');
        if (!el) {
            el = Object.assign(document.createElement('div'), { id: 'stToast' });
            document.body.appendChild(el);
        }
        el.textContent = msg;
        el.className = `st-toast st-toast--${type}`;
        requestAnimationFrame(() => el.classList.add('st-toast--visible'));
        clearTimeout(_tt);
        _tt = setTimeout(() => el.classList.remove('st-toast--visible'), 2600);
    }

    /* ── 進捗バー更新 ─────────────────────────────────────────── */
    function updateProgress() {
        const totalEl    = document.getElementById('summaryTotal');
        const total      = totalEl ? parseInt(totalEl.dataset.total ?? totalEl.textContent) : 0;
        const assigned   = canvas.querySelectorAll('.canvas-seat.is-occupied').length;
        const unassigned = pool.querySelectorAll('.st-guest-card').length;
        const pct = total > 0 ? Math.round(assigned / total * 100) : 0;

        const fill  = document.getElementById('progressFill');
        const label = document.getElementById('progressLabel');
        if (fill)  fill.style.width = pct + '%';
        if (label) label.textContent = `${assigned} / ${total} 名配置済み`;

        const uc = document.getElementById('unassignedCount');
        const ac = document.getElementById('assignedCount');
        if (uc) uc.textContent = unassigned + '名';
        if (ac) ac.textContent = assigned + '名';
    }

    /* ── テーブルメタ更新 ─────────────────────────────────────── */
    function updateTableMeta(tableId) {
        const body   = document.getElementById(`ct-body-${tableId}`);
        const metaEl = document.getElementById(`ct-meta-${tableId}`);
        if (!body || !metaEl) return;
        const seats    = body.querySelectorAll('.canvas-seat').length;
        const occupied = body.querySelectorAll('.canvas-seat.is-occupied').length;
        metaEl.textContent = `${occupied}/${seats}`;
    }

    function refreshHint(tableId) {
        const body = document.getElementById(`ct-body-${tableId}`);
        if (!body) return;
        const hint = body.querySelector('.ct-hint');
        if (!hint) return;
        hint.style.display = body.querySelector('.canvas-seat') ? 'none' : '';
    }

    /* ================================================================
       テーブルドラッグ（Pointer Events）
       ドラッグ開始時のスケールを保持し、移動量をスケール補正
    ================================================================ */
    canvas.addEventListener('pointerdown', e => {
        if (state.guestDnD || state.seatDrag.active || state.isPreview) return;
        const handle = e.target.closest('[data-drag-handle]');
        if (!handle || e.target.closest('button') || e.target.closest('.canvas-seat')) return;
        const el = handle.closest('.canvas-table');
        if (!el) return;
        e.preventDefault();
        state.tableDrag = {
            el, tableId: el.dataset.tableId,
            startPX: e.clientX, startPY: e.clientY,
            origX: parseInt(el.style.left ?? '0'),
            origY: parseInt(el.style.top  ?? '0'),
            scale: _scale,
        };
        el.classList.add('is-dragging-table');
        el.setPointerCapture(e.pointerId);
    });

    canvas.addEventListener('pointermove', e => {
        if (!state.tableDrag) return;
        e.preventDefault();
        const { tableDrag: d } = state;
        const dx  = (e.clientX - d.startPX) / d.scale;
        const dy  = (e.clientY - d.startPY) / d.scale;
        const nx  = Math.max(0, Math.min(CANVAS_W - TABLE_MIN_MARGIN, d.origX + dx));
        const ny  = Math.max(0, Math.min(CANVAS_H - TABLE_MIN_MARGIN, d.origY + dy));
        d.el.style.left = nx + 'px';
        d.el.style.top  = ny + 'px';
    });

    canvas.addEventListener('pointerup', e => {
        if (!state.tableDrag) return;
        const { tableDrag: d } = state;
        d.el.classList.remove('is-dragging-table');
        d.el.releasePointerCapture(e.pointerId);
        api.saveTablePos(d.tableId, Math.round(parseFloat(d.el.style.left)), Math.round(parseFloat(d.el.style.top)));
        state.tableDrag = null;
    });

    canvas.addEventListener('pointercancel', () => {
        const d = state.tableDrag;
        if (!d) return;
        d.el.classList.remove('is-dragging-table');
        d.el.style.left = d.origX + 'px';
        d.el.style.top  = d.origY + 'px';
        state.tableDrag = null;
    });

    /* ================================================================
       席ドラッグ（Pointer Events、テーブル内）
    ================================================================ */
    canvas.addEventListener('pointerdown', e => {
        if (state.guestDnD || state.tableDrag || state.isPreview) return;
        const seat = e.target.closest('.canvas-seat');
        if (!seat || e.target.closest('button')) return;
        e.preventDefault();
        e.stopPropagation();
        const sd = state.seatDrag;
        Object.assign(sd, {
            active: true, el: seat,
            seatId: seat.dataset.seatId,
            body: seat.closest('.canvas-table__body'),
            startPX: e.clientX, startPY: e.clientY,
            origX: parseInt(seat.style.left ?? '0'),
            origY: parseInt(seat.style.top  ?? '0'),
            scale: _scale,
            moved: false,
        });
        seat.classList.add('is-dragging-seat');
        seat.setPointerCapture(e.pointerId);
    }, true);

    canvas.addEventListener('pointermove', e => {
        const sd = state.seatDrag;
        if (!sd.active) return;
        e.preventDefault();
        const dx = (e.clientX - sd.startPX) / sd.scale;
        const dy = (e.clientY - sd.startPY) / sd.scale;
        const nx = Math.max(0, sd.origX + dx);
        const ny = Math.max(0, sd.origY + dy);
        sd.el.style.left = nx + 'px';
        sd.el.style.top  = ny + 'px';
        if (Math.abs(dx) > 3 || Math.abs(dy) > 3) sd.moved = true;
    }, true);

    canvas.addEventListener('pointerup', e => {
        const sd = state.seatDrag;
        if (!sd.active) return;
        sd.el.classList.remove('is-dragging-seat');
        sd.el.releasePointerCapture(e.pointerId);
        if (sd.moved) {
            api.saveSeatPos(sd.seatId, Math.round(parseFloat(sd.el.style.left)), Math.round(parseFloat(sd.el.style.top)));
            syncBodySize(sd.body);
        } else {
            selectSeat(sd.el);
        }
        sd.active = false; sd.moved = false;
    }, true);

    canvas.addEventListener('pointercancel', () => {
        const sd = state.seatDrag;
        if (!sd.active) return;
        sd.el.classList.remove('is-dragging-seat');
        sd.el.style.left = sd.origX + 'px';
        sd.el.style.top  = sd.origY + 'px';
        sd.active = false;
    }, true);

    /* ================================================================
       ゲスト D&D（HTML5）— サイドバーカード → 席
    ================================================================ */
    document.addEventListener('dragstart', e => {
        const card = e.target.closest('.st-guest-card[draggable]');
        if (!card) return;
        state.guestDnD = { card, userId: card.dataset.userId, name: card.dataset.name };
        card.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', card.dataset.userId);
    });

    document.addEventListener('dragend', () => {
        state.guestDnD?.card?.classList.remove('is-dragging');
        canvas.querySelectorAll('.drop-target, .drop-forbidden')
            .forEach(el => el.classList.remove('drop-target', 'drop-forbidden'));
        state.guestDnD = null;
    });

    canvas.addEventListener('dragover', e => {
        if (!state.guestDnD) return;
        const seat = e.target.closest('.canvas-seat');
        if (!seat) return;
        e.preventDefault(); e.stopPropagation();
        const occupied = seat.classList.contains('is-occupied');
        seat.classList.toggle('drop-target',   !occupied);
        seat.classList.toggle('drop-forbidden', occupied);
    });

    canvas.addEventListener('dragleave', e => {
        const seat = e.target.closest('.canvas-seat');
        if (!seat || seat.contains(e.relatedTarget)) return;
        seat.classList.remove('drop-target', 'drop-forbidden');
    });

    canvas.addEventListener('drop', async e => {
        if (!state.guestDnD) return;
        const seat = e.target.closest('.canvas-seat');
        if (!seat) return;
        e.preventDefault(); e.stopPropagation();
        seat.classList.remove('drop-target', 'drop-forbidden');
        if (seat.classList.contains('is-occupied')) {
            toast('この席は既に配置済みです', 'error');
            return;
        }
        const { card, userId, name } = state.guestDnD;
        const seatId  = seat.dataset.seatId;
        const tableId = seat.closest('.canvas-table__body')?.dataset.tableId;
        const initial = name.charAt(0);

        const { ok, data } = await api.post('/admin/seating/assign', { seat_id: seatId, user_id: userId });
        if (!ok) { toast(data.error ?? '配置に失敗しました', 'error'); return; }

        setSeatOccupied(seat, userId, initial, name);
        card.remove();
        addAssignedItem(userId, name, document.getElementById(`ct-${tableId}`)?.querySelector('.ct-name')?.textContent ?? '');
        updateTableMeta(tableId);
        updateProgress();
        if (state.selectedSeat === seat) refreshPropsPanel(seat);
        toast(`${name} を配置しました`, 'success');
    });

    /* ================================================================
       席の状態操作
    ================================================================ */
    function setSeatOccupied(seatEl, userId, initial, fullName) {
        seatEl.classList.add('is-occupied');
        seatEl.dataset.userId = userId;
        const init = seatEl.querySelector('.canvas-seat__initial');
        if (init) init.textContent = initial;
        let tt = seatEl.querySelector('.canvas-seat__tooltip');
        if (!tt) {
            tt = document.createElement('div');
            tt.className = 'canvas-seat__tooltip';
            seatEl.appendChild(tt);
        }
        tt.textContent = fullName;
    }

    function setSeatEmpty(seatEl) {
        seatEl.classList.remove('is-occupied');
        seatEl.dataset.userId = '';
        const init = seatEl.querySelector('.canvas-seat__initial');
        if (init) init.textContent = '';
        seatEl.querySelector('.canvas-seat__tooltip')?.remove();
    }

    /* ================================================================
       プロパティパネル
    ================================================================ */
    function openPropsPanel() {
        document.getElementById('seatProps')?.classList.add('is-open');
        document.getElementById('seatProps')?.setAttribute('aria-hidden', 'false');
    }
    function closePropsPanel() {
        document.getElementById('seatProps')?.classList.remove('is-open');
        document.getElementById('seatProps')?.setAttribute('aria-hidden', 'true');
        canvas.querySelectorAll('.canvas-seat.is-selected').forEach(s => s.classList.remove('is-selected'));
        state.selectedSeat = null;
    }

    function selectSeat(seatEl) {
        if (state.isPreview) return;
        canvas.querySelectorAll('.canvas-seat.is-selected').forEach(s => s.classList.remove('is-selected'));
        seatEl.classList.add('is-selected');
        state.selectedSeat = seatEl;
        refreshPropsPanel(seatEl);
        openPropsPanel();
    }

    function refreshPropsPanel(seatEl) {
        const type    = seatEl.dataset.type ?? 'normal';
        const userId  = seatEl.dataset.userId;
        const tooltip = seatEl.querySelector('.canvas-seat__tooltip');
        const fullName = tooltip?.textContent ?? '';

        document.querySelectorAll('.sp-type-radio').forEach(r => {
            r.checked = r.value === type;
        });

        const guestSec = document.getElementById('propsGuestSection');
        const emptySec = document.getElementById('propsEmptySection');
        const nameEl   = document.getElementById('propsGuestName');
        const avatarEl = document.getElementById('propsGuestAvatar');
        const unassign = document.getElementById('propsUnassign');

        if (userId) {
            if (guestSec) guestSec.style.display = '';
            if (emptySec) emptySec.style.display = 'none';
            if (nameEl)   nameEl.textContent = fullName;
            if (avatarEl) avatarEl.textContent = fullName.charAt(0);
            if (unassign) {
                unassign.dataset.seatId = seatEl.dataset.seatId;
                unassign.dataset.userId = userId;
            }
        } else {
            if (guestSec) guestSec.style.display = 'none';
            if (emptySec) emptySec.style.display = '';
        }

        const delBtn = document.getElementById('propsDeleteSeat');
        if (delBtn) delBtn.dataset.seatId = seatEl.dataset.seatId;
    }

    document.getElementById('propsClose')?.addEventListener('click', closePropsPanel);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closePropsPanel();
            if (state.isPreview) togglePreview();
        }
    });

    canvas.addEventListener('click', e => {
        if (!e.target.closest('.canvas-seat') && !e.target.closest('.canvas-table__handle')) {
            closePropsPanel();
        }
    });

    document.getElementById('propsTypeGrid')?.addEventListener('change', async e => {
        const radio = e.target.closest('.sp-type-radio');
        if (!radio || !state.selectedSeat) return;
        const type   = radio.value;
        const seatId = state.selectedSeat.dataset.seatId;
        const { ok } = await api.patch(`/admin/seating/seats/${seatId}`, { type });
        if (ok) {
            state.selectedSeat.dataset.type = type;
        } else {
            toast('タイプ変更に失敗しました', 'error');
        }
    });

    document.getElementById('propsUnassign')?.addEventListener('click', async e => {
        const btn    = e.currentTarget;
        const userId = btn.dataset.userId;
        const seat   = state.selectedSeat;
        if (!seat || !userId) return;
        const tableId = seat.closest('.canvas-table__body')?.dataset.tableId;

        const { ok } = await api.del(`/admin/seating/unassign/${userId}`);
        if (ok) {
            setSeatEmpty(seat);
            removeAssignedItem(userId);
            updateTableMeta(tableId);
            updateProgress();
            refreshPropsPanel(seat);
            toast('配置を解除しました');
        } else {
            toast('解除に失敗しました', 'error');
        }
    });

    document.getElementById('propsDeleteSeat')?.addEventListener('click', async e => {
        const seatId  = e.currentTarget.dataset.seatId;
        const seatEl  = document.getElementById(`seat-${seatId}`);
        const tableId = seatEl?.closest('.canvas-table__body')?.dataset.tableId;
        if (!seatId) return;

        const { ok, data } = await api.del(`/admin/seating/seats/${seatId}`);
        if (ok) {
            if (data.freed_user_id) removeAssignedItem(data.freed_user_id);
            seatEl?.remove();
            closePropsPanel();
            refreshHint(tableId);
            updateTableMeta(tableId);
            updateProgress();
            toast('席を削除しました');
        } else {
            toast('削除に失敗しました', 'error');
        }
    });

    /* ================================================================
       配置済みリスト管理
    ================================================================ */
    function addAssignedItem(userId, name, tableName) {
        const list = document.getElementById('assignedList');
        if (!list) return;
        const div = document.createElement('div');
        div.className = 'st-assigned-item';
        div.dataset.userId = userId;
        div.innerHTML = `
            <div class="st-assigned-item__dot" style="background:var(--ds-gold);border-radius:50%;"></div>
            <span class="st-assigned-item__name">${esc(name)}</span>
            <span class="st-assigned-item__table">${esc(tableName)}</span>`;
        list.appendChild(div);
    }

    function removeAssignedItem(userId) {
        document.querySelector(`#assignedList [data-user-id="${userId}"]`)?.remove();
    }

    /* ================================================================
       席追加（テーブルの ＋ ボタン）
    ================================================================ */
    canvas.addEventListener('click', async e => {
        const btn = e.target.closest('.ct-btn--add');
        if (!btn) return;
        const tableId = btn.dataset.tableId;
        const { ok, data } = await api.post(`/admin/seating/tables/${tableId}/seats`, { type: 'normal' });
        if (!ok) { toast('席の追加に失敗しました', 'error'); return; }
        const body = document.getElementById(`ct-body-${tableId}`);
        if (body) {
            body.appendChild(buildSeatNode(data.seat));
            syncBodySize(body);
        }
        refreshHint(tableId);
        updateTableMeta(tableId);
        toast('席を追加しました', 'success');
    });

    /* ================================================================
       テーブル削除
    ================================================================ */
    canvas.addEventListener('click', async e => {
        const btn = e.target.closest('.ct-btn--del');
        if (!btn) return;
        const tableId = btn.dataset.tableId;
        const node    = document.getElementById(`ct-${tableId}`);
        const name    = node?.querySelector('.ct-name')?.textContent ?? '';
        if (!confirm(`「${name}」を削除しますか？`)) return;

        const { ok } = await api.del(`/admin/seating/tables/${tableId}`);
        if (ok) {
            node?.remove();
            closePropsPanel();
            updateProgress();
            toast(`「${name}」を削除しました`);
            if (!canvas.querySelector('.canvas-table')) showCanvasEmpty();
        } else {
            toast('削除に失敗しました', 'error');
            location.reload();
        }
    });

    function showCanvasEmpty() {
        if (document.getElementById('canvasEmpty')) return;
        const div = document.createElement('div');
        div.id = 'canvasEmpty';
        div.className = 'st-canvas__empty';
        div.innerHTML = `<i class="fa-solid fa-chair" style="font-size:2.5rem;opacity:0.25;"></i><p>テーブルがありません<br>右下の ＋ ボタンから追加してください</p>`;
        canvas.appendChild(div);
    }

    /* ================================================================
       テーブル追加（FAB + モーダル）
    ================================================================ */
    const modal = document.getElementById('addTableModal');

    document.getElementById('fabAddTable')?.addEventListener('click', () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.getElementById('newTableName')?.focus();
    });
    const closeModal = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };
    document.getElementById('modalClose')?.addEventListener('click', closeModal);
    document.getElementById('modalCancel')?.addEventListener('click', closeModal);
    modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    document.getElementById('modalSubmit')?.addEventListener('click', async () => {
        const name      = document.getElementById('newTableName').value.trim();
        const seatCount = parseInt(document.getElementById('newTableSeats').value || '0');
        if (!name) { toast('テーブル名を入力してください', 'error'); return; }

        const { ok, data } = await api.post('/admin/seating/tables', {
            name,
            seat_count: isNaN(seatCount) ? 0 : seatCount,
        });
        if (!ok) { toast(data.message ?? '追加に失敗しました', 'error'); return; }

        document.getElementById('canvasEmpty')?.remove();
        canvas.appendChild(buildTableNode(data.table, data.seats ?? []));
        closeModal();
        document.getElementById('newTableName').value = '';
        document.getElementById('newTableSeats').value = '4';
        updateProgress();
        toast(`「${data.table.name}」を追加しました`, 'success');
    });

    /* ================================================================
       DOM ビルダー
    ================================================================ */
    // テーブルボディの min-width/min-height を席位置に合わせて更新
    function syncBodySize(body) {
        let maxRight = 180, maxBottom = 80;
        body.querySelectorAll('.canvas-seat').forEach(s => {
            maxRight  = Math.max(maxRight,  parseInt(s.style.left || '0') + 56);
            maxBottom = Math.max(maxBottom, parseInt(s.style.top  || '0') + 56);
        });
        body.style.minWidth  = maxRight  + 'px';
        body.style.minHeight = maxBottom + 'px';
    }

    function buildSeatNode(s) {
        const div = document.createElement('div');
        div.className = 'canvas-seat';
        div.id = `seat-${s.id}`;
        div.dataset.seatId = s.id;
        div.dataset.type   = s.type ?? 'normal';
        div.dataset.userId = '';
        div.style.left = (s.pos_x ?? 8) + 'px';
        div.style.top  = (s.pos_y ?? 8) + 'px';
        div.innerHTML = `
            <div class="canvas-seat__circle">
                <span class="canvas-seat__initial"></span>
                <span class="canvas-seat__plus" aria-hidden="true">+</span>
            </div>`;
        return div;
    }

    function buildTableNode(t, seats) {
        const div = document.createElement('div');
        div.className = 'canvas-table';
        div.id = `ct-${t.id}`;
        div.dataset.tableId = t.id;
        // 新規テーブルは未使用領域に配置（既存テーブル数ベース）
        const existing = canvas.querySelectorAll('.canvas-table').length;
        const col = existing % 4;
        const row = Math.floor(existing / 4);
        div.style.left = (t.pos_x ?? (24 + col * 240)) + 'px';
        div.style.top  = (t.pos_y ?? (24 + row * 200)) + 'px';
        div.innerHTML = `
            <div class="canvas-table__handle" data-drag-handle>
                <i class="fa-solid fa-grip-dots ct-grip"></i>
                <span class="ct-name" title="${esc(t.name)}">${esc(t.name)}</span>
                <span class="ct-count" id="ct-meta-${t.id}">0/${seats.length}</span>
                <button class="ct-btn ct-btn--add" data-table-id="${t.id}" title="席を追加">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <button class="ct-btn ct-btn--del" data-table-id="${t.id}" title="テーブルを削除">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="canvas-table__body" id="ct-body-${t.id}" data-table-id="${t.id}">
                <p class="ct-hint" id="ct-hint-${t.id}">＋ を押して席を追加</p>
            </div>`;
        const body = div.querySelector('.canvas-table__body');
        seats.forEach(s => body.appendChild(buildSeatNode(s)));
        if (seats.length) body.querySelector('.ct-hint')?.style.setProperty('display', 'none');
        syncBodySize(body);
        return div;
    }

    /* ================================================================
       プレビューモード
    ================================================================ */
    function togglePreview() {
        state.isPreview = !state.isPreview;
        const app = document.getElementById('stApp');
        app.classList.toggle('is-preview', state.isPreview);

        const btn      = document.getElementById('previewToggle');
        const printBtn = document.getElementById('printBtn');

        if (state.isPreview) {
            if (btn) btn.innerHTML = '<i class="fa-solid fa-pen" aria-hidden="true"></i>編集に戻る';
            closePropsPanel();
        } else {
            if (btn) btn.innerHTML = '<i class="fa-solid fa-eye" aria-hidden="true"></i>プレビュー';
        }

        applyScale();
    }

    document.getElementById('previewToggle')?.addEventListener('click', togglePreview);
    document.getElementById('printBtn')?.addEventListener('click', () => window.print());

    /* ================================================================
       サイドバーフィルタリング
    ================================================================ */
    document.getElementById('guestSearch')?.addEventListener('input', e => {
        state.searchQuery = e.target.value.toLowerCase();
        applyFilters();
    });

    document.getElementById('sideFilterSide')?.addEventListener('click', e => {
        const tab = e.target.closest('.st-filter-tab');
        if (!tab) return;
        document.querySelectorAll('#sideFilterSide .st-filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        state.filterSide = tab.dataset.filter;
        applyFilters();
    });

    function applyFilters() {
        const { filterSide, searchQuery } = state;
        pool.querySelectorAll('.st-guest-card').forEach(card => {
            const side = card.dataset.side;
            const name = card.dataset.name.toLowerCase();
            const show = (filterSide === 'all' || side === filterSide)
                      && (!searchQuery || name.includes(searchQuery));
            card.style.display = show ? '' : 'none';
        });

        const empty = pool.querySelector('.st-guest-list__empty');
        if (empty) {
            const hasVisible = [...pool.querySelectorAll('.st-guest-card')].some(c => c.style.display !== 'none');
            empty.style.display = hasVisible ? 'none' : '';
        }
    }

    /* ================================================================
       初期化
    ================================================================ */
    const totalEl = document.getElementById('summaryTotal');
    if (totalEl) totalEl.dataset.total = totalEl.textContent;

    // 初回スケール適用
    applyScale();
    updateProgress();

})();
