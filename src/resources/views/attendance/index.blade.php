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
            <a href="{{ route('attendance.list') }}" style="color: white; margin-left: 20px;">勤怠一覧</a>
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

        {{-- 休憩・休憩戻・退勤ボタンを出勤の下に表示 --}}
        @if ($status === '出勤中')
        <form method="POST" action="{{ route('attendance.breakin') }}">
            @csrf
            <button type="submit">休憩</button>
        </form>
        @endif

        @if ($status === '休憩中')
        <form method="POST" action="{{ route('attendance.breakout') }}">
            @csrf
            <button type="submit">休憩戻</button>
        </form>
        @endif

        {{-- 退勤ボタン --}}
        @if ($status === '出勤中' || $status === '休憩戻')
        <form method="POST" action="{{ route('attendance.clockout') }}">
            @csrf
            <button type="submit">退勤</button>
        </form>
        @endif

    </main>
</body>

</html>