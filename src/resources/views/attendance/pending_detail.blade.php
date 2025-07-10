<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>勤怠詳細（承認待ち）</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <style>
        body {
            background-color: #f4f4f8;
            font-family: sans-serif;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        tbody {
            background: white;
            border-radius: 20px;
            border-bottom: 2px solid #e4e4e4;
        }

        th {
            text-align: left;
            padding: 20px 0px 20px 30px;
            width: 30%;
            font-weight: unset;
            color: #868686;
        }

        td {
            padding: 10px;
            font-weight: bold;
            width: 35%;
            text-align: center;
        }

        tr {
            border-bottom: 2px solid #e4e4e4;
        }

        .note {
            color: red;
            text-align: right;
            margin-top: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: black;
            padding: 15px 30px;
        }

        .logo-text {
            font-size: 24px;
            color: white;
            font-weight: bold;
        }

        nav a,
        nav button {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <header class="header">
        <div class="logo-text">COACHTECH</div>
        <nav>
            <a href="#" style="color: white;">勤怠</a>
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a href="#">申請</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background: none; border: none; cursor: pointer;">ログアウト</button>
            </form>
        </nav>
    </header>


    <div class="container">
        <h2 style="margin-bottom: 20px;">勤怠詳細</h2>


        <table>
            <tr>
                <th>名前</th>
                <td colspan="1">{{ $user->name }}</td>
                <td></td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '--:--' }}</td>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '--:--' }}</td>
            </tr>
            <tr>
                <th>休憩</th>
                @if (isset($breaks[0]))
                <td>{{ \Carbon\Carbon::parse($breaks[0]->break_in)->format('H:i') }}</td>
                <td>{{ $breaks[0]->break_out ? \Carbon\Carbon::parse($breaks[0]->break_out)->format('H:i') : '--:--' }}</td>
                @else
                <td>--:--</td>
                <td>--:--</td>
                @endif
            </tr>
            <tr>
                <th>備考</th>
                <td colspan="2">{{ $attendance->note ?? '―' }}</td>
            </tr>
        </table>

        <p class="note">※承認待ちのため修正はできません。</p>

    </div>

</body>

</html>