<?php
require_once('lib/init.php');
require_login();

$current_user_id = get_logged_in_user()['user_id'];
$pdo = get_pdo();

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$current_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $last_name = trim($_POST['last_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $furigana_sei = trim($_POST['furigana_sei'] ?? '');
    $furigana_mei = trim($_POST['furigana_mei'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $participation = $_POST['participation'] ?? null;
    $children_count = $_POST['children_count'] ?? 0;
    $ryori = $_POST['ryori'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($last_name === '' || $first_name === '' || $participation === null) {
        $error = '名前と出欠は必須です。';
    } else {
        $stmt = $pdo->prepare("
            UPDATE users SET
                last_name=?, first_name=?, furigana_sei=?, furigana_mei=?,
                address=?, phone=?, email=?, participation=?,
                children_count=?, ryori=?, notes=?, updated_at=NOW()
            WHERE user_id=?
        ");
        $stmt->execute([
            $last_name,
            $first_name,
            $furigana_sei,
            $furigana_mei,
            $address,
            $phone,
            $email,
            $participation,
            $children_count,
            $ryori,
            $notes,
            $current_user_id
        ]);

        $success = 'ご回答ありがとうございます！';
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$current_user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>結婚式招待状 | Kakeru ＆ Mirai</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Noto+Serif+JP&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/invitation.css">
</head>

<body>

    <div id="background"></div>

    <?php loadHeader(); ?>

    <section id="message">
        <div class="message-inner">
            <p>謹啓</p>
            <p>皆様におかれましてはますますご清祥のこととお慶び申し上げます。</p>
            <p>このたび私たちは結婚式を挙げることになりました。つきましては皆様により一層のご指導を賜りたく、ささやかではございますが披露宴を催したく存じます。</p>
            <p>ご多用中誠に恐縮ではございますが、ぜひご出席をいただきたくご案内申し上げます。</p>
            <p>謹白</p>
            <p>令和8年7月19日</p>
            <p>濵口 翔 / 馬場 弥礼</p>
        </div>
    </section>

    <section class="rsvp-form">
        <h2>出欠確認フォーム</h2>

        <?php if ($error): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>姓</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            <label>名</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
            <label>フリガナ（姓）</label>
            <input type="text" name="furigana_sei" value="<?= htmlspecialchars($user['furigana_sei']) ?>">
            <label>フリガナ（名）</label>
            <input type="text" name="furigana_mei" value="<?= htmlspecialchars($user['furigana_mei']) ?>">
            <label>住所</label>
            <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>">
            <label>電話番号</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            <label>メール</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">

            <label>出席 / 欠席</label>
            <button type="button" class="attendance-btn attend" data-value="1">出席</button>
            <button type="button" class="attendance-btn absent" data-value="0">欠席</button>
            <input type="hidden" name="participation" id="participation" value="<?= htmlspecialchars($user['participation']) ?>">

            <label>子供人数</label>
            <input type="number" name="children_count" value="<?= htmlspecialchars($user['children_count']) ?>" min="0">

            <label>料理</label>
            <select name="ryori">
                <?php
                $options = ['大人', 'キッズランチ', 'スペシャルランチ', 'その他', '席のみ'];
                foreach ($options as $opt) {
                    $sel = $user['ryori'] == $opt ? 'selected' : '';
                    echo "<option value=\"$opt\" $sel>$opt</option>";
                }
                ?>
            </select>

            <label>メッセージ</label>
            <textarea name="notes"><?= htmlspecialchars($user['notes']) ?></textarea>

            <button type="submit" class="btn">送信</button>
        </form>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/gsap@3/dist/gsap.min.js"></script>
    <script src="js/invitation.js"></script>
    <script src="js/countdown.js"></script>
</body>

</html>