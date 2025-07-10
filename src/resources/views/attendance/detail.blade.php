<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>勤怠詳細</title>
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

        .btn-submit {
            text-align: right;
            margin-top: 30px;
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

        input[type="time"],
        textarea {
            width: 100%;
            padding: 5px;
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

        <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
            @csrf
            @method('PUT')

            <table>
                <tr>
                    <th>名前</th>
                    <td colspan="2">{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td colspan="2">
                        <input type="time" name="clock_in" value="{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}">
                        ～
                        <input type="time" name="clock_out" value="{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}">
                    </td>
                </tr>
                <tr>
                    <th>休憩</th>
                    <td colspan="2">
                        <div id="breaks-container">
                            @foreach ($breaks as $i => $break)
                            <div class="break-row">
                                <input type="time" name="breaks[{{ $i }}][break_in]"
                                    value="{{ $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '' }}">
                                ～
                                <input type="time" name="breaks[{{ $i }}][break_out]"
                                    value="{{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '' }}">
                            </div>
                            @endforeach

                            {{-- 空の1件 --}}
                            <div class="break-row">
                                <input type="time" name="breaks[{{ count($breaks) }}][break_in]" value="">
                                ～
                                <input type="time" name="breaks[{{ count($breaks) }}][break_out]" value="">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td colspan="2">
                        <textarea name="note" rows="3">{{ $attendance->note }}</textarea>
                    </td>
                </tr>
            </table>

            <div class="btn-submit">
                @if ($attendance->status === 'pending')
                <p class="note">※承認待ちのため修正はできません。</p>
                @else
                <button type="submit" class="btn btn-primary">修正</button>
                @endif
            </div>
        </form>
    </div>
</body>

</html>