<?php
require_once('lib/auth.php');

// セッションをクリアしてログアウト
session_start();
session_unset();
session_destroy();

// ログインページへリダイレクト
header("Location: login.php");
exit;
