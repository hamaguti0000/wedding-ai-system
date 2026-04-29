<?php
session_start();
require_once('com/include.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');

    try {
        $db = new Database();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $display_name = trim($_POST['display_name'] ?? '');

        // 必須チェック
        if (!$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'メール・パスワードは必須です']);
            exit;
        }

        // メール形式チェック
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => '正しいメールアドレスを入力してください']);
            exit;
        }

        // メール重複チェック
        $exists = $db->fetch("SELECT id FROM accounts WHERE email = :email", [':email' => $email]);
        if ($exists) {
            echo json_encode(['success' => false, 'message' => 'このメールは既に登録済みです']);
            exit;
        }

        // パスワードハッシュ
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // UUID生成
        $uuid = bin2hex(random_bytes(16)); // 32文字のUUID風（本番はRamsey\Uuid推奨）

        $now = date('Y-m-d H:i:s');

        // accountsに登録
        $sql = "INSERT INTO accounts 
            (id, email, password_hash, display_name, is_active, auth_type, created_at, updated_at)
            VALUES
            (:id, :email, :password, :display_name, 1, 'password', :created, :updated)";

        $db->execute($sql, [
            ':id' => $uuid,
            ':email' => $email,
            ':password' => $passwordHash,
            ':display_name' => $display_name,
            ':created' => $now,
            ':updated' => $now
        ]);

        $_SESSION['account_id'] = $uuid;

        echo json_encode(['success' => true, 'message' => '登録成功']);
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => '登録中にエラーが発生しました']);
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ユーザー登録</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/create_user.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
    <script src=js/common_ajax.js></script>
</head>

<body>
    <div class="user-register">
        <h2>ユーザー登録フォーム</h2>
        <div class="form-slider-wrapper">
            <form id="registerForm" class="form-slider" action="create_user.php" method="post">
                <div class="form-step">
                    <label><span>姓 <span class="required">*</span></span><input type="text" name="last_name" required></label>
                    <label><span>名 <span class="required">*</span></span><input type="text" name="first_name" required></label>
                    <label><span>パスワード <span class="required">*</span></span><input type="password" name="password" required></label>
                    <label><span>関係</span>
                        <select name="relation_type">
                            <option value="新郎親族">新郎親族</option>
                            <option value="新婦親族">新婦親族</option>
                            <option value="新郎友人">新郎友人</option>
                            <option value="新婦友人">新婦友人</option>
                            <option value="新郎会社">新郎会社</option>
                            <option value="新婦会社">新婦会社</option>
                        </select>
                    </label>
                    <div class="form-navigation">
                        <button type="button" class="next-btn">次へ</button>
                    </div>
                </div>

                <div class="form-step">
                    <label><span>住所</span><input type="text" name="address"></label>
                    <label><span>電話番号</span><input type="text" name="phone"></label>
                    <label><span>メール <span class="required">*</span></span><input type="email" name="email" required></label>
                    <div class="form-navigation">
                        <button type="button" class="prev-btn">戻る</button>
                        <button type="button" class="next-btn">次へ</button>
                    </div>
                </div>

                <div class="form-step">
                    <label><span>子供人数</span><input type="number" name="children_count" value="0"></label>
                    <label><span>参加人数</span><input type="number" name="participation" value="1"></label>
                    <label><span>自由記入欄</span><textarea name="notes"></textarea></label>
                    <div class="form-navigation">
                        <button type="button" class="prev-btn">戻る</button>
                        <button type="submit">登録</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script src="js/create_user.js"></script>
</body>

</html>