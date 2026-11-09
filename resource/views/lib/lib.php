<?php
// head 共通部分
function loadHead($title = '結婚式招待ページ')
{
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/common.css">
<?php
}

// ヘッダー
function loadHeader($activePage = '')
{
?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <header class="header">
        <div class="header-container">
            <a href="profile.php" class="logo">
                <i class="fa-solid fa-user-circle"></i> プロフィール
            </a>
            <nav>
                <ul>
                    <li><a href="home.php" class="<?= $activePage === 'home' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> ホーム</a></li>
                    <li><a href="menu.php" class="<?= $activePage === 'menu' ? 'active' : '' ?>"><i class="fa-solid fa-utensils"></i> お食事</a></li>
                    <li><a href="invitation.php" class="<?= $activePage === 'invitation' ? 'active' : '' ?>"><i class="fa-solid fa-envelope-open-text"></i> 招待状</a></li>
                    <li><a href="seating.php" class="<?= $activePage === 'seating' ? 'active' : '' ?>"><i class="fa-solid fa-chair"></i> 席次表</a></li>
                    <li><a href="contact.php" class="<?= $activePage === 'contact' ? 'active' : '' ?>"><i class="fa-solid fa-envelope"></i> お問い合わせ</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i> ログアウト</a>
        </div>
    </header>
<?php
}

// カウントダウン
function loadCountdown()
{
?>
    <div class="countdown" id="countdown">
        <span id="days"></span>日
        <span id="hours"></span>時間
        <span id="minutes"></span>分
        <span id="seconds"></span>秒
    </div>
<?php
}

// フッター
function loadFooter()
{
?>
    <footer>
        <p>&copy; 2026 濵口 ＆ Mirai Wedding Invitation</p>
    </footer>
<?php
}

// JS読み込み
function loadScripts()
{
?>
    <script src="js/main.js"></script>
    <script src="js/countdown.js"></script>
<?php
}
