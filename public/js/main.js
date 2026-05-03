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

/* ── 5. ホームページ: スクロールフェードイン (IntersectionObserver) ── */
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
