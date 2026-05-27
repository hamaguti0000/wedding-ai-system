<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード再設定 | Kakeru &amp; Mirai Wedding</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Noto+Sans+JP:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>
    <div class="wedding-bg">
        @php $bg = \App\Models\SiteImage::forDisplay('login_bg'); @endphp
        <img src="{{ $bg?->url ?? asset('img/チャペル.jpg') }}" alt="">
    </div>

    <div class="login-card">
        <div class="monogram">K &amp; M</div>
        <div class="ornament"><span>Password Reset</span></div>

        @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @if ($status)
        <div class="alert alert-info">{{ __($status) }}</div>
        @endif

        <p class="login-note">
            登録済みのメールアドレスを入力してください。パスワード再設定用のリンクをお送りします。
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>

            <button type="submit" class="btn-submit">再設定メールを送信</button>
        </form>

        <div class="card-footer">
            <a href="{{ route('email.forgot') }}">登録メールアドレスを忘れた方はこちら</a><br>
            <a href="{{ route('login') }}">ログインへ戻る</a>
        </div>
    </div>
</body>
</html>
