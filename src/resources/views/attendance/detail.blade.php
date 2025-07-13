@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/pending_detail.blade.css') }}">
@section('content')

<body>
    <div class="container">
        <h2>勤怠詳細</h2>

        <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
            @csrf
            @method('PUT')

            <table>
                <tr>
                    <th>名前</th>
                    <td colspan="2">{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td><input type="time" name="clock_in" value="{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}" @if($attendance->status === 'pending') disabled @endif></td>
                    <td><input type="time" name="clock_out" value="{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}" @if($attendance->status === 'pending') disabled @endif></td>
                </tr>

                @foreach ($breaks as $i => $break)
                <tr>
                    <th>休憩{{ $i + 1 }}</th>
                    <td><input type="time" name="breaks[{{ $i }}][break_in]" value="{{ $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '' }}" @if($attendance->status === 'pending') disabled @endif></td>
                    <td><input type="time" name="breaks[{{ $i }}][break_out]" value="{{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '' }}" @if($attendance->status === 'pending') disabled @endif></td>
                </tr>
                @endforeach

                <tr>
                    <th>休憩{{ count($breaks) + 1 }}</th>
                    <td><input type="time" name="breaks[{{ count($breaks) }}][break_in]" value="" @if($attendance->status === 'pending') disabled @endif></td>
                    <td><input type="time" name="breaks[{{ count($breaks) }}][break_out]" value="" @if($attendance->status === 'pending') disabled @endif></td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td colspan="2">
                        <textarea name="note" rows="3" @if($attendance->status === 'pending') disabled @endif>{{ $attendance->note }}</textarea>
                    </td>
                </tr>
            </table>
            <div class="btn-submit">
                @if ($attendance->status === 'pending')
                <p class="note">※承認待ちのため修正はできません。</p>
                @else
                <button type="submit" class="btn btn-primary">修正</button>
                @endif
            </div>
        </form>
    </div>
</body>

</html>