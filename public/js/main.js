/* ================================================================
   main.js  —  全ページ共通スクリプト
   ================================================================ */

/* ── 1. ヘッダー スクロール挙動 ────────────────────────── */
(function () {
    const header = document.querySelector('.header');
    if (!header) return;

    const heroEl = document.querySelector('.home-hero, .inv-banner, .image-section');

    function updateHeader() {
        if (!heroEl) {
            header.classList.add('scrolled');
            return;
        }
        header.classList.toggle('scrolled', heroEl.getBoundingClientRect().bottom <= 60);
    }

    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });
})();

/* ── 2. モバイルドロワー ───────────────────────────────── */
(function () {
    const burger  = document.querySelector('.header__burger');
    const drawer  = document.getElementById('headerDrawer');
    const overlay = document.getElementById('headerOverlay');
    if (!burger || !drawer) return;

    // iOS 対応: body を fixed にしてスクロール位置を保持
    let scrollY = 0;

    function openDrawer() {
        scrollY = window.scrollY;
        burger.setAttribute('aria-expanded', 'true');
        burger.setAttribute('aria-label', 'メニューを閉じる');
        drawer.setAttribute('aria-hidden', 'false');
        drawer.classList.add('is-open');
        overlay?.classList.add('is-visible');
        // body を fixed にすることで背景スクロールを防ぐ（iOS 対応）
        document.body.style.position = 'fixed';
        document.body.style.top      = `-${scrollY}px`;
        document.body.style.width    = '100%';
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        burger.setAttribute('aria-expanded', 'false');
        burger.setAttribute('aria-label', 'メニューを開く');
        drawer.setAttribute('aria-hidden', 'true');
        drawer.classList.remove('is-open');
        overlay?.classList.remove('is-visible');
        // body を元に戻してスクロール位置を復元
        document.body.style.position = '';
        document.body.style.top      = '';
        document.body.style.width    = '';
        document.body.style.overflow = '';
        window.scrollTo(0, scrollY);
    }

    burger.addEventListener('click', () => {
        burger.getAttribute('aria-expanded') === 'true' ? closeDrawer() : openDrawer();
    });
    overlay?.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && burger.getAttribute('aria-expanded') === 'true') {
            closeDrawer();
            burger.focus();
        }
    });
    window.matchMedia('(min-width: 768px)')
        .addEventListener('change', e => { if (e.matches) closeDrawer(); });
})();

/* ── 3. ホームページ: ヒーローテキスト フェードイン ────── */
(function () {
    // CSS クラスで管理（インライン style 操作を排除）
    const text   = document.querySelector('.home-hero__text');
    const scroll = document.querySelector('.home-hero__scroll');
    if (!text) return;

    // 初期状態は CSS 側で opacity:0 を設定（home.css 参照）
    setTimeout(() => text.classList.add('is-visible'), 300);
    if (scroll) setTimeout(() => scroll.classList.add('is-visible'), 1800);
})();

/* ── 4. CSRF トークンリフレッシュ（セッション維持ハートビート）── */
(function () {
    const metaToken = document.querySelector('meta[name="csrf-token"]');
    if (!metaToken) return; // layouts.app.blade.php 経由のページのみ実行

    const INTERVAL = 10 * 60 * 1000; // 10分ごと

    async function refreshCsrf() {
        try {
            const r = await fetch('/csrf-refresh', { credentials: 'same-origin' });
            if (!r.ok) return;
            const { token } = await r.json();
            if (!token) return;
            metaToken.setAttribute('content', token);
            // ページ内の全 hidden _token を更新
            document.querySelectorAll('input[name="_token"]')
                .forEach(el => { el.value = token; });
        } catch (_) { /* ネットワークエラーはサイレント無視 */ }
    }

    setInterval(refreshCsrf, INTERVAL);
})();

/* ── 4. ライブ検索フォーム ─────────────────────────────── */
(function () {
    const forms = document.querySelectorAll('form[data-live-search-form]');
    if (!forms.length) return;

    forms.forEach(form => {
        const delay = Math.max(0, parseInt(form.getAttribute('data-live-search-delay') || '300', 10) || 300);
        const targets = (form.getAttribute('data-live-search-target') || '')
            .split(',')
            .map(s => s.trim())
            .filter(Boolean);
        let controller = null;
        let timer = null;

        const schedule = (ms = delay) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                if (!targets.length) return;

                const url = new URL(form.action, window.location.origin);
                const params = new URLSearchParams(new FormData(form));
                url.search = params.toString();

                if (controller) {
                    controller.abort();
                }
                controller = new AbortController();

                fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                    signal: controller.signal,
                    credentials: 'same-origin',
                })
                    .then(resp => resp.text())
                    .then(html => {
                        const doc = new DOMParser().parseFromString(html, 'text/html');
                        targets.forEach(selector => {
                            const current = document.querySelector(selector);
                            const next = doc.querySelector(selector);
                            if (!current) return;
                            if (!next) {
                                current.remove();
                                return;
                            }
                            current.outerHTML = next.outerHTML;
                        });
                        history.replaceState(null, '', url.pathname + (url.search ? `?${url.searchParams.toString()}` : ''));
                    })
                    .catch(err => {
                        if (err?.name !== 'AbortError') return;
                    });
            }, ms);
        };

        form.querySelectorAll('input[type="search"], input[type="text"], input[type="date"], input[type="radio"], input[type="checkbox"], select, textarea')
            .forEach(control => {
                const type = (control.getAttribute('type') || '').toLowerCase();
                if (type === 'search' || type === 'text' || control.tagName === 'TEXTAREA') {
                    control.addEventListener('input', () => schedule());
                    return;
                }
                control.addEventListener('change', () => schedule(0));
            });

        form.addEventListener('submit', () => {
            clearTimeout(timer);
        });
    });
})();

/* ── 5. アバター設定フォーム ────────────────────────── */
(function () {
    const fields = document.querySelectorAll('[data-avatar-settings]');
    if (!fields.length) return;

    fields.forEach(field => {
        const preview = field.querySelector('[data-avatar-preview]');
        const typeInputs = [...field.querySelectorAll('input[name="avatar_type"]')];
        const emojiInputs = [...field.querySelectorAll('input[name="avatar_emoji"]')];
        const categoryTabs = [...field.querySelectorAll('[data-avatar-emoji-category-tab]')];
        const categoryPanels = [...field.querySelectorAll('[data-avatar-emoji-category-panel]')];
        const colorInput = field.querySelector('input[name="avatar_bg_color"]');
        const colorSwatches = [...field.querySelectorAll('.avatar-color-swatch[data-avatar-bg-color]')];
        const borderInput = field.querySelector('input[name="avatar_border_color"]');
        const borderSwatches = [...field.querySelectorAll('.avatar-color-swatch[data-avatar-border-color]')];
        const borderWidthInput = field.querySelector('input[name="avatar_border_width"]');
        const borderWidthValue = field.querySelector('[data-avatar-border-width-value]');
        const colorPanel = field.querySelector('[data-avatar-color-panel]');
        const emojiPanel = field.querySelector('[data-avatar-emoji-panel]');
        const photoPanel = field.querySelector('[data-avatar-photo-panel]');
        const photoInput = field.querySelector('input[name="avatar_image"]');
        const initial = field.getAttribute('data-avatar-initial') || '?';
        const state = {
            type: field.getAttribute('data-avatar-type') || 'initial',
            emoji: field.getAttribute('data-avatar-emoji') || '',
            emojiCategory: field.getAttribute('data-avatar-emoji-category') || '',
            bgColor: field.getAttribute('data-avatar-bg-color') || '#ffffff',
            borderColor: field.getAttribute('data-avatar-border-color') || '#f0e4d0',
            borderWidth: field.getAttribute('data-avatar-border-width') || '3',
            image: field.getAttribute('data-avatar-image') || '',
        };

        const render = () => {
            if (!preview) return;
            preview.style.setProperty('--avatar-border-color', state.borderColor || '#f0e4d0');
            preview.style.setProperty('--avatar-border-width', `${state.borderWidth || 3}px`);

            if (state.type === 'photo' && state.image) {
                preview.style.background = '';
                preview.innerHTML = `<img src="${state.image}" alt="">`;
                return;
            }

            if (state.type === 'emoji' && state.emoji) {
                preview.style.background = state.bgColor || '#ffffff';
                preview.innerHTML = `<span class="avatar-preview__circle-emoji">${state.emoji}</span>`;
                return;
            }

            preview.style.background = '';
            preview.innerHTML = `<span>${initial}</span>`;
        };

        const syncPanels = () => {
            if (emojiPanel) emojiPanel.hidden = state.type !== 'emoji';
            if (photoPanel) photoPanel.hidden = state.type !== 'photo';
            if (colorPanel) colorPanel.hidden = state.type !== 'emoji';
        };

        const syncCategory = (category) => {
            if (!category) return;
            state.emojiCategory = category;
            categoryTabs.forEach(tab => {
                tab.classList.toggle('is-active', tab.getAttribute('data-avatar-emoji-category-tab') === category);
            });
            categoryPanels.forEach(panel => {
                const active = panel.getAttribute('data-avatar-emoji-category-panel') === category;
                panel.hidden = !active;
                panel.classList.toggle('is-active', active);
            });
        };

        const syncType = () => {
            const checked = typeInputs.find(input => input.checked);
            state.type = checked ? checked.value : 'initial';
            syncPanels();
            render();
        };

        const syncEmoji = () => {
            const checked = emojiInputs.find(input => input.checked);
            state.emoji = checked ? checked.value : '';
            const checkedCategory = checked ? checked.getAttribute('data-avatar-emoji-category') : '';
            if (checkedCategory) {
                syncCategory(checkedCategory);
            }
            render();
        };

        const syncColor = value => {
            if (!value) return;
            state.bgColor = value;
            if (colorInput) colorInput.value = value;
            colorSwatches.forEach(swatch => {
                swatch.classList.toggle('is-active', swatch.getAttribute('data-avatar-bg-color') === value);
            });
            render();
        };

        const syncBorderColor = value => {
            if (!value) return;
            state.borderColor = value;
            if (borderInput) borderInput.value = value;
            borderSwatches.forEach(swatch => {
                swatch.classList.toggle('is-active', swatch.getAttribute('data-avatar-border-color') === value);
            });
            render();
        };

        const syncBorderWidth = value => {
            if (value === null || value === undefined || value === '') return;
            state.borderWidth = String(Math.max(0, Math.min(10, parseInt(value, 10) || 0)));
            if (borderWidthInput) borderWidthInput.value = state.borderWidth;
            if (borderWidthValue) borderWidthValue.textContent = `${state.borderWidth}px`;
            render();
        };

        categoryTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                syncCategory(tab.getAttribute('data-avatar-emoji-category-tab') || '');
            });
        });
        typeInputs.forEach(input => input.addEventListener('change', syncType));
        emojiInputs.forEach(input => input.addEventListener('change', syncEmoji));
        if (colorInput) {
            colorInput.addEventListener('input', () => syncColor(colorInput.value));
        }
        colorSwatches.forEach(swatch => {
            swatch.addEventListener('click', () => syncColor(swatch.getAttribute('data-avatar-bg-color') || '#ffffff'));
        });
        if (borderInput) {
            borderInput.addEventListener('input', () => syncBorderColor(borderInput.value));
        }
        borderSwatches.forEach(swatch => {
            swatch.addEventListener('click', () => syncBorderColor(swatch.getAttribute('data-avatar-border-color') || '#f0e4d0'));
        });
        if (borderWidthInput) {
            borderWidthInput.addEventListener('input', () => syncBorderWidth(borderWidthInput.value));
        }

        if (photoInput) {
            photoInput.addEventListener('change', () => {
                const file = photoInput.files && photoInput.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = () => {
                    state.image = String(reader.result || '');
                    render();
                };
                reader.readAsDataURL(file);
            });
        }

        syncPanels();
        syncEmoji();
        syncColor(state.bgColor || '#ffffff');
        syncBorderColor(state.borderColor || '#f0e4d0');
        syncBorderWidth(state.borderWidth || '3');
        syncCategory(state.emojiCategory || categoryTabs[0]?.getAttribute('data-avatar-emoji-category-tab') || '');
        render();
    });
})();

/* ── 6. ホームページ: スクロールフェードイン (IntersectionObserver) ── */
(function () {
    // #top_info: CSS クラス .top-info-visible で opacity を制御
    const topInfo = document.querySelector('#top_info');
    if (!topInfo) return;

    const obs = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    topInfo.classList.add('top-info-visible');
                    obs.unobserve(topInfo);
                }
            });
        },
        { threshold: 0.15 }
    );
    obs.observe(topInfo);
})();
