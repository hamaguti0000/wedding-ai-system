<?php
require_once(__DIR__ . '/com/include.php');
require_login();

$db = new Database();

/* =========================
   Ajax: メンバー取得
========================= */
if (isset($_GET['member_id'])) {
    header('Content-Type: application/json; charset=utf-8');

    $member_id = $_GET['member_id'];

    $member = $db->fetch(
        "SELECT 
            wm.wedding_member_id,
            gp.last_name,
            gp.first_name,
            gp.email,
            gp.phone,
            gp.address,
            gp.notes
         FROM wedding_members wm
         JOIN guest_profiles gp 
           ON wm.guest_profile_id = gp.guest_profile_id
         WHERE wm.wedding_member_id = ?",
        [$member_id]
    );

    if (!$member) {
        echo json_encode(['error' => '見つかりません']);
        exit;
    }

    echo json_encode([
        'member_id' => $member['wedding_member_id'],
        'name' => trim($member['last_name'] . ' ' . $member['first_name']),
        'email' => $member['email'],
        'phone' => $member['phone'],
        'address' => $member['address'],
        'notes' => $member['notes']
    ]);
    exit;
}

/* =========================
   POST: 座席更新
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $data = json_decode(file_get_contents('php://input'), true);
    $updates = $data['updates'] ?? [];

    foreach ($updates as $update) {

        $seat_id = $update['seat_id'] ?? null;
        $member_id = $update['member_id'] ?? null;

        if (!$seat_id) continue;

        // 既存削除
        $db->execute(
            "DELETE FROM seat_assignments WHERE seat_id = ?",
            [$seat_id]
        );

        // 新規割当
        if ($member_id) {
            $db->execute(
                "INSERT INTO seat_assignments (seat_id, wedding_member_id)
                 VALUES (?, ?)",
                [$seat_id, $member_id]
            );
        }
    }

    echo json_encode(['status' => 'success']);
    exit;
}

/* =========================
   表示データ取得
========================= */

// テーブル一覧
$tables = $db->query(
    "SELECT * FROM tables
     ORDER BY position_row, position_col"
);

// 席＋割当取得
$seat_data = $db->query(
    "SELECT 
        s.seat_id,
        s.table_id,
        s.seat_number,
        wm.wedding_member_id,
        gp.last_name,
        gp.first_name
     FROM seats s
     LEFT JOIN seat_assignments sa 
        ON s.seat_id = sa.seat_id
     LEFT JOIN wedding_members wm 
        ON wm.wedding_member_id = sa.wedding_member_id
     LEFT JOIN guest_profiles gp
        ON gp.guest_profile_id = wm.guest_profile_id
     ORDER BY s.table_id, s.seat_number"
);

// 未割当メンバー
$unassigned_members = $db->query(
    "SELECT 
        wm.wedding_member_id,
        gp.last_name,
        gp.first_name
     FROM wedding_members wm
     JOIN guest_profiles gp
        ON gp.guest_profile_id = wm.guest_profile_id
     LEFT JOIN seat_assignments sa
        ON sa.wedding_member_id = wm.wedding_member_id
     WHERE sa.seat_id IS NULL"
);

// 整形
$seats_by_table = [];

foreach ($seat_data as $seat) {
    $seats_by_table[$seat['table_id']][] = $seat;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>席次表</title>
    <link rel="stylesheet" href="css/seating.css">
</head>

<body>

    <h1>席次表</h1>

    <div class="tables-container">
        <?php foreach ($tables as $table): ?>
            <?php $table_id = $table['table_id']; ?>
            <div class="table" data-table-id="<?= htmlspecialchars($table_id) ?>">
                <h3><?= htmlspecialchars($table['table_name']) ?></h3>

                <div class="seats">
                    <?php if (!empty($seats_by_table[$table_id])): ?>
                        <?php foreach ($seats_by_table[$table_id] as $seat): ?>
                            <?php
                            $name = '空席';
                            if (!empty($seat['wedding_member_id'])) {
                                $name = htmlspecialchars(
                                    trim(($seat['last_name'] ?? '') . ' ' . ($seat['first_name'] ?? ''))
                                );
                            }
                            ?>
                            <div class="seat"
                                draggable="true"
                                data-seat-id="<?= htmlspecialchars($seat['seat_id']) ?>"
                                data-member-id="<?= htmlspecialchars($seat['wedding_member_id'] ?? '') ?>">
                                <?= $name ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>未割当</h2>
    <div id="unassigned-members">
        <?php foreach ($unassigned_members as $member): ?>
            <?php
            $name = htmlspecialchars(
                trim(($member['last_name'] ?? '') . ' ' . ($member['first_name'] ?? ''))
            );
            ?>
            <div class="unassigned-member"
                draggable="true"
                data-member-id="<?= htmlspecialchars($member['wedding_member_id']) ?>">
                <?= $name ?>
            </div>
        <?php endforeach; ?>
    </div>

    <button id="save-button">更新</button>

    <script src="js/seating.js"></script>
</body>

</html>