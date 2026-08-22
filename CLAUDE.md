# Wedding App — CLAUDE.md

このファイルはコードを触る際の**必読ドキュメント**です。
設計方針・機能一覧・ルール・デプロイ手順をまとめています。

---

## プロジェクト概要

**結婚式招待サイト** — ゲストが招待状への出欠回答・アレルギー情報入力・ゲストブックへの書き込みなどをオンラインで行えるWebアプリ。新郎新婦側の管理者がすべてのコンテンツ・ゲスト情報を管理できる。

- **ドメイン**: `https://k-m-wedding815.com`
- **本番サーバー**: ConoHa VPS (`133.117.74.212`) / root
- **Gitリポジトリ**: `https://github.com/hamaguti0000/wedding-ai-system`

---

## 技術スタック

| 項目 | 内容 |
|---|---|
| フレームワーク | Laravel 12 / PHP 8.4 |
| DB | MySQL 8.4 |
| テンプレート | Blade（Inertia.js は使用しない） |
| 認証 | 独自実装（`username` + `password`）※Laravelデフォルトのメール認証とは別 |
| メール | AWS SES（`MAIL_MAILER=ses`）/ ローカルは Mailpit |
| ローカル環境 | Laravel Sail（Docker）— `vendor/bin/sail` で操作 |
| テスト | Pest（`vendor/bin/sail artisan test`） |
| CSS | 独自CSS（`public/css/`）/ Tailwindは使用しない |

---

## 認証・ロール設計

```
ゲスト (role='guest')  ─── /login → /home 以降のゲスト画面
管理者 (role='admin')  ─── /login → /admin/dashboard 以降の管理画面
```

- ログインは **username + password**（メールアドレスではない）
- ゲストアカウントは**管理者が作成**するか、`/register` で自己登録
- 管理者ミドルウェア: `app/Http/Middleware/AdminMiddleware.php`
- メール認証はオプション（未認証でもサイトは使える。認証済みの方が望ましい）

---

## 主要ファイル構成

```
app/
  Http/Controllers/
    AuthController.php          # ログイン・ログアウト
    AccountController.php       # ゲスト自己登録（/register）
    HomeController.php          # ホーム画面
    ProfileController.php       # プロフィール編集・メール変更
    InvitationController.php    # RSVP出欠回答
    EmailVerificationController.php  # メール認証（/verify）
    Admin*/                     # 管理者専用コントローラー群
  Models/
    User.php                    # 認証・アバター・メール認証ヘルパー
    WeddingSetting.php          # 式の設定（日時・会場・シャトルバス等）
    SiteImage.php               # 各ページのバナー・ヒーロー画像管理
    GuestProfile.php            # ゲストのRSVP情報
  Mail/
    EmailVerificationMail.php   # メール認証メール

resources/views/
  home.blade.php        # ゲスト向けホーム（ヒーロー・RSVP状態・お知らせ）
  login.blade.php       # ログイン（独自デザイン）
  create_user.blade.php # 新規登録（2ステップUI）
  verify.blade.php      # メール認証結果ページ
  invitation.blade.php  # RSVP出欠回答フォーム
  admin/
    media.blade.php         # メディア管理（ヒーロー・バナー画像・動画）
    settings.blade.php      # 式の情報設定

public/css/             # ページ別の独自CSS（Tailwindなし）
```

---

## 機能一覧

### ゲスト向け
| 機能 | ルート | 備考 |
|---|---|---|
| ログイン | `GET/POST /login` | username + password |
| 新規登録 | `GET/POST /register` | 2ステップフォーム |
| メール認証 | `GET /verify?token=xxx` | 24時間有効トークン |
| ホーム | `GET /home` | カウントダウン・RSVP状態・お知らせ |
| 出欠回答 | `GET/POST /invitation` | RSVP・アレルギー・メッセージ |
| プログラム | `GET /program` | 式次第タイムライン |
| アクセス | `GET /access` | 会場・地図・電車/車案内 |
| ゲストブック | `GET/POST /guestbook` | 絵文字スタンプ付きメッセージ壁 |
| ギャラリー | `GET /gallery` | 写真ギャラリー |
| お知らせ | `GET /news` `GET /news/{id}` | 複数レイアウト対応 |
| FAQ | `GET /faq` | よくある質問 |
| 席次確認 | `GET /seating` | 管理者公開時のみ表示 |
| プロフィール | `GET /profiles` | カップルプロフィール |

### 管理者向け（`/admin/` prefix）
| 機能 | 主なルート |
|---|---|
| ダッシュボード | `/admin` |
| ゲスト管理（CRUD） | `/admin/users` |
| RSVP集計・CSV | `/admin/rsvp` |
| 受付チェックイン（QR） | `/admin/check-in` |
| 席次表管理 | `/admin/seating` |
| **メディア管理** | `/admin/media` |
| お知らせ管理 | `/admin/news` |
| ギャラリー管理 | `/admin/gallery` |
| ゲストブック管理 | `/admin/guestbook` |
| プログラム管理 | `/admin/program` |
| タスク管理（当日役割） | `/admin/tasks` |
| FAQ管理 | `/admin/faq` |
| カップルプロフィール | `/admin/profiles` |
| **式の情報設定** | `/admin/settings` |
| ログイン履歴 | `/admin/login-history` |
| 監査ログ | `/admin/audit/check-in` |

---

## データベース主要テーブル

| テーブル | 用途 |
|---|---|
| `users` | 認証・ロール・アバター・メール認証トークン |
| `guest_profiles` | RSVP情報（参加/不参加・連絡先・アレルギー等） |
| `wedding_settings` | 式の設定（日時・会場・ヒーロー設定・シャトルバス等）※1行のみ |
| `site_images` | 各ページのバナー・ヒーロー画像（location別に管理） |
| `gallery_photos` | ギャラリー写真 |
| `news_items` | お知らせ（複数レイアウト対応） |
| `program_items` | 式次第 |
| `wedding_tasks` / `guest_task_assignments` | 当日役割の割り当て |
| `seating_tables` / `seats` / `seat_assignments` | 席次管理 |
| `guestbook_messages` | ゲストブック投稿 |
| `faqs` | FAQ |
| `login_histories` | ログイン履歴 |
| `check_in_audit_logs` | チェックイン監査ログ |

---

## メディア管理（SiteImage）

`site_images` テーブルの `location` カラムで管理場所を区別：

| location | 表示場所 |
|---|---|
| `hero` | ホームのヒーロー（スライドショー or 動画） |
| `login_bg` | ログイン背景 |
| `banner_invitation` | 招待状バナー |
| `banner_program` | プログラムバナー |
| `banner_access` | アクセスバナー |
| `banner_faq` | FAQバナー |
| `banner_gallery` | ギャラリーバナー |
| `banner_guestbook` | ゲストブックバナー |
| `banner_news` | お知らせバナー |
| `banner_profile` | プロフィールバナー |

- 画像未設定時は `public/img/チャペル.jpg` がフォールバック
- `AppServiceProvider` の ViewComposer で全バナービューに `$bannerImage` を自動注入
- `hero_type`: `slideshow`（クロスフェード）or `video`（背景動画）を `/admin/media` で切替

---

## バリデーションルール（統一）

| フィールド | ルール |
|---|---|
| パスワード | `min:8|max:255` — **全コントローラーで統一すること** |
| メール | `email:rfc,filter|max:255|unique:users,email` |
| 姓/名 | `required|string|max:50` |

---

## デプロイ手順

### 必須フロー（毎回）
```bash
# 1. テスト実行（全件パスしてから進む）
vendor/bin/sail artisan test

# 2. GitHubへプッシュ
git push origin main

# 3. VPSへデプロイ
ssh -i ~/.ssh/vps_key root@133.117.74.212 "
  cd /var/www/wedding-ai-system
  git fetch origin main && git reset --hard FETCH_HEAD
  composer install --no-dev --optimize-autoloader --quiet
  php8.4 artisan migrate --force
  php8.4 artisan config:cache
  php8.4 artisan route:cache
  php8.4 artisan view:cache

  # IMPORTANT: artisanをrootで実行するとfile cacheがroot所有になり、画面が500になる。
  # 必ず全artisan実行後の最後にWebサーバーユーザーへ戻すこと。
  chown -R www-data:www-data storage bootstrap/cache
  find storage bootstrap/cache -type d -exec chmod 775 {} \;
  find storage bootstrap/cache -type f ! -name .gitignore -exec chmod 664 {} \;
"
```

> **テストを通さずデプロイしない。** 過去に本番でエラーが発生した教訓から。

### SSH鍵
- ローカルの鍵: `~/.ssh/vps_key`
- VPSユーザー: `root`
- アプリパス: `/var/www/wedding-ai-system`
- PHPコマンド: `php8.4`（バージョン固定）

---

## テスト

```bash
vendor/bin/sail artisan test          # 全テスト
vendor/bin/sail artisan test --filter=RegistrationTest  # 登録のみ
```

| テストファイル | 内容 |
|---|---|
| `RegistrationTest.php` | 登録バリデーション26件 |
| `UserAvatarTest.php` | アバター機能 |
| `RouteAccessTest.php` | 主要ルートのアクセス権限 |
| `AdminSettingsTest.php` | 管理者設定 |
| `AdminSeatingApiTest.php` | 席次API |
| `GuestSeatingTest.php` | ゲスト席次表示 |
| `ProgramPageTest.php` | プログラムページ |
| `SessionExpiryTest.php` | セッション期限 |
| `ViewDataContractTest.php` | ビューへのデータ注入 |
| `AdminOperationsTest.php` | 管理者ダッシュボード |

---

## コーディング規則

- **コメントは「なぜ」だけ書く。何をしているかはコードで分かる**
- **Bladeテンプレート内でモデルを直接呼ぶときは完全修飾名**: `\App\Models\SiteImage::` （省略すると `Class "Xxx" not found` エラーになる）
- **Tailwindは使わない** — `public/css/` 配下のページ別CSSで管理
- **カラーパレット**: ゴールド `#b38b59`、ダークブラウン `#3d2f25`、ベージュ `#fdfaf6`
- **フォント**: `Playfair Display`（見出し）+ `Noto Sans JP`（本文）
- **Inertia.js は使わない** — Bladeに統一

---

## 環境変数（秘密情報はここに記載しない）

本番 `.env` に必要な主なキー（値は別管理）：

```
APP_URL=https://k-m-wedding815.com
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1

MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@k-m-wedding815.com
MAIL_FROM_NAME="K&M Wedding"
AWS_ACCESS_KEY_ID=        # SES本番申請通過後に設定
AWS_SECRET_ACCESS_KEY=    # 同上
AWS_DEFAULT_REGION=ap-northeast-1

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## 注意事項・過去の教訓

- `email_verified_at` は存在するが**認証は任意**。未認証でもサイトは使える
- メール送信は `try/catch` で包む — SES未設定時もユーザー操作を妨げない
- `SiteImage::forDisplay()` は `WeddingSetting` を **60秒キャッシュ**して取得（N+1対策）
- `wedding_settings` テーブルは**常に1行だけ**（`firstOrNew` / `updateOrCreate` で操作）
- `site_images` の `location` は `SiteImage::LOCATIONS` 定数で管理 — Bladeでは完全修飾名が必要
- ヒーロー画像未設定時は `public/img/チャペル.jpg` がフォールバック（削除禁止）
