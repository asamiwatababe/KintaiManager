{{-- resources/views/admin/attendance/list.blade.php --}}
@extends('layouts.admin_app')

@section('title', '管理者勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pending_detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<main class="register-container">
    <h2>勤怠一覧</h2>

    <div class="month-nav">
        <form method="GET" action="{{ route('admin.attendance.list') }}">
            <input type="hidden" name="month" value="{{ $previousMonth }}">
            <button class="prev-month-button">&lt; 前月</button>
        </form>

        <div class="month-title">{{ \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('Y/m') }}</div>

        <form method="GET" action="{{ route('admin.attendance.list') }}">
            <input type="hidden" name="month" value="{{ $nextMonth }}">
            <button class="prev-month-button">翌月 &gt;</button>
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

                {{-- 時刻は秒を出さず H:i 表示 --}}
                <td>
                    @if ($attendance->clock_in)
                    {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if ($attendance->clock_out)
                    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>

                {{-- 休憩合計・勤務合計は実装に依存。あれば表示、無ければ "-" --}}
                <td>{{ $attendance->break_duration ?? '-' }}</td>
                <td>{{ $attendance->work_duration ?? '-' }}</td>

                <td>
                    <a href="{{ route('attendance.show', $attendance->id) }}">詳細</a>
                    {{-- 上記リンクは /attendance/{id} になる（テストがここを見る） --}}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</main>
@endsection