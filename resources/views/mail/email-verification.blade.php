<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>メールアドレスの確認</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif;
    background: #fdfaf6;
    color: #3d2f25;
    -webkit-text-size-adjust: 100%;
  }
  .wrapper {
    max-width: 560px;
    margin: 40px auto;
    background: #ffffff;
    border: 1px solid #e8d5b7;
    border-radius: 4px;
    overflow: hidden;
  }
  .header {
    background: #3d2f25;
    padding: 36px 40px;
    text-align: center;
  }
  .header__monogram {
    font-size: 1.6rem;
    color: #e8c98a;
    letter-spacing: 8px;
    font-weight: 300;
    margin-bottom: 6px;
  }
  .header__rule {
    width: 40px;
    height: 1px;
    background: rgba(232,201,138,0.4);
    margin: 0 auto 8px;
  }
  .header__tagline {
    font-size: 0.68rem;
    color: rgba(255,255,255,0.5);
    letter-spacing: 4px;
    text-transform: uppercase;
  }
  .body {
    padding: 40px 40px 32px;
  }
  .greeting {
    font-size: 1rem;
    line-height: 1.9;
    color: #3d2f25;
    margin-bottom: 28px;
  }
  .greeting strong {
    display: block;
    font-size: 1.05rem;
    margin-bottom: 16px;
  }
  .btn-wrap {
    text-align: center;
    margin: 32px 0;
  }
  .btn {
    display: inline-block;
    padding: 14px 40px;
    background: #b38b59;
    color: #ffffff !important;
    text-decoration: none;
    border-radius: 3px;
    font-size: 0.95rem;
    letter-spacing: 2px;
    font-weight: 500;
  }
  .note {
    font-size: 0.82rem;
    color: #7a6555;
    line-height: 1.8;
    margin-top: 24px;
    padding: 16px 20px;
    background: #fdfaf6;
    border-left: 3px solid #d4b896;
    border-radius: 0 4px 4px 0;
  }
  .url-fallback {
    margin-top: 20px;
    font-size: 0.76rem;
    color: #a89282;
    line-height: 1.8;
    word-break: break-all;
  }
  .url-fallback a { color: #b38b59; }
  .footer {
    background: #fdfaf6;
    border-top: 1px solid #f0ebe3;
    padding: 20px 40px;
    text-align: center;
    font-size: 0.74rem;
    color: #a89282;
    line-height: 1.8;
  }
  @media (max-width: 600px) {
    .wrapper  { margin: 0; border-radius: 0; border-left: none; border-right: none; }
    .header   { padding: 28px 24px; }
    .body     { padding: 28px 24px 24px; }
    .footer   { padding: 16px 24px; }
  }
</style>
</head>
<body>
<div class="wrapper">

  {{-- ヘッダー --}}
  <div class="header">
    <p class="header__monogram">{{ mb_substr($groomName, -1) }} &amp; {{ mb_substr($brideName, -1) }}</p>
    <div class="header__rule"></div>
    <p class="header__tagline">Wedding Invitation</p>
  </div>

  {{-- 本文 --}}
  <div class="body">
    <div class="greeting">
      <strong>{{ $user->name }} 様</strong>
      この度はご登録いただきありがとうございます。<br>
      以下のボタンをクリックして、メールアドレスの確認を完了してください。
    </div>

    <div class="btn-wrap">
      <a href="{{ $verifyUrl }}" class="btn">メールアドレスを確認する</a>
    </div>

    <div class="note">
      <strong>ご注意</strong><br>
      このリンクは送信から <strong>24時間</strong> 有効です。<br>
      メールが見つからない場合は、迷惑メールフォルダもご確認ください。<br>
      身に覚えのない場合は、このメールを無視してください。<br>
      アカウントへの不正アクセスは発生していません。
    </div>

    <div class="url-fallback">
      ボタンが押せない場合は、以下のURLをブラウザにコピーしてください：<br>
      <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a>
    </div>
  </div>

  {{-- フッター --}}
  <div class="footer">
    {{ $groomName }} &amp; {{ $brideName }} Wedding<br>
    このメールは自動送信されています。返信はできません。
  </div>

</div>
</body>
</html>
