<?php
require_once('lib/init.php');
require_login();

$pdo = get_pdo();
$current_user = get_logged_in_user();
$is_admin = isset($current_user['admin']) && $current_user['admin'] == 1;

// =======================
// 更新処理
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    foreach ($_POST['user_id'] as $index => $user_id) {
        $stmt = $pdo->prepare("
            UPDATE users SET
                last_name = ?,
                first_name = ?,
                furigana_sei = ?,
                furigana_mei = ?,
                relation_type = ?,
                side = ?,
                address = ?,
                table_id = ?,
                seat_number = ?,
                ryori = ?,
                role = ?,
                hojyo = ?,
                notes = ?,
                admin = ?
            WHERE user_id = ?
        ");

        // 役割・補助情報をカンマ区切りに
        $roles = isset($_POST['chkrole'][$index]) ? implode(',', $_POST['chkrole'][$index]) : '';
        $hojyo = isset($_POST['chkhojyo'][$index]) ? implode(',', $_POST['chkhojyo'][$index]) : '';

        // table_id が空なら NULL に変換
        $table_id = $_POST['table_id'][$index] === "" ? null : $_POST['table_id'][$index];

        $stmt->execute([
            $_POST['last_name'][$index],
            $_POST['first_name'][$index],
            $_POST['furigana_sei'][$index],
            $_POST['furigana_mei'][$index],
            $_POST['relation_type'][$index],
            $_POST['side'][$index],
            $_POST['address'][$index],
            $table_id,
            $_POST['seat_number'][$index],
            $_POST['ryori'][$index],
            $roles,
            $hojyo,
            $_POST['notes'][$index],
            $_POST['admin'][$index],
            $user_id
        ]);
    }

    // 更新後リロードして二重送信防止
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
    exit;
}

// =======================
// ユーザー一覧取得
// =======================
$stmt = $pdo->query("SELECT * FROM users ORDER BY user_id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =======================
// テーブル一覧取得
// =======================
$tables = $pdo->query("SELECT table_id, table_name FROM tables ORDER BY table_id ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ユーザー編集 | Wedding</title>
    <link rel="stylesheet" href="css/view_user.css">
</head>

<body>
    <div class="user-container">
        <h2>💍 招待者リスト 編集ページ</h2>

        <?php if (isset($_GET['success'])): ?>
            <p class="success-msg">✅ 更新が完了しました。</p>
        <?php endif; ?>

        <form method="post">
            <div class="user-table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名前（フリガナ上・漢字下）</th>
                            <th>関係</th>
                            <th>側</th>
                            <th>住所</th>
                            <th>テーブル</th>
                            <th>席番号</th>
                            <th>料理</th>
                            <th>役割</th>
                            <th>補助情報</th>
                            <th>備考</th>
                            <th>管理者</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $index => $user): ?>
                            <tr>
                                <!-- ID -->
                                <td>
                                    <?= htmlspecialchars($user['user_id']) ?>
                                    <input type="hidden" name="user_id[]" value="<?= htmlspecialchars($user['user_id']) ?>">
                                </td>

                                <!-- 名前 -->
                                <td>
                                    <div class="name-cell">
                                        <div class="name-furigana">
                                            <input type="text" name="furigana_sei[]" value="<?= htmlspecialchars($user['furigana_sei'] ?? '') ?>" placeholder="セイ">
                                            <input type="text" name="furigana_mei[]" value="<?= htmlspecialchars($user['furigana_mei'] ?? '') ?>" placeholder="メイ">
                                        </div>
                                        <div class="name-kanji">
                                            <input type="text" name="last_name[]" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" placeholder="姓">
                                            <input type="text" name="first_name[]" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" placeholder="名">
                                        </div>
                                    </div>
                                </td>

                                <!-- 関係 -->
                                <td>
                                    <select name="relation_type[]">
                                        <?php
                                        $relations = ['新郎', '新婦', '新郎親族', '新婦親族', '新郎友人', '新婦友人', '管理者'];
                                        foreach ($relations as $rel) {
                                            $selected = ($user['relation_type'] === $rel) ? 'selected' : '';
                                            echo "<option value=\"$rel\" $selected>$rel</option>";
                                        }
                                        ?>
                                    </select>
                                </td>

                                <!-- 側 -->
                                <td>
                                    <select name="side[]">
                                        <option value="">未設定</option>
                                        <option value="新郎側" <?= ($user['side'] === '新郎側') ? 'selected' : '' ?>>新郎側</option>
                                        <option value="新婦側" <?= ($user['side'] === '新婦側') ? 'selected' : '' ?>>新婦側</option>
                                    </select>
                                </td>

                                <!-- 住所 -->
                                <td><textarea name="address[]" rows="4"><?= htmlspecialchars($user['address'] ?? '') ?></textarea></td>

                                <!-- テーブル -->
                                <td>
                                    <select name="table_id[]">
                                        <option value="">未割当</option>
                                        <?php foreach ($tables as $t): ?>
                                            <option value="<?= $t['table_id'] ?>" <?= $user['table_id'] == $t['table_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['table_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>

                                <!-- 席番号 -->
                                <td><input type="number" name="seat_number[]" value="<?= htmlspecialchars($user['seat_number'] ?? '') ?>" style="width:45px;"></td>

                                <!-- 料理 -->
                                <td>
                                    <div class="radio-group">
                                        <?php
                                        $ryori_options = ['大人', 'キッズランチ', 'スペシャルランチ', 'その他', '席のみ'];
                                        foreach ($ryori_options as $opt) {
                                            $checked = ($user['ryori'] === $opt) ? 'checked' : '';
                                            echo "<label><input type='radio' name='ryori[$index]' value='$opt' $checked>$opt</label>";
                                        }
                                        ?>
                                    </div>
                                </td>

                                <!-- 役割 -->
                                <td>
                                    <div class="checkbox-group">
                                        <?php
                                        $roles_options = ['来賓祝辞', '友人祝辞', '乾杯', '万歳', '受付担当', 'エスコート', '余興代表', '余興人', '二次会幹事'];
                                        $user_roles = isset($user['role']) ? explode(',', $user['role']) : [];
                                        foreach ($roles_options as $role) {
                                            $checked = in_array($role, $user_roles) ? 'checked' : '';
                                            echo "<label><input type='checkbox' name='chkrole[$index][]' value='$role' $checked>$role</label>";
                                        }
                                        ?>
                                    </div>
                                </td>

                                <!-- 補助情報 -->
                                <td>
                                    <div class="checkbox-group">
                                        <?php
                                        $hojyo_options = ['子供椅子', 'ベビーカー(持込み)', 'ベビーベット(依頼)', '車椅子', '食品アレルギー', '席のみ準備あり'];
                                        $user_hojyo = isset($user['hojyo']) ? explode(',', $user['hojyo']) : [];
                                        foreach ($hojyo_options as $ho) {
                                            $checked = in_array($ho, $user_hojyo) ? 'checked' : '';
                                            echo "<label><input type='checkbox' name='chkhojyo[$index][]' value='$ho' $checked>$ho</label>";
                                        }
                                        ?>
                                    </div>
                                </td>

                                <!-- 備考 -->
                                <td><textarea name="notes[]" rows="2"><?= htmlspecialchars($user['notes'] ?? '') ?></textarea></td>

                                <!-- 管理者 -->
                                <td>
                                    <?php if ($is_admin): ?>
                                        <select name="admin[]">
                                            <option value="0" <?= $user['admin'] == 0 ? 'selected' : '' ?>>一般</option>
                                            <option value="1" <?= $user['admin'] == 1 ? 'selected' : '' ?>>管理者</option>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="admin[]" value="<?= htmlspecialchars($user['admin']) ?>">
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($is_admin): ?>
                <div class="btn-wrapper">
                    <button type="submit" class="btn">💾 更新</button>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>