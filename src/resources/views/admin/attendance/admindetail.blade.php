{{-- resources/views/admin/attendance/admindetail.blade.php --}}
@extends('layouts.admin_app')

@section('title', '管理者勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<h2 class="title">勤怠詳細</h2>

@php
// 最初の休憩レコードを表示用に採用（必要なら複数対応に拡張可）
$firstBreak = optional($attendance->breaks)->sortBy('break_in')->first();
$initBreakIn = $firstBreak && $firstBreak->break_in ? \Carbon\Carbon::parse($firstBreak->break_in)->format('H:i') : '';
$initBreakOut = $firstBreak && $firstBreak->break_out ? \Carbon\Carbon::parse($firstBreak->break_out)->format('H:i') : '';
@endphp

<form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
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

        {{-- 出勤・退勤 --}}
        <tr>
            <th>出勤・退勤</th>
            <td>
                <input type="time" name="clock_in"
                    value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                ～
                <input type="time" name="clock_out"
                    value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                @error('clock_in') <p class="error">{{ $message }}</p> @enderror
                @error('clock_out') <p class="error">{{ $message }}</p> @enderror
            </td>
        </tr>

        {{-- 休憩（break_times から反映） --}}
        <tr>
            <th>休憩</th>
            <td>
                <input type="time" name="break_start" value="{{ old('break_start', $initBreakIn) }}">
                ～
                <input type="time" name="break_end" value="{{ old('break_end', $initBreakOut) }}">
                @error('break_start') <p class="error">{{ $message }}</p> @enderror
                @error('break_end') <p class="error">{{ $message }}</p> @enderror
            </td>
        </tr>

        {{-- 備考（note カラム） --}}
        <tr>
            <th>備考</th>
            <td>
                <input type="text" name="memo" value="{{ old('memo', $attendance->note) }}">
                @error('memo') <p class="error">{{ $message }}</p> @enderror
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