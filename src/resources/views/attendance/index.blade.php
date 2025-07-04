<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>打刻</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>
    <header class="header">
        <div class="logo-text">COACHTECH</div>
        <nav style="float: right; margin-top: -30px;">
            <a href="#" style="color: white; margin-left: 20px;">勤怠</a>
            <a href="#" style="color: white; margin-left: 20px;">勤怠一覧</a>
            <a href="#" style="color: white; margin-left: 20px;">申請</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button style="background: none; border: none; color: white; margin-left: 20px; cursor: pointer;">ログアウト</button>
            </form>
        </nav>
    </header>

    <main class="register-container">
        <div>
            <span style="background: #eee; padding: 5px 10px; border-radius: 10px; font-size: 12px;">勤務外</span>
        </div>
        <h2>{{ $date }}</h2>
        <h1 style="font-size: 48px;">{{ $time }}</h1>

        <form method="POST" action="{{ route('attendance.clockin') }}">
            @csrf
            <button type="submit">出勤</button>
        </form>
        @if (session('success'))
        <p style="color: green">{{ session('success') }}</p>
        @endif
        @if (session('error'))
        <p style="color: red">{{ session('error') }}</p>
        @endif

    </main>
</body>

</html>