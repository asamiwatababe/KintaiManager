@extends('layouts.app')
    <link rel="stylesheet" href="{{ asset('css/pending_detail.blade.css') }}">
@section('content')
<body>
    <div class="container">
        <h2>勤怠詳細</h2>
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
@endsection