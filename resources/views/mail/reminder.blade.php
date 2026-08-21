<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $reminder->subject }}</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { -webkit-text-size-adjust: 100%; }
  @media (max-width: 600px) {
    .wrapper        { margin: 0 !important; border-radius: 0 !important; border-left: none !important; border-right: none !important; }
    .header         { padding: 32px 24px 28px !important; }
    .header__names  { font-size: 22px !important; }
    .body-cell      { padding: 28px 24px 24px !important; }
    .footer-cell    { padding: 18px 24px !important; }
  }
</style>
</head>
<body style="margin:0;padding:0;background:#fdfaf6;font-family:'Helvetica Neue',Arial,'Hiragino Kaku Gothic ProN','Hiragino Sans',Meiryo,sans-serif;color:#3d2f25;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fdfaf6;">
  <tr>
    <td align="center" style="padding:40px 16px;">

      <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" border="0"
             style="max-width:560px;background:#ffffff;border:1px solid #e8d5b7;border-radius:4px;overflow:hidden;">

        {{-- ヘッダー --}}
        <tr>
          <td class="header" align="center"
              style="background:#3d2f25;padding:44px 40px 38px;text-align:center;">
            <span style="display:block;font-family:'Helvetica Neue',Arial,sans-serif;font-size:11px;color:rgba(255,255,255,0.55);letter-spacing:6px;text-transform:uppercase;margin-bottom:24px;">
              Wedding Invitation
            </span>
            <span class="header__names"
                  style="display:block;font-family:Georgia,'Times New Roman',serif;font-size:32px;font-weight:400;letter-spacing:4px;color:#ffffff;line-height:1.2;margin-bottom:22px;">
              {{ $groomName }}
              <em style="font-style:italic;color:#e8c98a;letter-spacing:2px;">&amp;</em>
              {{ $brideName }}
            </span>
            <div style="width:50px;height:1px;background:rgba(232,201,138,0.5);margin:0 auto;"></div>
          </td>
        </tr>

        {{-- 本文 --}}
        <tr>
          <td class="body-cell" style="padding:40px 40px 32px;">

            {{-- カスタムメッセージ（改行を <br> に変換） --}}
            <div style="font-size:15px;line-height:2.0;color:#3d2f25;margin-bottom:32px;white-space:pre-line;">{{ $reminder->message }}</div>

            {{-- サイトへのリンク --}}
            <table width="100%" cellpadding="0" cellspacing="0" border="0">
              <tr>
                <td align="center" style="padding:0 0 32px;">
                  <a href="{{ $siteUrl }}/home"
                     style="display:inline-block;padding:14px 40px;background:#b38b59;color:#ffffff;text-decoration:none;border-radius:3px;font-size:15px;letter-spacing:2px;font-weight:500;">
                    招待サイトを開く
                  </a>
                </td>
              </tr>
            </table>

          </td>
        </tr>

        {{-- フッター --}}
        <tr>
          <td class="footer-cell" align="center"
              style="background:#fdfaf6;border-top:1px solid #f0ebe3;padding:22px 40px;text-align:center;">
            <p style="font-family:Georgia,'Times New Roman',serif;font-size:14px;color:#7a6555;letter-spacing:3px;margin-bottom:6px;">
              {{ $groomName }}
              <em style="font-style:italic;color:#b38b59;">&amp;</em>
              {{ $brideName }}
            </p>
            <p style="font-size:12px;color:#a89282;line-height:1.8;">
              このメールは事前に予約された自動配信です。こちらに返信いただいても確認できません。
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
