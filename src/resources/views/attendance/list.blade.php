<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>勤怠一覧</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }

        .month-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            gap: 30px;
        }

        .month-nav button {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
        }

        .month-title {
            font-size: 18px;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-text">COACHTECH</div>
        <nav>
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
        <h2 style="text-align: left;">勤怠一覧</h2>

        <div class="month-nav">
            <form method="GET" action="{{ route('attendance.list') }}">
                <input type="hidden" name="month" value="{{ $previousMonth }}">
                <button class="prev-month-button">&lt; 前月</button>
            </form>

            <div class="month-title">{{ \Carbon\Carbon::parse($currentMonth)->format('Y/m') }}</div>

            <form method="GET" action="{{ route('attendance.list') }}">
                <input type="hidden" name="month" value="{{ $nextMonth }}">
                <button class="prev-month-button">翌月 &gt;</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d(D)') }}</td>
                    <td>{{ $attendance->clock_in ?? '-' }}</td>
                    <td>{{ $attendance->clock_out ?? '-' }}</td>
                    <td>{{ $attendance->break_duration ?? '-' }}</td>
                    <td>{{ $attendance->work_duration ?? '-' }}</td>
                    <td><a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</body>

</html>