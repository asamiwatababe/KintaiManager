{{-- resources/views/admin/attendance/list.blade.php --}}
@extends('layouts.admin_app')

@section('title', ($mode ?? '') === 'daily'
? \Carbon\Carbon::parse($currentDate)->format('Y年n月j日').'の勤怠'
: '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pending_detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<main class="register-container">

    @if (($mode ?? '') === 'daily')
    <h2 class="title">{{ \Carbon\Carbon::parse($currentDate)->format('Y年n月j日') }}の勤怠</h2>

    <div class="month-nav">
        <form method="GET" action="{{ route('admin.attendance.list') }}">
            <input type="hidden" name="date" value="{{ $previousDate }}">
            <button class="nav-btn">&lt; 前日</button>
        </form>

        <div class="month-title">
            {{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}
        </div>

        <form method="GET" action="{{ route('admin.attendance.list') }}">
            <input type="hidden" name="date" value="{{ $nextDate }}">
            <button class="nav-btn">翌日 &gt;</button>
        </form>
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
                <td>{{ optional($attendance->user)->name ?? '-' }}</td>

                <td>
                    @if ($attendance->clock_in)
                    {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                    @else - @endif
                </td>
                <td>
                    @if ($attendance->clock_out)
                    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                    @else - @endif
                </td>

                <td>{{ $attendance->break_duration ?? '-' }}</td>
                <td>{{ $attendance->work_duration ?? '-' }}</td>

                <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ========== 月 次 ========== --}}
    @else
    <h2 class="title">勤怠一覧</h2>

    <div class="nav-bar">
        <form method="GET" action="{{ route('admin.attendance.list') }}">
            <input type="hidden" name="month" value="{{ $previousMonth }}">
            <button class="nav-btn">&lt; 前月</button>
        </form>

        <div class="nav-title">{{ \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('Y/m') }}</div>

        <form method="GET" action="{{ route('admin.attendance.list') }}">
            <input type="hidden" name="month" value="{{ $nextMonth }}">
            <button class="nav-btn">翌月 &gt;</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>名前</th>
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
                <td>{{ optional($attendance->user)->name ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d(D)') }}</td>

                <td>
                    @if ($attendance->clock_in)
                    {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                    @else - @endif
                </td>
                <td>
                    @if ($attendance->clock_out)
                    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                    @else - @endif
                </td>

                <td>{{ $attendance->break_duration ?? '-' }}</td>
                <td>{{ $attendance->work_duration ?? '-' }}</td>

                <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</main>
@endsection