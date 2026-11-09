<?php
// ログイン中ユーザー取得
function get_logged_in_user()
{
    start_session_once();
    if (empty($_SESSION['user_id'])) return null;
    return get_user_by_id($_SESSION['user_id']);
}

// ログイン必須チェック
function require_login()
{   
    start_session_once();
    if (empty($_SESSION['user_id'])) {
        // 現在のURLを保存
        $current_url = $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_after_login'] = $current_url;

        header("Location: login.php");
        exit;
    }
}


// ✅ ログイン成功時にセッションをセットする関数
function set_login_session(array $user)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // セッションが未開始なら開始
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['is_active'] = $user['is_active'];
    // 必要に応じて他の情報も追加可能
}

