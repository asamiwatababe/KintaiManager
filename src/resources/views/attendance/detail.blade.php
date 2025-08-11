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

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}" autocomplete="off">
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

            {{-- 出勤・退勤（※ old は使わず DB 値のみ） --}}
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in"
                        value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}"
                        autocomplete="off">
                    ～
                    <input type="time" name="clock_out"
                        value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}"
                        autocomplete="off">

                    @error('clock_in') <p class="error">{{ $message }}</p> @enderror
                    @error('clock_out') <p class="error">{{ $message }}</p> @enderror
                </td>
            </tr>

            {{-- 休憩（※ old は使わず break_times から反映） --}}
            <tr>
                <th>休憩</th>
                <td>
                    <input type="time" name="break_start" value="{{ $initBreakIn }}" autocomplete="off">
                    ～
                    <input type="time" name="break_end" value="{{ $initBreakOut }}" autocomplete="off">

                    @error('break_start') <p class="error">{{ $message }}</p> @enderror
                    @error('break_end') <p class="error">{{ $message }}</p> @enderror
                </td>
            </tr>

            {{-- 備考（※ 常に空で表示。old も DB も使わない） --}}
            <tr>
                <th>備考</th>
                <td>
                    <input type="text" name="note" value=""  autocomplete="off">
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