@extends('layouts.admin_app')

@section('title', 'スタッフ勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<h2 class="title">{{ $user->name }}さんの勤怠</h2>

<div class="date-navigation">
    <span class="month">{{ $currentMonth }}</span>
</div>

<table class="attendance-table">
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
            <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
            <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
            <td>
                @php
                $totalBreak = 0;
                foreach ($attendance->breaks as $break) {
                if ($break->break_in && $break->break_out) {
                $totalBreak += \Carbon\Carbon::parse($break->break_out)
                ->diffInMinutes(\Carbon\Carbon::parse($break->break_in));
                }
                }
                @endphp
                {{ floor($totalBreak / 60) }}:{{ sprintf('%02d', $totalBreak % 60) }}
            </td>
            <td>
                @if ($attendance->clock_in && $attendance->clock_out)
                @php
                $start = \Carbon\Carbon::parse($attendance->clock_in);
                $end = \Carbon\Carbon::parse($attendance->clock_out);
                $work = $end->diffInMinutes($start) - $totalBreak;
                @endphp
                {{ floor($work / 60) }}:{{ sprintf('%02d', $work % 60) }}
                @else
                -
                @endif
            </td>
            <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="button-area">
    <form method="GET" action="{{ route('admin.attendance.staff.csv', $user->id) }}">
        <button class="submit-btn">CSV出力</button>
    </form>
</div>
@endsection