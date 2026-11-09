$(function () {
  // meal-desc を文字単位で分割
  $(".meal-desc").each(function () {
    const text = $(this).text().trim();
    const html = text
      .split("")
      .map((ch) => `<span class="fade-char">${ch}</span>`)
      .join("");
    $(this).html(html);
  });

  function animateMeal() {
    $(".meal").each(function () {
      const $meal = $(this);
      const top = $meal.offset().top;
      const scroll = $(window).scrollTop();
      const windowH = $(window).height();

      // 「料理説明文の位置」で発火
      if (scroll + windowH * 0.85 > top && scroll < top + $meal.outerHeight()) {
        $meal.addClass("show");

        // 各meal-item（タイトル＋説明）もセットでアニメーション
        $meal.find(".meal-item").each(function () {
          const $item = $(this);
          $item.addClass("show");

          // 説明文の文字を1文字ずつフェードイン
          $item.find(".meal-desc .fade-char").each(function (i) {
            const $c = $(this);
            setTimeout(() => {
              $c.addClass("show");
            }, i * 30);
          });
        });
      } else {
        $meal.removeClass("show");
        $meal.find(".meal-item").removeClass("show");
        $meal.find(".meal-desc .fade-char").removeClass("show");
      }
    });
  }

  animateMeal();
  $(window).on("scroll resize", animateMeal);
});
