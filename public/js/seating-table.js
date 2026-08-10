/* ================================================================
   seating-table.js — 席次表管理（表形式エディタ）
   ================================================================ */
(function () {
    'use strict';

    const BASE = '/admin/seating';
    const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const TYPE_CFG = window.SEAT_TYPE_CONFIG ?? {};
    let ALL_GUESTS = window.ALL_GUESTS ?? [];
    const TOTAL_GUESTS = window.SUMMARY_TOTAL_GUESTS ?? 0;

    const body            = document.getElementById('sxBody');
    const unassignedList  = document.getElementById('unassignedList');
    const unassignedCount = document.getElementById('unassignedCount');
    const progressFill    = document.getElementById('progressFill');
    const progressLabel   = document.getElementById('progressLabel');
    const saveStatus      = document.getElementById('saveStatus');
    if (!body) return;

    let assignedCount = TOTAL_GUESTS - (ALL_GUESTS.filter(g => !g.table).length);

    /* ── 通信 ───────────────────────────────────────────────── */
    async function api(method, url, data) {
        setSaving();
        try {
            const res = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                },
                body: data ? JSON.stringify(data) : undefined,
            });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.error || '通信に失敗しました');
            setSaved();
            return json;
        } catch (e) {
            setError();
            alert(e.message || '通信に失敗しました');
            throw e;
        }
    }

    function setSaving() {
        if (!saveStatus) return;
        saveStatus.className = 'sx-save-status is-saving';
        saveStatus.innerHTML = '<i class="fa-solid fa-rotate"></i>保存中...';
    }
    function setSaved() {
        if (!saveStatus) return;
        saveStatus.className = 'sx-save-status is-saved';
        saveStatus.innerHTML = '<i class="fa-solid fa-check"></i>自動保存済み';
    }
    function setError() {
        if (!saveStatus) return;
        saveStatus.className = 'sx-save-status is-error';
        saveStatus.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>保存に失敗しました';
    }

    /* ── 進捗表示 ───────────────────────────────────────────── */
    function updateProgress() {
        const pct = TOTAL_GUESTS > 0 ? Math.round((assignedCount / TOTAL_GUESTS) * 100) : 0;
        if (progressFill)  progressFill.style.width = pct + '%';
        if (progressLabel) progressLabel.textContent = `${assignedCount} / ${TOTAL_GUESTS} 名配置済み`;
        if (unassignedCount) unassignedCount.textContent = `${TOTAL_GUESTS - assignedCount}名`;
    }

    /* ── 未配置ゲストリスト ─────────────────────────────────── */
    const fpUnassignedList  = document.getElementById('fpUnassignedList');
    const fpUnassignedCount = document.getElementById('fpUnassignedCount');

    function renderUnassignedList() {
        const q = (document.getElementById('guestSearch')?.value ?? '').trim();
        const items = ALL_GUESTS.filter(g => !g.table && (!q || g.name.includes(q)));
        unassignedList.innerHTML = '';
        if (ALL_GUESTS.every(g => g.table)) {
            unassignedList.innerHTML = '<li class="sx-unassigned-list__empty">全員配置済みです</li>';
        } else if (items.length === 0) {
            unassignedList.innerHTML = '<li class="sx-unassigned-list__filtered-empty">該当なし</li>';
        } else {
            items.forEach(g => {
                const li = document.createElement('li');
                li.dataset.userId = g.id;
                li.dataset.guestName = g.name;
                li.title = 'クリックで最初の空席へ配置';
                li.innerHTML = `<span class="sx-unassigned-list__name"></span><span class="sx-unassigned-list__tag"></span><span class="sx-unassigned-list__action">配置</span>`;
                li.querySelector('.sx-unassigned-list__name').textContent = g.name;
                li.querySelector('.sx-unassigned-list__tag').textContent = g.tag;
                unassignedList.appendChild(li);
            });
        }
        renderFpUnassignedList();
    }

    function renderFpUnassignedList() {
        if (!fpUnassignedList) return;
        const unassigned = ALL_GUESTS.filter(g => !g.table);
        if (fpUnassignedCount) fpUnassignedCount.textContent = `${unassigned.length}名`;
        fpUnassignedList.innerHTML = '';
        if (unassigned.length === 0) {
            fpUnassignedList.innerHTML = '<li class="sx-unassigned-list__empty">全員配置済みです</li>';
            return;
        }
        unassigned.forEach(g => {
            const li = document.createElement('li');
            li.dataset.userId = g.id;
            li.dataset.guestName = g.name;
            li.draggable = true;
            li.title = 'ドラッグしてテーブルに配置';
            li.innerHTML = `<span class="sx-unassigned-list__name"></span><span class="sx-unassigned-list__tag"></span>`;
            li.querySelector('.sx-unassigned-list__name').textContent = g.name;
            li.querySelector('.sx-unassigned-list__tag').textContent = g.tag;
            fpUnassignedList.appendChild(li);
        });
    }

    document.getElementById('guestSearch')?.addEventListener('input', renderUnassignedList);

    async function assignUnassignedGuest(userId) {
        const emptySeat = body.querySelector('select.sx-guest[data-current-user=""]');
        if (!emptySeat) {
            alert('空席がありません。先に「+席」で席を追加してください。');
            return;
        }
        emptySeat.value = userId;
        emptySeat.dispatchEvent(new Event('change', { bubbles: true }));
        emptySeat.closest('tr')?.classList.add('sx-row--just-updated');
        emptySeat.closest('tr')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => emptySeat.closest('tr')?.classList.remove('sx-row--just-updated'), 1400);
    }

    async function assignGuestToSeat(userId, seatId) {
        const sel = body.querySelector(`select.sx-guest[data-seat-id="${seatId}"]`);
        if (!sel) return;
        sel.value = userId;
        sel.dispatchEvent(new Event('change', { bubbles: true }));
        sel.closest('tr')?.classList.add('sx-row--just-updated');
        setTimeout(() => sel.closest('tr')?.classList.remove('sx-row--just-updated'), 1400);
    }

    async function unassignSeat(seatId) {
        const sel = body.querySelector(`select.sx-guest[data-seat-id="${seatId}"]`);
        if (!sel || !sel.dataset.currentUser) return;
        sel.value = '';
        sel.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function fpSeatHtml(seatId, tableId, guestName) {
        const occupied = !!guestName;
        return `<div class="sxp-seat sx-fp-seat ${occupied ? 'is-occupied' : 'is-empty'}" data-seat-id="${seatId}" data-table-id="${tableId}">
            <div class="sxp-seat__body"><span class="sxp-seat__name">${escapeHtml(guestName || '空席')}</span></div>
        </div>`;
    }

    // 座席の追加・削除で左右レールの振り分けが変わるため、シートマップは毎回まるごと再構築する
    function syncFloorplanTable(tableId) {
        const fpTable = document.querySelector(`.sx-fp-table[data-table-id="${tableId}"]`);
        if (!fpTable) return;
        const seatSelects = Array.from(body.querySelectorAll(`tr[data-table-id="${tableId}"] select.sx-guest`));
        const total = seatSelects.length;
        const occupied = seatSelects.filter(s => s.dataset.currentUser).length;
        const countEl = fpTable.querySelector('.sx-fp-table__count');
        if (countEl) countEl.textContent = `${occupied}/${total}`;
        fpTable.classList.toggle('is-full', total > 0 && occupied >= total);

        const seatData = seatSelects.map(sel => {
            const userId = sel.dataset.currentUser || '';
            const guest = userId ? ALL_GUESTS.find(g => String(g.id) === String(userId)) : null;
            return { seatId: sel.dataset.seatId, name: guest ? guest.name : '' };
        });
        const leftCount = Math.ceil(Math.max(seatData.length, 1) / 2);
        const leftSeats = seatData.slice(0, leftCount);
        const rightSeats = seatData.slice(leftCount);

        const leftRail = fpTable.querySelector('.sxp-seat-rail--left');
        const rightRail = fpTable.querySelector('.sxp-seat-rail--right');
        if (leftRail) leftRail.innerHTML = leftSeats.map(s => fpSeatHtml(s.seatId, tableId, s.name)).join('');
        if (rightRail) rightRail.innerHTML = rightSeats.map(s => fpSeatHtml(s.seatId, tableId, s.name)).join('');
    }

    unassignedList?.addEventListener('click', (e) => {
        const item = e.target.closest('li[data-user-id]');
        if (!item) return;
        assignUnassignedGuest(item.dataset.userId);
    });

    fpUnassignedList?.addEventListener('dragstart', (e) => {
        const item = e.target.closest('li[data-user-id]');
        if (!item) return;
        e.dataTransfer.setData('text/plain', 'guest:' + item.dataset.userId);
        e.dataTransfer.effectAllowed = 'move';
    });


    function guestOptionsHtml(selectedId) {
        let html = `<option value="">— 未配置 —</option>`;
        ALL_GUESTS.forEach(g => {
            const selected = String(g.id) === String(selectedId) ? 'selected' : '';
            const suffix = (g.table && String(g.id) !== String(selectedId)) ? `（${g.table}に配置中）` : '';
            html += `<option value="${g.id}" ${selected}>${escapeHtml(g.name)}${escapeHtml(suffix)}</option>`;
        });
        return html;
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    }

    function refreshAllGuestSelects() {
        body.querySelectorAll('select.sx-guest').forEach(sel => {
            const current = sel.dataset.currentUser || '';
            sel.innerHTML = guestOptionsHtml(current);
        });
    }

    /* ── 席タイプ変更 ───────────────────────────────────────── */
    body.addEventListener('change', async (e) => {
        const sel = e.target.closest('select.sx-type');
        if (!sel) return;
        await api('PATCH', `${BASE}/seats/${sel.dataset.seatId}`, { type: sel.value });
    });

    /* ── ゲスト割り当て変更 ─────────────────────────────────── */
    body.addEventListener('change', async (e) => {
        const sel = e.target.closest('select.sx-guest');
        if (!sel) return;
        const seatId  = sel.dataset.seatId;
        const newUser = sel.value;
        const prevUser = sel.dataset.currentUser || '';
        const row = sel.closest('tr');
        const tableId = row?.dataset.tableId;
        const tableName = row?.closest('table')?.querySelector(`.sx-table-name[data-table-id="${tableId}"]`)?.value;

        try {
            if (newUser === '') {
                if (prevUser) await api('DELETE', `${BASE}/unassign/${prevUser}`);
            } else {
                await api('POST', `${BASE}/assign`, { seat_id: seatId, user_id: newUser });
            }
        } catch {
            sel.value = prevUser; // ロールバック
            return;
        }

        // 以前このゲストが別の席にいた場合、その席のセレクトを未配置に戻す
        const affectedTableIds = new Set([tableId]);
        if (newUser) {
            body.querySelectorAll(`select.sx-guest[data-current-user="${newUser}"]`).forEach(other => {
                if (other !== sel) {
                    other.dataset.currentUser = '';
                    other.value = '';
                    const otherTableId = other.closest('tr')?.dataset.tableId;
                    if (otherTableId) affectedTableIds.add(otherTableId);
                }
            });
        }
        // 直前にこの席にいたゲストは未配置に戻る
        sel.dataset.currentUser = newUser;

        // ローカルの ALL_GUESTS 状態を同期
        ALL_GUESTS.forEach(g => {
            if (String(g.id) === String(newUser)) g.table = tableName || '';
            else if (String(g.id) === String(prevUser)) g.table = null;
        });

        assignedCount = ALL_GUESTS.filter(g => g.table).length;
        updateProgress();
        renderUnassignedList();
        refreshAllGuestSelects();
        // refreshAllGuestSelects で value がリセットされるため選択状態を再適用
        body.querySelectorAll('select.sx-guest').forEach(s => { s.value = s.dataset.currentUser || ''; });
        affectedTableIds.forEach(id => syncFloorplanTable(id));
    });

    /* ── 席を削除 ───────────────────────────────────────────── */
    body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.sx-del-seat');
        if (!btn) return;
        if (!confirm('この席を削除しますか？配置されているゲストは未配置に戻ります。')) return;

        const seatId = btn.dataset.seatId;
        const row = btn.closest('tr');
        const tableId = row.dataset.tableId;
        const guestSel = row.querySelector('select.sx-guest');
        const freedUserId = guestSel?.dataset.currentUser;

        await api('DELETE', `${BASE}/seats/${seatId}`);

        if (freedUserId) {
            ALL_GUESTS.forEach(g => { if (String(g.id) === String(freedUserId)) g.table = null; });
            assignedCount = ALL_GUESTS.filter(g => g.table).length;
        }

        removeSeatRow(row, tableId);
        updateProgress();
        renderUnassignedList();
        syncFloorplanTable(tableId);
    });

    function removeSeatRow(row, tableId) {
        const groupRows = Array.from(body.querySelectorAll(`tr[data-table-id="${tableId}"]`));
        const remaining = groupRows.filter(r => r !== row);
        const hadMergeCell = row.querySelector('.sx-cell-table');
        const anchor = row.nextElementSibling; // 削除後の挿入位置の目印

        if (remaining.length === 0) {
            // 最後の1席だった → 同じ位置に空テーブル行を差し込む
            const tableCellHtml = hadMergeCell ? hadMergeCell.innerHTML : '';
            const emptyRow = document.createElement('tr');
            emptyRow.dataset.tableId = tableId;
            emptyRow.className = 'sx-row sx-row--first';
            emptyRow.innerHTML = `
                <td class="sx-cell-table" rowspan="1" data-table-id="${tableId}">${tableCellHtml}</td>
                <td class="sx-cell-empty" colspan="4">席がありません。「+席」で追加してください</td>`;
            if (anchor) body.insertBefore(emptyRow, anchor); else body.appendChild(emptyRow);
            row.remove();
            return;
        }

        if (hadMergeCell) {
            // マージセルを次の行へ移す
            const next = remaining[0];
            next.classList.add('sx-row--first');
            const td = hadMergeCell.cloneNode(true);
            td.rowSpan = remaining.length;
            next.insertBefore(td, next.firstChild);
        } else {
            const mergeCell = document.querySelector(`.sx-cell-table[data-table-id="${tableId}"]`);
            if (mergeCell) mergeCell.rowSpan = remaining.length;
        }
        row.remove();
    }

    /* ── 席を追加 ───────────────────────────────────────────── */
    body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.sx-add-seat');
        if (!btn) return;
        const tableId = btn.dataset.tableId;
        const groupRows = Array.from(body.querySelectorAll(`tr[data-table-id="${tableId}"]`));
        if (groupRows.length >= 8) { alert('1テーブルの最大席数は8席です'); return; }

        const json = await api('POST', `${BASE}/tables/${tableId}/seats`, { type: 'normal' });
        const seat = json.seat;
        const seatIndex = groupRows.length + 1;

        const isEmptyState = groupRows.length === 1 && groupRows[0].querySelector('.sx-cell-empty');

        const newRow = document.createElement('tr');
        newRow.dataset.seatId = seat.id;
        newRow.dataset.tableId = tableId;
        newRow.className = 'sx-row';
        newRow.innerHTML = `
            <td class="sx-cell-num">${isEmptyState ? 1 : seatIndex}</td>
            <td class="sx-cell-type"><select class="sx-type" data-seat-id="${seat.id}">${typeOptionsHtml('normal')}</select></td>
            <td class="sx-cell-guest"><div class="sx-guest-wrap"><select class="sx-guest" data-seat-id="${seat.id}" data-current-user="">${guestOptionsHtml('')}</select></div></td>
            <td class="sx-cell-action"><button type="button" class="sx-del-seat" data-seat-id="${seat.id}" title="この席を削除"><i class="fa-solid fa-xmark"></i></button></td>`;

        if (isEmptyState) {
            const mergeCell = groupRows[0].querySelector('.sx-cell-table');
            mergeCell.rowSpan = 1;
            newRow.classList.add('sx-row--first');
            newRow.insertBefore(mergeCell.cloneNode(true), newRow.firstChild);
            groupRows[0].after(newRow);
            groupRows[0].remove();
        } else {
            const mergeCell = document.querySelector(`.sx-cell-table[data-table-id="${tableId}"]`);
            if (mergeCell) mergeCell.rowSpan = groupRows.length + 1;
            groupRows[groupRows.length - 1].after(newRow);
        }
        syncFloorplanTable(tableId);
    });

    function typeOptionsHtml(selected) {
        return Object.entries(TYPE_CFG).map(([key, cfg]) =>
            `<option value="${key}" ${key === selected ? 'selected' : ''}>${escapeHtml(cfg.label)}</option>`
        ).join('');
    }

    /* ── テーブル名の変更 ───────────────────────────────────── */
    body.addEventListener('change', async (e) => {
        const input = e.target.closest('.sx-table-name');
        if (!input) return;
        const name = input.value.trim();
        if (!name) { input.value = input.defaultValue; return; }
        await api('PATCH', `${BASE}/tables/${input.dataset.tableId}`, { name });
        input.defaultValue = name;

        // このテーブルに配置中のゲストの table 名を更新し、他セレクトの「配置中」表示に反映
        const tableId = input.dataset.tableId;
        const seatedUserIds = Array.from(body.querySelectorAll(`tr[data-table-id="${tableId}"] select.sx-guest`))
            .map(sel => sel.dataset.currentUser)
            .filter(Boolean);
        ALL_GUESTS.forEach(g => {
            if (seatedUserIds.includes(String(g.id))) g.table = name;
        });
        refreshAllGuestSelects();
        body.querySelectorAll('select.sx-guest').forEach(s => { s.value = s.dataset.currentUser || ''; });
    });

    /* ── テーブルを削除 ─────────────────────────────────────── */
    body.addEventListener('click', async (e) => {
        const btn = e.target.closest('.sx-del-table');
        if (!btn) return;
        const tableId = btn.dataset.tableId;
        if (!confirm('このテーブルを削除しますか？配置されているゲストは全員未配置に戻ります。')) return;

        const groupRows = Array.from(body.querySelectorAll(`tr[data-table-id="${tableId}"]`));
        const freedUserIds = groupRows
            .map(r => r.querySelector('select.sx-guest')?.dataset.currentUser)
            .filter(Boolean);

        await api('DELETE', `${BASE}/tables/${tableId}`);

        freedUserIds.forEach(uid => {
            ALL_GUESTS.forEach(g => { if (String(g.id) === String(uid)) g.table = null; });
        });
        assignedCount = ALL_GUESTS.filter(g => g.table).length;

        groupRows.forEach(r => r.remove());
        updateProgress();
        renderUnassignedList();

        const remainingTables = body.querySelectorAll('tr').length;
        if (remainingTables === 0) {
            const main = document.querySelector('.sx-main');
            if (main && !main.querySelector('.sx-no-tables')) {
                const p = document.createElement('p');
                p.className = 'sx-no-tables';
                p.textContent = 'テーブルがまだありません。下のフォームから追加してください';
                document.getElementById('sxTable').after(p);
            }
        }
    });

    /* ── テーブルを追加(表側の作成結果を表の行として挿入。配置図側のドラッグ作成と共用) ── */
    function insertTableIntoListView(table, seats) {
        document.querySelector('.sx-no-tables')?.remove();

        if (seats.length === 0) {
            const row = document.createElement('tr');
            row.dataset.tableId = table.id;
            row.className = 'sx-row sx-row--first';
            row.innerHTML = `
                <td class="sx-cell-table" rowspan="1" data-table-id="${table.id}">
                    ${tableCellHtml(table)}
                </td>
                <td class="sx-cell-empty" colspan="4">席がありません。「+席」で追加してください</td>`;
            body.appendChild(row);
        } else {
            seats.forEach((seat, i) => {
                const row = document.createElement('tr');
                row.dataset.seatId = seat.id;
                row.dataset.tableId = table.id;
                row.className = 'sx-row' + (i === 0 ? ' sx-row--first' : '');
                let html = '';
                if (i === 0) {
                    html += `<td class="sx-cell-table" rowspan="${seats.length}" data-table-id="${table.id}">${tableCellHtml(table)}</td>`;
                }
                html += `
                    <td class="sx-cell-num">${i + 1}</td>
                    <td class="sx-cell-type"><select class="sx-type" data-seat-id="${seat.id}">${typeOptionsHtml('normal')}</select></td>
                    <td class="sx-cell-guest"><div class="sx-guest-wrap"><select class="sx-guest" data-seat-id="${seat.id}" data-current-user="">${guestOptionsHtml('')}</select></div></td>
                    <td class="sx-cell-action"><button type="button" class="sx-del-seat" data-seat-id="${seat.id}" title="この席を削除"><i class="fa-solid fa-xmark"></i></button></td>`;
                row.innerHTML = html;
                body.appendChild(row);
            });
        }
    }

    document.getElementById('addTableBtn')?.addEventListener('click', async () => {
        const nameInput = document.getElementById('newTableName');
        const seatsInput = document.getElementById('newTableSeats');
        const name = nameInput.value.trim();
        if (!name) { alert('テーブル名を入力してください'); return; }
        const seatCount = Math.max(0, Math.min(8, parseInt(seatsInput.value, 10) || 0));

        const json = await api('POST', `${BASE}/tables`, { name, seat_count: seatCount });
        insertTableIntoListView(json.table, json.seats || []);
        appendFloorplanTable(json.table, json.seats || []);

        nameInput.value = '';
        seatsInput.value = 4;
    });

    function tableCellHtml(table) {
        return `<input type="text" class="sx-table-name" data-table-id="${table.id}" value="${escapeHtml(table.name)}" maxlength="50">
            <div class="sx-table-actions">
                <button type="button" class="sx-add-seat" data-table-id="${table.id}" title="席を追加"><i class="fa-solid fa-plus"></i>席</button>
                <button type="button" class="sx-del-table" data-table-id="${table.id}" title="テーブルを削除"><i class="fa-solid fa-trash"></i></button>
            </div>`;
    }

    /* ── ゲスト詳細情報モーダル ─────────────────────────────── */
    function openGuestDetail(btn) {
        const d = btn.dataset;
        document.getElementById('gdName').textContent         = d.guestName || '—';
        document.getElementById('gdTable').textContent        = d.tableName || '—';
        document.getElementById('gdCount').textContent        = d.guestCount ? d.guestCount + '名' : '—';
        document.getElementById('gdChildren').textContent     = d.guestChildren ? d.guestChildren + '名' : '—';
        document.getElementById('gdSide').textContent         = d.guestSide || '—';
        document.getElementById('gdRel').textContent          = d.guestRel || '—';
        document.getElementById('gdRelDetail').textContent    = d.guestRelDetail || '—';
        document.getElementById('gdAllergy').textContent      = d.guestAllergy || '—';
        document.getElementById('gdAllergyNotes').textContent = d.guestAllergyNotes || '—';
        document.getElementById('gdPhone').textContent        = d.guestPhone || '—';
        document.getElementById('gdPostal').textContent       = d.guestPostal || '—';
        document.getElementById('gdAddress').textContent      = d.guestAddress || '—';
        document.getElementById('gdNotes').textContent        = d.guestNotes || '—';

        const overlay = document.getElementById('guestDetailOverlay');
        overlay?.classList.add('is-open');
        overlay?.setAttribute('aria-hidden', 'false');
    }
    function closeGuestDetail() {
        const overlay = document.getElementById('guestDetailOverlay');
        overlay?.classList.remove('is-open');
        overlay?.setAttribute('aria-hidden', 'true');
    }

    body.addEventListener('click', (e) => {
        const btn = e.target.closest('.sx-guest-detail');
        if (btn) openGuestDetail(btn);
    });
    document.getElementById('guestDetailClose')?.addEventListener('click', closeGuestDetail);
    document.getElementById('guestDetailOverlay')?.addEventListener('click', (e) => {
        if (e.target.id === 'guestDetailOverlay') closeGuestDetail();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeGuestDetail();
    });

    /* ── 表示切り替え（表 / 配置図） ─────────────────────────── */
    const viewTable     = document.getElementById('sxViewTable');
    const viewFloorplan = document.getElementById('sxViewFloorplan');
    document.getElementById('viewTabs')?.addEventListener('click', (e) => {
        const tab = e.target.closest('.sx-view-tab');
        if (!tab) return;
        document.querySelectorAll('.sx-view-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        const isFloorplan = tab.dataset.view === 'floorplan';
        if (viewTable)     viewTable.hidden = isFloorplan;
        if (viewFloorplan) viewFloorplan.hidden = !isFloorplan;
    });

    const fpRoom = document.getElementById('sxFpRoom');

    function tableMarkOf(name) {
        const trimmed = (name || '').trim();
        return trimmed ? trimmed.charAt(0) : 'T';
    }

    function appendFloorplanTable(table, seats) {
        if (!fpRoom) return;
        seats = seats || [];
        const el = document.createElement('div');
        el.className = 'sx-fp-table sxp-table-card';
        el.dataset.tableId = table.id;
        const leftCount = Math.ceil(Math.max(seats.length, 1) / 2);
        const leftSeats = seats.slice(0, leftCount);
        const rightSeats = seats.slice(leftCount);
        el.innerHTML = `
            <div class="sxp-table-label">${escapeHtml(tableMarkOf(table.name))}</div>
            <div class="sxp-table-content">
                <div class="sxp-table-card__head sx-fp-table__head">
                    <span class="sxp-table-card__name sx-fp-table__name">${escapeHtml(table.name)}</span>
                    <span class="sx-fp-table__count">0/${seats.length}</span>
                </div>
                <div class="sxp-seat-map">
                    <div class="sxp-seat-rail sxp-seat-rail--left">${leftSeats.map(s => fpSeatHtml(s.id, table.id, '')).join('')}</div>
                    <div class="sxp-seat-rail sxp-seat-rail--right">${rightSeats.map(s => fpSeatHtml(s.id, table.id, '')).join('')}</div>
                </div>
            </div>`;
        fpRoom.appendChild(el);
    }

    /* ── 配置図：新しいテーブルを追加 ─────────────────────── */
    document.getElementById('sxFpNewTable')?.addEventListener('click', async () => {
        const name = prompt('新しいテーブルの名前を入力してください（例: 友人1）');
        if (!name || !name.trim()) return;
        const seatCountInput = prompt('席数（0〜8、あとから変更できます）', '4');
        if (seatCountInput === null) return;
        const seatCount = Math.max(0, Math.min(8, parseInt(seatCountInput, 10) || 0));

        const json = await api('POST', `${BASE}/tables`, {
            name: name.trim(),
            seat_count: seatCount,
        });
        insertTableIntoListView(json.table, json.seats || []);
        appendFloorplanTable(json.table, json.seats || []);
    });

    /* ── 配置図：未配置ゲストをドラッグして座席に配置 ── */
    if (fpRoom) {
        fpRoom.addEventListener('dragenter', (e) => {
            const seatEl = e.target.closest('.sx-fp-seat.is-empty');
            if (seatEl) seatEl.classList.add('is-drop-target');
        });

        fpRoom.addEventListener('dragleave', (e) => {
            const seatEl = e.target.closest('.sx-fp-seat.is-empty');
            if (seatEl && !seatEl.contains(e.relatedTarget)) seatEl.classList.remove('is-drop-target');
        });

        fpRoom.addEventListener('drop', async (e) => {
            const seatEl = e.target.closest('.sx-fp-seat.is-empty');
            const data = e.dataTransfer.getData('text/plain');
            if (!seatEl) return;
            seatEl.classList.remove('is-drop-target');
            if (!data.startsWith('guest:')) return;
            e.preventDefault();
            const userId = data.slice('guest:'.length);
            await assignGuestToSeat(userId, seatEl.dataset.seatId);
        });

        fpRoom.addEventListener('click', (e) => {
            const seatEl = e.target.closest('.sx-fp-seat.is-occupied');
            if (!seatEl) return;
            const name = seatEl.textContent.trim();
            if (!confirm(`${name} さんを未配置に戻しますか？`)) return;
            unassignSeat(seatEl.dataset.seatId);
        });
    }

    updateProgress();
})();
