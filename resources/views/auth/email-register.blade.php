<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メールアドレス登録 | Kakeru &amp; Mirai Wedding</title>
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
        <div class="ornament"><span>Email Registration</span></div>

        @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        @if (session('message') || session('info'))
        <div class="alert alert-info">{{ session('message') ?? session('info') }}</div>
        @endif

        @if (session('email_verification_sent') || session('success'))
        <div class="alert alert-info">
            確認メールを送信しました。メール内のURLをクリックして認証を完了してください。見つからない場合は迷惑メールフォルダも確認してください。
        </div>
        @endif

        @if ($user->isEmailUnverified())
        <div class="alert alert-info">
            <strong>{{ $user->email }}</strong> は未確認です。確認メールのURLをクリックすると次に進めます。
        </div>
        @endif

        <p class="login-note">
            パスワードを忘れた時の再設定に使います。ここでメールアドレスを登録し、届いた確認メールのURLをクリックしてください。
        </p>

        <form method="POST" action="{{ route('email.register.update') }}">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required autofocus autocomplete="email" placeholder="example@example.com">
            </div>

            <button type="submit" class="btn-submit">
                {{ $user->email ? '確認メールを送信する' : '登録して確認メールを送信' }}
            </button>
        </form>

        @if ($user->hasVerifiedEmail())
        <div class="card-footer">
            <a href="{{ route($user->password_change_required ? 'password.change' : 'dashboard') }}">次へ進む</a>
        </div>
        @else
        <div class="card-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="border:0;background:transparent;color:#b38b59;text-decoration:underline;text-underline-offset:3px;cursor:pointer;font:inherit;">
                    ログアウト
                </button>
            </form>
        </div>
        @endif
    </div>
</body>
</html>
