@extends('layouts.admin_app')

@section('title', '管理者勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<h2 class="title">勤怠詳細</h2>

@php
$sorted = optional($attendance->breaks)->sortBy('break_in')->values();

$b1 = $sorted->get(0);
$b2 = $sorted->get(1);

$initBreak1In = $b1 && $b1->break_in ? \Carbon\Carbon::parse($b1->break_in)->format('H:i') : '';
$initBreak1Out = $b1 && $b1->break_out ? \Carbon\Carbon::parse($b1->break_out)->format('H:i') : '';

$initBreak2In = $b2 && $b2->break_in ? \Carbon\Carbon::parse($b2->break_in)->format('H:i') : '';
$initBreak2Out = $b2 && $b2->break_out ? \Carbon\Carbon::parse($b2->break_out)->format('H:i') : '';
@endphp

<form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}" autocomplete="off">
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

        {{-- 出勤・退勤（バリデ時は old、通常はDB値） --}}
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

        {{-- 休憩1 --}}
        <tr>
            <th>休憩</th>
            <td>
                <input type="time" name="break_1_start" value="{{ old('break_1_start', $initBreak1In) }}">
                ～
                <input type="time" name="break_1_end" value="{{ old('break_1_end', $initBreak1Out) }}">
                @error('break_1_start') <p class="error">{{ $message }}</p> @enderror
                @error('break_1_end') <p class="error">{{ $message }}</p> @enderror
            </td>
        </tr>

        {{-- 休憩2 --}}
        <tr>
            <th>休憩2</th>
            <td>
                <input type="time" name="break_2_start" value="{{ old('break_2_start', $initBreak2In) }}">
                ～
                <input type="time" name="break_2_end" value="{{ old('break_2_end', $initBreak2Out) }}">
                @error('break_2_start') <p class="error">{{ $message }}</p> @enderror
                @error('break_2_end') <p class="error">{{ $message }}</p> @enderror
            </td>
        </tr>

        {{-- 備考（初期は空。エラー時のみ old を保持） --}}
        <tr>
            <th>備考</th>
            <td>
                <input type="text" name="memo" value="{{ old('memo', '') }}" placeholder="備考を入力">
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