<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード変更 | Kakeru &amp; Mirai Wedding</title>
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
        <div class="ornament"><span>Password</span></div>
        @include('auth.partials.onboarding-steps', [
            'current' => 'password',
            'emailDone' => true,
        ])

        @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        @if (session('message'))
        <div class="alert alert-info">{{ session('message') }}</div>
        @endif

        <p class="login-note">
            メール認証は完了しています。最後に、ご自身だけが分かる新しいパスワードを設定してください。
        </p>

        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="current_password">現在のパスワード</label>
                <div class="password-field">
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" data-password-toggle="current_password" aria-label="パスワードを表示" aria-pressed="false">
                        <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password">新しいパスワード（6文字以上）</label>
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
                    <button type="button" class="password-toggle" data-password-toggle="password_confirmation" aria-label="パスワードを表示" aria-pressed="false">
                        <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit">パスワードを変更して進む</button>
        </form>

        <div class="card-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="border:0;background:transparent;color:#b38b59;text-decoration:underline;text-underline-offset:3px;cursor:pointer;font:inherit;">
                    ログアウト
                </button>
            </form>
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
