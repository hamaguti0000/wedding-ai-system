<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録メール確認 | Kakeru &amp; Mirai Wedding</title>
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
        <div class="ornament"><span>Email Help</span></div>

        @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @if (session('message'))
        <div class="alert alert-info">{{ session('message') }}</div>
        @endif

        @if (session('found_email'))
        <div class="alert alert-info">
            登録メールアドレスは <strong>{{ session('found_email') }}</strong> です。<br>
            @if (session('found_email_verified'))
                認証済みのメールアドレスです。
            @else
                まだ認証が完了していません。ログイン後に確認メールを再送してください。
            @endif
        </div>
        @endif

        <p class="login-note">
            ユーザー名から登録メールアドレスの一部を確認できます。第三者に見えないよう一部を伏せて表示します。
        </p>

        <form method="POST" action="{{ route('email.forgot.lookup') }}">
            @csrf

            <div class="form-group">
                <label for="username">ユーザー名</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
            </div>

            <button type="submit" class="btn-submit">登録メールを確認</button>
        </form>

        <div class="card-footer">
            <a href="{{ route('password.request') }}">パスワードを忘れた方はこちら</a><br>
            <a href="{{ route('login') }}">ログインへ戻る</a>
        </div>
    </div>
</body>
</html>
