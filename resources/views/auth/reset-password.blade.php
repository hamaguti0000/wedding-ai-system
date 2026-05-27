<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>新しいパスワード | Kakeru &amp; Mirai Wedding</title>
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
        <div class="ornament"><span>New Password</span></div>

        @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">新しいパスワード</label>
                <div class="password-field">
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="パスワードを表示" aria-pressed="false">
                        <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">新しいパスワード（確認）</label>
                <div class="password-field">
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="確認用パスワードを表示" aria-pressed="false">
                        <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">パスワードを更新</button>
        </form>

        <div class="card-footer">
            <a href="{{ route('login') }}">ログインへ戻る</a>
        </div>
    </div>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                if (!input) return;

                const willShow = input.type === 'password';
                input.type = willShow ? 'text' : 'password';
                button.setAttribute('aria-pressed', willShow ? 'true' : 'false');
                button.setAttribute('aria-label', willShow ? 'パスワードを隠す' : 'パスワードを表示');
                button.classList.toggle('is-visible', willShow);
            });
        });
    </script>
</body>
</html>
