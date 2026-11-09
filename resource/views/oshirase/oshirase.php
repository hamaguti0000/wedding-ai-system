<?php
require_once('../lib/init.php');  // DB接続とログインチェック

$pdo = get_pdo(); // PDO接続関数
$current_user = get_logged_in_user();
$is_admin = isset($current_user['admin']) && $current_user['admin'] == 1;

// URLパラメータでモード判定
$mode = $_GET['mode'] ?? 'list';
$id   = $_GET['id'] ?? null;

// =======================
// POST処理（新規・編集・削除）
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO oshirase (title, content) VALUES (?, ?)");
        $stmt->execute([$_POST['title'], $_POST['content']]);
        header("Location: oshirase.php");
        exit;
    }
    if ($action === 'edit' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE oshirase SET title=?, content=? WHERE id=?");
        $stmt->execute([$_POST['title'], $_POST['content'], $_POST['id']]);
        header("Location: oshirase.php");
        exit;
    }
    if ($action === 'delete' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("DELETE FROM oshirase WHERE id=?");
        $stmt->execute([$_POST['id']]);
        header("Location: oshirase.php");
        exit;
    }
}

// =======================
// 編集用データ取得
// =======================
$edit_data = null;
if (($mode === 'edit' || $mode === 'detail') && $id) {
    $stmt = $pdo->prepare("SELECT * FROM oshirase WHERE id=?");
    $stmt->execute([$id]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// =======================
// HTMLレンダリング
// =======================
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>お知らせ管理</title>
    <link rel="stylesheet" href="../css/oshirase.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
</head>

<body>
    <div class="container">
        <h1>お知らせ管理</h1>

        <?php if ($mode === 'list'): ?>
            <?php
            $stmt = $pdo->query("SELECT * FROM oshirase ORDER BY created_at DESC");
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <?php if ($is_admin): ?>
                <p><a href="oshirase.php?mode=create">新規作成</a></p>
            <?php endif; ?>
            <?php foreach ($articles as $item): ?>
                <div class="article">
                    <h2><a href="oshirase.php?mode=detail&id=<?= $item['id'] ?>"><?= htmlspecialchars($item['title']) ?></a></h2>
                    <p>投稿日: <?= $item['created_at'] ?></p>
                    <?php if ($is_admin): ?>
                        <p>
                            <a href="oshirase.php?mode=edit&id=<?= $item['id'] ?>">編集</a> |
                        <form action="oshirase.php" method="post" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        <?php elseif ($mode === 'detail' && $edit_data): ?>
            <div class="article">
                <h2><?= htmlspecialchars($edit_data['title']) ?></h2>
                <p>投稿日: <?= $edit_data['created_at'] ?></p>
                <div class="article-content"><?= $edit_data['content'] ?></div>
                <p><a href="oshirase.php">一覧へ戻る</a></p>
            </div>

        <?php elseif (($mode === 'create' || $mode === 'edit') && $is_admin): ?>
            <form method="POST">
                <input type="hidden" name="action" value="<?= $mode === 'create' ? 'create' : 'edit' ?>">
                <?php if ($mode === 'edit'): ?>
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>
                <label>タイトル</label>
                <input type="text" name="title" value="<?= $edit_data['title'] ?? '' ?>" required>
                <label>本文</label>
                <textarea name="content" id="editor"><?= $edit_data['content'] ?? '' ?></textarea>
                <button type="submit"><?= $mode === 'create' ? '作成' : '更新' ?></button>
                <p><a href="oshirase.php">一覧へ戻る</a></p>
            </form>
            <script>
                ClassicEditor.create(document.querySelector('#editor'));
            </script>
        <?php else: ?>
            <p>権限がありません。</p>
        <?php endif; ?>

    </div>
</body>

</html>