{{-- resources/views/attendance/detail.blade.php --}}
@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
@php
// 保存済みの休憩（最初の1本）を初期値として使う
$firstBreak = optional($attendance->breaks)->sortBy('break_in')->first();
$initBreakIn = $firstBreak && $firstBreak->break_in ? \Carbon\Carbon::parse($firstBreak->break_in)->format('H:i') : '';
$initBreakOut = $firstBreak && $firstBreak->break_out ? \Carbon\Carbon::parse($firstBreak->break_out)->format('H:i') : '';
@endphp

<div class="container">
    <h2 class="title">勤怠詳細</h2>
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

            <tr>
                <th>備考</th>
                <td>
                    <input type="text" name="note" value="{{ $attendance->note }}">
                    @error('note') <p class="error">{{ $message }}</p> @enderror
                </td>
            </tr>
        </table>

        <div class="button-area">
            <button type="submit" class="submit-btn">修正申請</button>
        </div>
    </form>
</div>
@endsection