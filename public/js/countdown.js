(() => {
  const weddingDate = new Date("2026-07-19T00:00:00").getTime();
  const countdownEl = document.getElementById("countdown");

  if (!countdownEl) return; // countdown がないページでは何もしない

  function updateCountdown() {
    const now = new Date().getTime();
    const distance = weddingDate - now;

    if (distance <= 0) {
      countdownEl.innerHTML = "本日です！";
      clearInterval(interval);
      return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    countdownEl.innerHTML = `あと ${days}日 ${hours}時間 ${minutes}分 ${seconds}秒`;
  }

  const interval = setInterval(updateCountdown, 1000);
  updateCountdown();

  // スクロールで文字色を変更
  const imageSection = document.querySelector('.image-section');
  if (!imageSection) return; // 画像セクションがないページでは何もしない

  window.addEventListener('scroll', () => {
    const photoHeight = imageSection.offsetHeight;
    if (window.scrollY > photoHeight) {
      countdownEl.style.color = '#333';
      countdownEl.style.background = 'rgba(255,255,255,0.8)';
    } else {
      countdownEl.style.color = '#fff';
      countdownEl.style.background = 'rgba(0,0,0,0.3)';
    }
  });
})();
