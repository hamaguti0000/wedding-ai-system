// 出欠ボタン
const attendBtn = document.querySelector(".attend");
const absentBtn = document.querySelector(".absent");
const participation = document.getElementById("participation");

attendBtn.addEventListener("click", () => {
  participation.value = "1";
  attendBtn.classList.add("selected");
  absentBtn.classList.remove("selected");
});
absentBtn.addEventListener("click", () => {
  participation.value = "0";
  absentBtn.classList.add("selected");
  attendBtn.classList.remove("selected");
});

// メッセージフェードイン
gsap.from("#message p", {
  duration: 1.2,
  opacity: 0,
  y: 50,
  stagger: 0.3,
  ease: "power2.out",
});

// 背景光アニメーション
const bg = document.getElementById("background");
gsap.to(bg, {
  backgroundPosition: "200% 200%",
  repeat: -1,
  yoyo: true,
  duration: 40,
  ease: "sine.inOut",
});
