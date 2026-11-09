<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>メールアドレスの確認 | Wedding</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Noto+Sans+JP:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ css_asset('css/login.css') }}">
  <style>
    .verify-card {
      max-width: 440px; width: 92%; margin: auto;
      background: #fff; border: 1px solid #e8d5b7;
      border-radius: 6px; padding: 48px 40px; text-align: center;
      box-shadow: 0 8px 32px rgba(61,47,37,0.1);
    }
    .verify-icon { font-size: 3rem; margin-bottom: 20px; }
    .verify-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem; font-weight: 400;
      color: #3d2f25; margin-bottom: 14px;
    }
    .verify-msg {
      font-size: 0.9rem; color: #7a6555;
      line-height: 1.9; margin-bottom: 28px;
    }
    .verify-btn {
      display: inline-block; padding: 12px 32px;
      background: #b38b59; color: #fff;
      border-radius: 3px; text-decoration: none;
      font-size: 0.88rem; letter-spacing: 2px;
      font-family: 'Noto Sans JP', sans-serif;
      transition: background 0.2s;
    }
    .verify-btn:hover { background: #9a7447; }
    .verify-btn--outline {
      background: transparent; border: 1px solid #b38b59; color: #b38b59;
    }
    .verify-btn--outline:hover { background: #b38b59; color: #fff; }
    .verify-rule {
      width: 36px; height: 1px; background: #d4b896; margin: 0 auto 20px;
    }
  </style>
</head>
<body>
  <div class="wedding-bg">
    @php $bg = \App\Models\SiteImage::forDisplay('login_bg'); @endphp
    <img src="{{ $bg?->url ?? asset('img/チャペル.jpg') }}" alt="">
  </div>

  <div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;position:relative;z-index:1;">
    <div class="verify-card">

      @if ($status === 'success')
        @php
          $verifiedUser = $user ?? auth()->user();
          $nextRoute = $verifiedUser?->password_change_required ? 'password.change' : 'dashboard';
        @endphp
        <div class="verify-icon">✦</div>
        <div class="verify-rule"></div>
        <h1 class="verify-title">確認が完了しました</h1>
        @include('auth.partials.onboarding-steps', [
          'current' => 'password',
          'emailDone' => true,
          'passwordDone' => ! $verifiedUser?->password_change_required,
        ])
        <p class="verify-msg">
          メールアドレスの確認が完了しました。<br>
          @if ($verifiedUser?->password_change_required)
            次に安全のため新しいパスワードを設定してください。
          @else
            ウェディングサイトへようこそ。
          @endif
        </p>
        <a href="{{ route($nextRoute) }}" class="verify-btn">次へ進む</a>

      @elseif ($status === 'expired')
        <div class="verify-icon" style="color:#c0392b;">⚠</div>
        <div class="verify-rule"></div>
        <h1 class="verify-title">リンクが無効です</h1>
        <p class="verify-msg">
          このリンクは有効期限（24時間）が切れているか、<br>
          既に使用済みです。<br>
          ログイン後にメール登録画面から<br>
          確認メールを再送信してください。
        </p>
        <a href="{{ route('login') }}" class="verify-btn verify-btn--outline">ログインへ</a>

      @else
        <div class="verify-icon" style="color:#c0392b;">✕</div>
        <div class="verify-rule"></div>
        <h1 class="verify-title">無効なリンクです</h1>
        <p class="verify-msg">
          このリンクは正しくありません。<br>
          メール内のリンクをそのままクリックしてください。
        </p>
        <a href="{{ route('login') }}" class="verify-btn verify-btn--outline">ログインへ</a>
      @endif

    </div>
  </div>
</body>
</html>
