<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="user-register">
        <h2>ログイン</h2>

        @if($errors->has('login_error'))
        <p class="message">{{ $errors->first('login_error') }}</p>
        @endif

        <form method="POST" class="user-form-grid" action="{{ route('login') }}">
            @csrf
            <label>
                <span>メールアドレス</span>
                <input type="email" name="login_id" value="{{ old('login_id') }}" required autofocus>
            </label>

            <label>
                <span>パスワード</span>
                <input type="password" name="password" required>
            </label>

            <button type="submit">ログイン</button>
        </form>
    </div>
</body>

</html>