@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/pending_detail.blade.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@section('content')

<body>
    <main class="register-container">
        <h2>勤怠一覧</h2>

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
                    <!-- statusがpendingの場合は承認待ち画面に遷移 -->
                    <td>@if ($attendance->status === 'pending')
                        <a href="{{ route('attendance.pending', $attendance->id) }}">詳細</a>
                        @else
                        <a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</body>
@endsection