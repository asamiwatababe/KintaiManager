@extends('layouts.admin_app')

@section('title', '管理者勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<h2 class="title">勤怠詳細</h2>
<form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
    @csrf
    @method('PUT')
    <table>
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in) }}">
                ～
                <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out) }}">
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                <input type="time" name="break_start" value="{{ old('break_start', $attendance->break_start) }}">
                ～
                <input type="time" name="break_end" value="{{ old('break_end', $attendance->break_end) }}">
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>
                <input type="text" name="memo" value="{{ old('memo', $attendance->memo) }}">
            </td>
        </tr>
    </table>

    @if ($errors->any())
    <div class="errors">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="button-area">
        <button type="submit" class="submit-btn">修正</button>
    </div>
</form>
@endsection