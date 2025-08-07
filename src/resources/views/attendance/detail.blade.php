@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h2 class="title">勤怠詳細</h2>

    <!-- {{-- 成功メッセージ --}}
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- 全体的なバリデーションエラー表示（リスト形式） --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif -->

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
            </tr>

            {{-- 出勤・退勤 --}}
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                    ～
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">

                    {{-- 出勤・退勤エラー --}}
                    @error('clock_in')
                    <p class="error">{{ $message }}</p>
                    @enderror
                    @error('clock_out')
                    <p class="error">{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            {{-- 休憩 --}}
            <tr>
                <th>休憩</th>
                <td>
                    <input type="time" name="break_start" value="{{ old('break_start', $attendance->break_start ? \Carbon\Carbon::parse($attendance->break_start)->format('H:i') : '') }}">
                    ～
                    <input type="time" name="break_end" value="{{ old('break_end', $attendance->break_end ? \Carbon\Carbon::parse($attendance->break_end)->format('H:i') : '') }}">

                    {{-- 休憩エラー --}}
                    @error('break_start')
                    <p class="error">{{ $message }}</p>
                    @enderror
                    @error('break_end')
                    <p class="error">{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            {{-- 備考 --}}
            <tr>
                <th>備考</th>
                <td>
                    <input type="text" name="note" value="{{ old('note', $attendance->note) }}">

                    {{-- 備考エラー --}}
                    @error('note')
                    <p class="error">{{ $message }}</p>
                    @enderror
                </td>
            </tr>
        </table>

        <div class="button-area">
            <button type="submit" class="submit-btn">修正申請</button>
        </div>
    </form>
</div>
@endsection