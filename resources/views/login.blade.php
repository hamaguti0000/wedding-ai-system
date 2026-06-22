<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Kakeru &amp; Mirai Wedding</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Noto+Sans+JP:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ css_asset('css/login.css') }}">
</head>
<body>

    {{-- 背景写真 --}}
    <div class="wedding-bg">
        <img src="{{ ($bannerImage?->url ?? asset('img/チャペル.jpg')) }}" alt="">
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

        @if (session('status'))
        <div class="alert alert-info">{{ __(session('status')) }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" data-login-form>
            @csrf

            <div class="form-group">
                <label for="username">ユーザー名</label>
                <input id="username" type="text" name="username"
                    value="{{ old('username') }}"
                    autofocus required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <div class="password-field">
                    <input id="password" type="password" name="password"
                        required autocomplete="current-password">
                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="パスワードを表示" aria-pressed="false">
                        <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-submit" data-login-button>ログイン</button>
        </form>

        <div class="card-footer">
            <a href="{{ route('password.request') }}">パスワードを忘れた方はこちら</a><br>
            <a href="{{ route('email.forgot') }}">登録メールアドレスを忘れた方はこちら</a>
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

        (() => {
            const form = document.querySelector('[data-login-form]');
            const button = document.querySelector('[data-login-button]');
            const token = form?.querySelector('input[name="_token"]');

            if (!form || !button || !token) return;

            let refreshed = false;

            form.addEventListener('submit', async (event) => {
                if (refreshed) return;

                event.preventDefault();
                button.disabled = true;
                button.textContent = 'ログイン中...';

                try {
                    const response = await fetch('{{ route('csrf.refresh') }}', {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });
                    const data = await response.json();
                    if (data.token) token.value = data.token;
                } catch (error) {
                    // トークン更新に失敗しても通常のログイン送信は試みる。
                }

                refreshed = true;
                form.requestSubmit();
            });
        })();
    </script>

</body>
</html>
