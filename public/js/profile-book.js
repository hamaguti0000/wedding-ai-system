/* ================================================================
   profile-book.js — Kindleのような見開きページめくりビューア
   St.PageFlip (vendor/page-flip.browser.js) を利用
   ================================================================ */
(function () {
    'use strict';

    const el = document.getElementById('pbBook');
    if (!el || typeof St === 'undefined') return;

    let pages = [];
    try {
        pages = JSON.parse(el.dataset.pages || '[]');
    } catch (e) {
        return;
    }
    if (!pages.length) return;

    const pageFlip = new St.PageFlip(el, {
        width: 500,
        height: 700,
        size: 'stretch',
        minWidth: 280,
        maxWidth: 900,
        minHeight: 400,
        maxHeight: 1200,
        // 表紙(1ページ目)だけを単独表示にし、そこから見開きが始まるようにする。
        // false だと最初から1・2ページ目が横並びになり、本というより資料めくりに見える。
        showCover: true,
        maxShadowOpacity: 0.5,
        // 軽快にめくれるよう、デフォルト(1000ms)より短めに設定
        flippingTime: 500,
        usePortrait: true,
    });

    pageFlip.loadFromImages(pages);

    const indicator = document.getElementById('pbPageIndicator');
    function updateIndicator() {
        if (!indicator) return;
        indicator.textContent = `${pageFlip.getCurrentPageIndex() + 1} / ${pageFlip.getPageCount()}`;
    }
    pageFlip.on('flip', updateIndicator);
    updateIndicator();

    document.getElementById('pbPrev')?.addEventListener('click', () => pageFlip.flipPrev());
    document.getElementById('pbNext')?.addEventListener('click', () => pageFlip.flipNext());
})();
