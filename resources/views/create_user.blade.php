<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ユーザー登録</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ css_asset('css/create_user.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&family=Playfair+Display:wght@500;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="user-register">
        <h2>ユーザー登録</h2>

        @if ($errors->any())
        <div class="reg-errors">
            <ul>
                @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- step1(email)のみエラーならSTEP2から開始、step0にもエラーがあればSTEP1から --}}
        @php
            $hasStep0Errors = $errors->hasAny(['last_name','first_name','password','password_confirmation']);
            $startStep = (!$hasStep0Errors && $errors->has('email')) ? 1 : 0;
        @endphp

        {{-- ステッププログレスバー --}}
        <div class="step-progress" id="stepProgress" aria-label="登録の進捗">
            <div class="step-progress__track">
                <div class="step-progress__fill" id="stepFill"></div>
            </div>
            <div class="step-progress__labels">
                <span class="step-progress__dot step-progress__dot--active" data-step="0">
                    <span class="step-progress__dot-num">1</span>
                    <span class="step-progress__dot-label">お名前</span>
                </span>
                <span class="step-progress__dot" data-step="1">
                    <span class="step-progress__dot-num">2</span>
                    <span class="step-progress__dot-label">メール</span>
                </span>
            </div>
        </div>

        <div class="form-slider-wrapper">
            <form id="registerForm"
                method="POST"
                action="{{ route('register.post') }}"
                class="form-slider"
                data-start-step="{{ $startStep }}">

                @csrf

                <!-- STEP 1: お名前・パスワード -->
                <div class="form-step">
                    <p class="step-label">STEP 1 / 2 &nbsp;お名前・パスワード</p>

                    <label>
                        <span>姓 <em>*</em></span>
                        <input type="text" name="last_name"
                            value="{{ old('last_name') }}"
                            required>
                    </label>

                    <label>
                        <span>名 <em>*</em></span>
                        <input type="text" name="first_name"
                            value="{{ old('first_name') }}"
                            required>
                    </label>

                    <label>
                        <span>パスワード <em>*</em>（6文字以上）</span>
                        <div class="password-field">
                            <input id="reg_password" type="password" name="password"
                                minlength="6"
                                required>
                            <button type="button" class="password-toggle" data-password-toggle="reg_password" aria-label="パスワードを表示" aria-pressed="false">
                                <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </label>

                    <label>
                        <span>パスワード（確認） <em>*</em></span>
                        <div class="password-field">
                            <input id="reg_password_confirmation" type="password" name="password_confirmation"
                                minlength="6"
                                required>
                            <button type="button" class="password-toggle" data-password-toggle="reg_password_confirmation" aria-label="パスワードを表示" aria-pressed="false">
                                <svg class="icon-eye" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </label>

                    <div class="form-navigation">
                        <button type="button" class="next-btn">次へ</button>
                    </div>
                </div>

                <!-- STEP 2: メールアドレス -->
                <div class="form-step">
                    <p class="step-label">STEP 2 / 2 &nbsp;メールアドレス</p>

                    <label>
                        <span>メールアドレス <em>*</em></span>
                        <input type="email" name="email"
                            value="{{ old('email') }}"
                            required>
                    </label>

                    <p class="step-note">
                        ご出席の回答・アレルギー情報などは<br>
                        登録後に「招待状」ページから入力できます。
                    </p>

                    <div class="form-navigation">
                        <button type="button" class="prev-btn">戻る</button>
                        <button type="submit" class="submit-btn">登録する</button>
                    </div>
                </div>

            </form>
        </div>

        <p style="text-align:center; margin-top:20px;">
            すでにアカウントをお持ちの方は
            <a href="{{ route('login') }}">こちらからログイン</a>
        </p>
    </div>

    <script src="{{ asset('js/create_user.js') }}"></script>
</body>

</html>
