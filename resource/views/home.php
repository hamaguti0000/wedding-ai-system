<?php
require_once('com/include.php');
require_login();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kakeru & Mirai Wedding</title>

  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Noto+Sans+JP:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
</head>

<body>

  <!-- オープニング -->
  <div id="opening">
    <div class="opening-text">
      <p>この度、</p>
      <h1>私たちは結婚いたします</h1>
    </div>
  </div>

  <!-- メインコンテンツ -->
  <div id="main-content">

    <?php loadHeader(); ?>

    <section class="hero">
      <div class="hero-bg"></div>
      <div class="hero-text">
        <h1>Kakeru & Mirai</h1>
        <p>2026.07.19</p>
      </div>
    </section>

    <section class="message">
      <h2>Message</h2>
      <p>
        これまで支えてくださった皆さまへ<br>
        感謝の気持ちを込めて<br>
        ささやかな祝宴を催します。
      </p>
    </section>

    <section class="info">
      <div>
        <h3>Date</h3>
        <p>2026年7月19日（日）</p>
      </div>
      <div>
        <h3>Venue</h3>
        <p>◯◯チャペル</p>
      </div>
    </section>

    <section class="rsvp">
      <a href="rsvp.php" class="rsvp-btn">ご出席の回答はこちら</a>
    </section>

    <?php loadFooter(); ?>

  </div>

  <script src="js/main.js"></script>
</body>

</html>