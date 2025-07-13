<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>管理者ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>
    <header class="header">
        <div class="logo-text">COACHTECH</div>
    </header>

    <main class="register-container">
        <h1>管理者ログイン</h1>

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}">
                @error('email')
                <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password">
                @error('password')
                <p class="error">{{ $message }}</p>
                @enderror
            </div>

            @if(session('status'))
            <p class="error">{{ session('status') }}</p>
            @endif

            <button type="submit">ログインする</button>
        </form>
    </main>
</body>

</html>