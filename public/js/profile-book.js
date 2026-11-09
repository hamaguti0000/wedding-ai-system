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
        // スマホの狭い画面でも見開き(2ページ分)が収まるよう、最小サイズを
        // 1ページあたり140px程度まで縮められるようにしておく。
        minWidth: 140,
        maxWidth: 900,
        minHeight: 196,
        maxHeight: 1200,
        // 表紙(1ページ目)だけを単独表示にし、そこから見開きが始まるようにする。
        // false だと最初から1・2ページ目が横並びになり、本というより資料めくりに見える。
        showCover: true,
        maxShadowOpacity: 0.5,
        // 軽快にめくれるよう、デフォルト(1000ms)より短めに設定
        flippingTime: 500,
        // false固定: スマホでも常に見開き(2ページ)表示にする。
        // true(デフォルト)だと画面が狭い時に自動で1ページ表示に切り替わる。
        usePortrait: false,
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
