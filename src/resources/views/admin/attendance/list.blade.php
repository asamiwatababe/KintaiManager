{{-- resources/views/admin/attendance/list.blade.php --}}
@extends('layouts.admin_app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pending_detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<main class="register-container">
    <h2>勤怠一覧</h2>

    <div class="month-nav">
        {{-- 前日へ --}}
        <form method="GET" action="{{ route('admin.attendance.list') }}">
            <input type="hidden" name="date" value="{{ $previousDate }}">
            <button class="prev-month-button">&lt; 前日</button>
        </form>

        {{-- 表示中の日付（一般の「月タイトル」に合わせた見た目クラスを流用） --}}
        <div class="month-title">{{ \Carbon\Carbon::parse($currentDate)->format('Y/m/d') }}</div>

        {{-- 翌日へ --}}
        <form method="GET" action="{{ route('admin.attendance.list') }}">
            <input type="hidden" name="date" value="{{ $nextDate }}">
            <button class="prev-month-button">翌日 &gt;</button>
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
                <td>{{ $attendance->clock_in ?? '-' }}</td>
                <td>{{ $attendance->clock_out ?? '-' }}</td>
                <td>{{ $attendance->break_duration ?? '-' }}</td>
                <td>{{ $attendance->work_duration ?? '-' }}</td>
                <td>
                    {{-- 詳細パスは共通の /attendance/{id} に統一 --}}
                    <a href="{{ route('attendance.show', $attendance->id) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</main>
@endsection