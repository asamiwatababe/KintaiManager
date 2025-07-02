<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>会員登録</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>
    <header class="header">
        <div class="logo-text">COACHTECH</div>
    </header>

    <main class="register-container">
        <h1>会員登録</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <label>名前</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            @error('name') <p class="error">{{ $message }}</p> @enderror

            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            @error('email') <p class="error">{{ $message }}</p> @enderror

            <label>パスワード</label>
            <input type="password" name="password" required>
            @error('password') <p class="error">{{ $message }}</p> @enderror

            <label>パスワード確認</label>
            <input type="password" name="password_confirmation" required>

            <button type="submit">登録する</button>
        </form>

        <a class="login-link" href="{{ route('login') }}">ログインはこちら</a>
    </main>
</body>

</html>