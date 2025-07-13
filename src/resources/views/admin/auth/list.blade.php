@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">

<main class="register-container">
    <h2>{{ \Carbon\Carbon::parse($currentDate)->format('Y年n月j日') }}の勤怠</h2>

    <div class="date-nav">
        <a href="{{ route('admin.attendance.list', ['date' => $previousDate]) }}">← 前日</a>
        <span>{{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}</span>
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日 →</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>名前</th>
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
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->clock_in ?? '' }}</td>
                <td>{{ $attendance->clock_out ?? '' }}</td>
                <td>{{ $attendance->break_duration ?? '-' }}</td>
                <td>{{ $attendance->work_duration ?? '-' }}</td>
                <td>
                    @if ($attendance->status === 'pending')
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
@endsection