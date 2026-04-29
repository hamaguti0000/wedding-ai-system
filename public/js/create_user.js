document.addEventListener("DOMContentLoaded", () => {
    const slider = document.querySelector(".form-slider");
    const steps = document.querySelectorAll(".form-step");
    const nextBtns = document.querySelectorAll(".next-btn");
    const prevBtns = document.querySelectorAll(".prev-btn");
    const form = document.getElementById("registerForm");
    // サーバー側バリデーションエラー後に正しいSTEPを復元
    const startStep = parseInt(slider.dataset.startStep ?? '0', 10);
    let currentStep = (startStep >= 0 && startStep < steps.length) ? startStep : 0;

    // プログレスバー更新
    const fillEl = document.getElementById('stepFill');
    const dotEls = document.querySelectorAll('.step-progress__dot');
    function updateProgress(step) {
        const pct = steps.length <= 1 ? 100 : Math.round((step / (steps.length - 1)) * 100);
        if (fillEl) fillEl.style.width = pct + '%';
        dotEls.forEach((dot, i) => {
            dot.classList.toggle('step-progress__dot--active', i <= step);
        });
    }

    function updateSlider() {
        slider.style.transform = `translateX(-${currentStep * 100}%)`;
        updateProgress(currentStep);

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
                const btn = activeStep.querySelector("button.next-btn, button[type='submit']");
                if (btn) btn.click();
            }
        }
    });

    // 次へ
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

    // 戻る
    prevBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            if (currentStep > 0) {
                currentStep--;
                updateSlider();
            }
        });
    });

    updateSlider();
});