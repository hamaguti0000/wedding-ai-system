<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Kakeru &amp; Mirai Wedding</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Noto+Sans+JP:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    {{-- 背景写真 --}}
    <div class="wedding-bg">
        <img src="{{ asset('img/チャペル.jpg') }}" alt="">
    </div>

    {{-- ログインカード --}}
    <div class="login-card">

        <div class="monogram">K &amp; M</div>

        <div class="ornament">
            <span>Wedding 2026</span>
        </div>

        @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @if (session('message'))
        <div class="alert alert-info">{{ session('message') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email"
                    value="{{ old('email') }}"
                    placeholder="your@email.com"
                    autofocus required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password"
                    placeholder="••••••••"
                    required>
            </div>

            <button type="submit" class="btn-submit">ログイン</button>
        </form>

    </div>

</body>
</html>
