/* ================================================================
   invitation.js  —  RSVP フォーム
   ================================================================ */
(function () {
    'use strict';

    const STORAGE_KEY = 'wedding_rsvp_draft';

    const PERSIST_FIELDS = [
        'last_name', 'first_name', 'furigana_sei', 'furigana_mei',
        'phone', 'postal_code', 'address',
        'attending_count', 'children_count',
        'allergy_notes', 'notes', 'relationship_detail',
    ];

    const form            = document.getElementById('rsvpForm');
    const participationEl = document.getElementById('participation');

    if (!form) return;

    /* ── localStorage ──────────────────────────────────────── */

    function saveDraft() {
        if (!window.localStorage) return;
        const draft = {
            participation:        participationEl?.value           ?? '',
            guest_side:           getHiddenVal('guestSide'),
            relationship:         getHiddenVal('relationship'),
            has_allergy:          getHiddenVal('hasAllergy'),
        };
        PERSIST_FIELDS.forEach(name => {
            const el = form.querySelector(`[name="${name}"]`);
            if (el) draft[name] = el.value;
        });
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(draft)); } catch (_) {}
    }

    function loadDraft() {
        try { return JSON.parse(localStorage.getItem(STORAGE_KEY) ?? 'null'); }
        catch (_) { return null; }
    }

    function clearDraft() {
        try { localStorage.removeItem(STORAGE_KEY); } catch (_) {}
    }

    function getHiddenVal(id) {
        return document.getElementById(id)?.value ?? '';
    }

    if (document.querySelector('.inv-alert--success')) clearDraft();

    function restoreDraft() {
        if (form.dataset.restoreDraft !== '1') return;
        const draft = loadDraft();
        if (!draft) return;

        PERSIST_FIELDS.forEach(name => {
            if (!draft[name]) return;
            const el = form.querySelector(`[name="${name}"]`);
            if (el) el.value = draft[name];
        });

        if (draft.participation) {
            participationEl.value = draft.participation;
            applyParticipation(draft.participation, false);
        }
        if (draft.guest_side)    applyBtnGroup('guestSide',    draft.guest_side,   false);
        if (draft.relationship) {
            applyBtnGroup('relationship', draft.relationship, false);
            toggleConditional('relationshipDetail', draft.relationship === 'other', false);
        }
        if (draft.has_allergy !== undefined) {
            applyBtnGroup('hasAllergy', draft.has_allergy, false);
            toggleConditional('allergyDetail', draft.has_allergy === '1', false);
        }
    }

    /* ── 汎用ボタングループ ────────────────────────────────── */

    // hiddenId に紐づくボタン群の選択状態を更新する
    function applyBtnGroup(hiddenId, value, animate = true) {
        const hiddenEl = document.getElementById(hiddenId);
        if (!hiddenEl) return;
        hiddenEl.value = value;

        form.querySelectorAll(`[data-target="${hiddenId}"]`).forEach(btn => {
            btn.classList.toggle('selected', btn.dataset.value === String(value));
        });
    }

    /* ── 条件付き表示 ──────────────────────────────────────── */

    function toggleConditional(id, show, animate = true) {
        const el = document.getElementById(id);
        if (!el) return;
        if (!animate) {
            el.style.transition = 'none';
            el.classList.toggle('visible', show);
            requestAnimationFrame(() => { el.style.transition = ''; });
        } else {
            el.classList.toggle('visible', show);
        }
    }

    /* ── 出欠 UI ────────────────────────────────────────────── */

    function applyParticipation(value, animate = true) {
        // 出欠ボタン
        form.querySelectorAll('.inv-part-btn').forEach(btn => {
            const match = btn.dataset.value === value;
            btn.classList.toggle('selected', match);
            btn.classList.toggle('attending', match && value === 'attending');
            btn.classList.toggle('declining', match && value === 'declining');
        });

        // 出席時のみ表示セクション（複数の inv-section-toggle を一括制御）
        document.querySelectorAll('.inv-section-toggle').forEach(el => {
            if (!animate) {
                el.style.transition = 'none';
                el.classList.toggle('hidden', value !== 'attending');
                requestAnimationFrame(() => { el.style.transition = ''; });
            } else {
                el.classList.toggle('hidden', value !== 'attending');
            }
        });

        // 出席人数の required 制御
        const countEl = document.getElementById('attendingCount');
        if (countEl) countEl.required = (value === 'attending');
    }

    /* ── エラー UI ─────────────────────────────────────────── */

    function applyErrorStyles() {
        form.querySelectorAll('.field-error').forEach(errEl => {
            errEl.closest('.form-group')?.classList.add('has-error');
        });
        form.querySelectorAll('.inv-card').forEach(card => {
            if (card.querySelector('.field-error')) {
                card.querySelector('.inv-card__head')
                    ?.classList.add('inv-card__head--has-error');
            }
        });
    }

    /* ── 初期化 ────────────────────────────────────────────── */

    restoreDraft();
    applyErrorStyles();
    applyParticipation(participationEl?.value || 'pending', false);

    // サーバー値での条件付き表示を初期適用
    toggleConditional('relationshipDetail', getHiddenVal('relationship') === 'other', false);
    toggleConditional('allergyDetail',       getHiddenVal('hasAllergy')  === '1',     false);

    /* ── イベント ──────────────────────────────────────────── */

    // 出欠ボタン
    form.querySelectorAll('.inv-part-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            participationEl.value = btn.dataset.value;
            applyParticipation(btn.dataset.value, true);
            saveDraft();
        });
    });

    // 汎用ボタングループ（data-target 属性で紐づけ）
    form.querySelectorAll('[data-target]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;
            const value    = btn.dataset.value;

            applyBtnGroup(targetId, value, true);

            // 依存する条件付き表示の制御
            if (targetId === 'relationship') {
                toggleConditional('relationshipDetail', value === 'other', true);
            }
            if (targetId === 'hasAllergy') {
                toggleConditional('allergyDetail', value === '1', true);
            }

            saveDraft();
        });
    });

    // デバウンス自動保存
    let saveTimer = null;
    form.addEventListener('input',  () => { clearTimeout(saveTimer); saveTimer = setTimeout(saveDraft, 350); });
    form.addEventListener('change', saveDraft);

})();
