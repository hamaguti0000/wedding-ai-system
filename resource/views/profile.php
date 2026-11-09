<?php
require_once('lib/init.php');

require_login();

$user = get_logged_in_user();

// 管理者判定（新郎または新婦）
$isAdmin = ($user['relation_type'] === '新郎' || $user['relation_type'] === '新婦');
$pdo = get_pdo(); // init.php内で定義されている関数を呼ぶ

// DBから再取得（安全のため）
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user['user_id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// プロフィール画像設定
$profileDir = "profile/" . htmlspecialchars($user['user_id']);
if (!file_exists($profileDir)) mkdir($profileDir, 0777, true);

$profileImage = "$profileDir/profile.jpg";
if (!file_exists($profileImage)) $profileImage = "profile/default.jpg";

// キャッシュ回避用タイムスタンプ
$timestamp = file_exists($profileImage) ? filemtime($profileImage) : time();

// 更新処理
$success = false;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $children_count = intval($_POST['children_count'] ?? 0);
    $participation = intval($_POST['participation'] ?? 1);
    $notes = $_POST['notes'] ?? '';

    // DB 更新
    $stmt = $pdo->prepare("
        UPDATE users 
        SET name=?, email=?, address=?, phone=?, children_count=?, participation=?, notes=?
        WHERE user_id=?
    ");
    $stmt->execute([$name, $email, $address, $phone, $children_count, $participation, $notes, $user['user_id']]);

    // プロフィール写真アップロード処理
    if (!empty($_FILES['profile_image']['tmp_name'])) {
        $fileTmp = $_FILES['profile_image']['tmp_name'];
        $fileSize = $_FILES['profile_image']['size'];
        $fileType = mime_content_type($fileTmp);

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
            $uploadPath = "$profileDir/profile.jpg";
            move_uploaded_file($fileTmp, $uploadPath);
        } else {
            $errorMsg = "ファイルは JPEG/PNG/GIF で、2MB 以下にしてください。";
        }
    }

    $success = true;
    // 再読み込みして最新のプロフィールを反映
    header("Location: profile.php?success=1&ts=" . time());
    exit;
}

$success = isset($_GET['success']);
$timestamp = isset($_GET['ts']) ? intval($_GET['ts']) : $timestamp;

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>プロフィール | 招待管理システム</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <header class="header">
        <div class="header-container">
            <a href="profile.php" class="logo"><i class="fa-solid fa-user-circle"></i> プロフィール</a>
            <nav>
                <ul>
                    <li><a href="home.php"><i class="fa-solid fa-house"></i> ホーム</a></li>
                    <li><a href="menu.php"><i class="fa-solid fa-utensils"></i> お食事</a></li>
                    <li><a href="invitation.php"><i class="fa-solid fa-envelope-open"></i> 招待状</a></li>
                    <li><a href="contact.php"><i class="fa-solid fa-envelope"></i> お問い合わせ</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i> ログアウト</a>
        </div>
    </header>

    <main class="profile-container">
        <div class="profile-card">
            <h2><i class="fa-solid fa-id-card"></i> あなたのプロフィール</h2>

            <?php if ($success): ?>
                <p class="success-msg"><i class="fa-solid fa-check-circle"></i> プロフィールを更新しました。</p>
            <?php elseif ($errorMsg): ?>
                <p class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?></p>
            <?php endif; ?>

            <form method="post" action="profile.php" class="profile-form" enctype="multipart/form-data">
                <div class="profile-image-area">
                    <img id="profile-preview" src="<?= htmlspecialchars($profileImage) ?>?t=<?= $timestamp ?>" alt="プロフィール写真" class="profile-image">

                    <?php if ($isAdmin): ?>
                        <div class="upload-area">
                            <label for="profile-upload" class="upload-label"><i class="fa-solid fa-camera"></i> 写真を変更</label>
                            <input type="file" id="profile-upload" name="profile_image" accept="image/*" style="display:none;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>名前</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($profile['name']) ?>" <?= $isAdmin ? '' : 'readonly' ?>>
                </div>

                <div class="form-group">
                    <label>関係</label>
                    <input type="text" name="relation_type" value="<?= htmlspecialchars($profile['relation_type']) ?>" readonly>
                </div>

                <div class="form-group">
                    <label>メールアドレス</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($profile['email']) ?>" <?= $isAdmin ? '' : 'readonly' ?>>
                </div>

                <div class="form-group">
                    <label>住所</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($profile['address'] ?? '') ?>" <?= $isAdmin ? '' : 'readonly' ?>>
                </div>

                <div class="form-group">
                    <label>電話番号</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone'] ?? '') ?>" <?= $isAdmin ? '' : 'readonly' ?>>
                </div>

                <div class="form-group">
                    <label>子供の人数</label>
                    <input type="number" name="children_count" value="<?= htmlspecialchars($profile['children_count'] ?? 0) ?>" <?= $isAdmin ? '' : 'readonly' ?>>
                </div>

                <div class="form-group">
                    <label>参加人数</label>
                    <input type="number" name="participation" value="<?= htmlspecialchars($profile['participation'] ?? 1) ?>" <?= $isAdmin ? '' : 'readonly' ?>>
                </div>

                <div class="form-group">
                    <label>メモ</label>
                    <textarea name="notes" <?= $isAdmin ? '' : 'readonly' ?>><?= htmlspecialchars($profile['notes'] ?? '') ?></textarea>
                </div>

                <?php if ($isAdmin): ?>
                    <div class="button-area">
                        <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> 保存</button>
                    </div>
                <?php else: ?>
                    <p class="readonly-msg">※ 編集は管理者のみ可能です。</p>
                <?php endif; ?>
            </form>
        </div>
    </main>

    <script>
        <?php if ($isAdmin): ?>
            // 画像プレビュー
            document.getElementById('profile-upload').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        document.getElementById('profile-preview').src = ev.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        <?php endif; ?>
    </script>
</body>

</html>