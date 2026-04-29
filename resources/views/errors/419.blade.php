<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>セッションが切れました | Kakeru &amp; Mirai Wedding</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Noto+Sans+JP:wght@300;400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fdfaf6;
            font-family: 'Noto Sans JP', sans-serif;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 14px;
            padding: 52px 44px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 6px 28px rgba(0,0,0,0.08), 0 0 0 1px rgba(232,213,183,0.35);
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
        }
        .title {
            font-family: 'Playfair Display', serif;
            font-size: 1.55rem;
            font-weight: 400;
            color: #b38b59;
            margin-bottom: 14px;
        }
        .desc {
            font-size: 0.9rem;
            color: #6b5b4e;
            line-height: 1.9;
            margin-bottom: 32px;
        }
        .btn {
            display: inline-block;
            padding: 13px 36px;
            background: #b38b59;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.92rem;
            font-family: 'Noto Sans JP', sans-serif;
            font-weight: 500;
            letter-spacing: 1.5px;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn:hover { background: #9a7447; }
        .rule {
            width: 36px; height: 1px;
            background: #b38b59;
            margin: 0 auto 28px;
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="icon">⏱</span>
        <h1 class="title">セッションが切れました</h1>
        <div class="rule"></div>
        <p class="desc">
            長時間操作がなかったため、<br>
            セッションが終了しました。<br><br>
            再度ログインして操作を続けてください。
        </p>
        <a href="{{ route('login') }}" class="btn">ログインページへ</a>
    </div>
</body>
</html>
