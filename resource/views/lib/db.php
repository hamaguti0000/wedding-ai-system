<?php
// DB接続関数
function get_pdo()
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

// DBからユーザーを取得
function get_user_by_id($user_id)
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// DBから全ユーザー取得（管理者用など）
function get_all_users()
{
    $pdo = get_pdo();
    $stmt = $pdo->query("SELECT * FROM users");
    return $stmt->fetchAll();
}
