<?php
require_once('lib/init.php');
require_login();

$menu = [
    [
        'category' => 'オードブル',
        'image' => 'img/チャペル.jpg',
        'dishes' => [
            ['title' => '帆立貝柱のスモークとズッキーニのミルフィユ', 'desc' => 'ソース・ア・ラ・クレームとイクラ添え。旬の食材を使った芳醇な味わいの前菜です。'],
            ['title' => 'オイルサーディンのカナッペ', 'desc' => '芳醇な香りのオイルサーディンを使用し、軽やかに仕上げました。'],
            ['title' => '生ハムで巻いた焼き林檎 ローズマリー添え', 'desc' => '球体のオリーブオイルとともに香り高く焼き上げた一皿。'],
            ['title' => 'ペティ・トマトに鋳込んだスモークサーモンのムース', 'desc' => 'やさしい口当たりで、前菜の締めにふさわしい一品です。']
        ]
    ],
    [
        'category' => 'スープとパン',
        'image' => 'img/チャペル.jpg',
        'dishes' => [
            ['title' => 'ポタージュまたはミネストローネ、胡桃パンと白いパン', 'desc' => '季節の素材を丁寧に煮込んだ温かいスープ。胡桃パンと白いパンとともにお楽しみいただけます。']
        ]
    ],
    [
        'category' => '魚料理',
        'image' => 'img/チャペル.jpg',
        'dishes' => [
            ['title' => '旬魚のポワレと帆立貝柱のクルート', 'desc' => 'ソース・アメリケーヌで仕上げた華やかな魚料理。旬の味覚を存分にお楽しみください。']
        ]
    ],
    [
        'category' => '肉料理',
        'image' => 'img/チャペル.jpg',
        'dishes' => [
            ['title' => 'USストリップロインのロティ', 'desc' => 'ソース・グレイビーと季節の野菜を添えた上品な肉料理。柔らかくジューシーな味わい。']
        ]
    ],
    [
        'category' => 'デセールとカフェ',
        'image' => 'img/チャペル.jpg',
        'dishes' => [
            ['title' => 'ケーキ', 'desc' => 'シェフ特製のケーキ。おふたりのイメージに合わせた特別なデザイン。'],
            ['title' => '季節のフルーツ', 'desc' => '旬のフルーツを彩り豊かに盛り付けたデセールプレート。'],
            ['title' => 'カフェ', 'desc' => 'コーヒーまたは紅茶。食後のひとときを穏やかに演出します。']
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>Wedding Menu</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Noto+Sans+JP&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/menu.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/ScrollTrigger.min.js"></script>
</head>

<body>
    <section class="menu-container">
        <header class="menu-header">
            <h1 class="menu-title">旬の食材を使った芳醇な洋コース</h1>
            <p class="menu-subtitle">大切な日にふさわしい、心を込めたお料理をご用意しました。</p>
        </header>

        <?php foreach ($menu as $cat): ?>
            <section class="meal">
                <div class="meal-image">
                    <img src="<?= $cat['image'] ?>" alt="<?= htmlspecialchars($cat['category'], ENT_QUOTES) ?>">
                </div>
                <div class="meal-content">
                    <h2 class="meal-category"><?= htmlspecialchars($cat['category'], ENT_QUOTES) ?></h2>
                    <?php foreach ($cat['dishes'] as $dish): ?>
                        <div class="meal-item">
                            <h3 class="meal-title"><?= htmlspecialchars($dish['title'], ENT_QUOTES) ?></h3>
                            <p class="meal-desc"><?= htmlspecialchars($dish['desc'], ENT_QUOTES) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </section>

    <script src="js/menu.js"></script>
</body>

</html>