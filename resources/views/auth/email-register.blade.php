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
    @php
        $sentAt = $user->email_verification_sent_at;
        $resendAvailableAt = $sentAt?->copy()->addMinutes(60);
        $formEmail = old('email', $user->email);
        $isSameAsCurrentEmail = strtolower(trim((string) $formEmail)) === strtolower(trim((string) $user->email));
        $isWaitingForResend = $user->isEmailUnverified()
            && $sentAt
            && $isSameAsCurrentEmail
            && $resendAvailableAt->isFuture();
        $waitingMinutes = $isWaitingForResend ? max(1, (int) ceil(now()->diffInSeconds($resendAvailableAt) / 60)) : null;
    @endphp

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
            確認メールを送信しました。ボタンはしばらく押せません。メール内のURLをクリックして認証を完了してください。
        </div>
        @endif

        @if ($user->isEmailUnverified())
        <div class="alert alert-info">
            <strong>{{ $user->email }}</strong> は未確認です。確認メールのURLをクリックすると次に進めます。
            @if ($isWaitingForResend)
                再送は約{{ $waitingMinutes }}分後にできます。
            @endif
        </div>
        @endif

        <p class="login-note">
            パスワードを忘れた時の再設定に使います。ここでメールアドレスを登録し、届いた確認メールのURLをクリックしてください。
        </p>

        <form method="POST" action="{{ route('email.register.update') }}" data-email-form>
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ $formEmail }}" required autofocus autocomplete="email" placeholder="example@example.com" data-current-email="{{ $user->email }}">
            </div>

            <button type="submit" class="btn-submit" data-submit-button @disabled($isWaitingForResend)>
                @if ($isWaitingForResend)
                    確認メール送信済み
                @else
                    {{ $user->email ? '確認メールを送信する' : '登録して確認メールを送信' }}
                @endif
            </button>
            <p class="login-note" data-submit-status style="margin:12px 0 0;">
                @if ($isWaitingForResend)
                    すでに確認メールを送信しています。メールが見つからない場合は迷惑メールフォルダも確認してください。
                @endif
            </p>
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
    <script>
        (() => {
            const form = document.querySelector('[data-email-form]');
            const button = document.querySelector('[data-submit-button]');
            const status = document.querySelector('[data-submit-status]');
            const email = document.querySelector('#email');

            if (!form || !button || !status || !email) return;

            const waitingLabel = '送信中です...';
            const submittedLabel = '確認メールを送信しました';
            const defaultLabel = button.textContent.trim();
            const currentEmail = (email.dataset.currentEmail || '').trim().toLowerCase();

            email.addEventListener('input', () => {
                const nextEmail = email.value.trim().toLowerCase();
                if (button.disabled && currentEmail && nextEmail !== currentEmail) {
                    button.disabled = false;
                    button.textContent = defaultLabel.includes('送信済み') ? '確認メールを送信する' : defaultLabel;
                    status.textContent = 'メールアドレスを変更した場合は、登録すると新しい確認メールを送信します。';
                }
            });

            form.addEventListener('submit', () => {
                button.disabled = true;
                button.textContent = waitingLabel;
                status.textContent = 'ボタンを押しました。確認メールを送信しています。画面が切り替わるまでお待ちください。';

                window.setTimeout(() => {
                    button.textContent = submittedLabel;
                    status.textContent = '送信処理中です。二重送信を防ぐため、しばらくお待ちください。';
                }, 1200);
            });
        })();
    </script>
</body>
</html>
