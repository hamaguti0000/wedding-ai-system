document.addEventListener("DOMContentLoaded", () => {
  const slider = document.querySelector(".form-slider");
  const steps = document.querySelectorAll(".form-step");
  const nextBtns = document.querySelectorAll(".next-btn");
  const prevBtns = document.querySelectorAll(".prev-btn");
  const form = document.getElementById("registerForm");
  let currentStep = 0;

  function updateSlider() {
    slider.style.transform = `translateX(-${currentStep * 100}%)`;

    // tab操作制御
    steps.forEach((step, index) => {
      const inputs = step.querySelectorAll("input, select, textarea, button");
      if (index === currentStep) {
        inputs.forEach(el => el.removeAttribute("tabindex"));
      } else {
        inputs.forEach(el => el.setAttribute("tabindex", "-1"));
      }
    });
  }

  // Enterキー制御
  form.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      const activeStep = steps[currentStep];
      const isTextarea = e.target.tagName.toLowerCase() === "textarea";
      const isSubmitButton = e.target.type === "submit";

      if (!isTextarea && !isSubmitButton) {
        e.preventDefault();

        // 現在ステップの visible ボタン（次へ or 登録）を押す
        const btn = activeStep.querySelector("button.next-btn, button[type='submit']");
        if (btn) {
          btn.click();
        }
      }
    }
  });

  // 次へボタン
  nextBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      const inputs = steps[currentStep].querySelectorAll("input, select, textarea");
      for (let input of inputs) {
        if (!input.checkValidity()) {
          input.reportValidity();
          return;
        }
      }
      if (currentStep < steps.length - 1) {
        currentStep++;
        updateSlider();
      }
    });
  });

  // 戻るボタン
  prevBtns.forEach(btn => {
    btn.addEventListener("click", () => {
      if (currentStep > 0) {
        currentStep--;
        updateSlider();
      }
    });
  });

  // 登録ボタン
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const inputs = steps[currentStep].querySelectorAll("input, select, textarea");
    for (let input of inputs) {
      if (!input.checkValidity()) {
        input.reportValidity();
        return;
      }
    }

    // Ajax送信
    const formData = new FormData(form);
    try {
      const res = await fetch('create_user.php', {
        method: 'POST',
        body: formData
      });
      const result = await res.json();
      if (result.success) {
        alert('登録完了！');
        form.reset();
        currentStep = 0;
        updateSlider();
      } else {
        alert('エラー: ' + result.message);
      }
    } catch (err) {
      alert('通信エラーが発生しました');
    }
  });

  updateSlider();
});
