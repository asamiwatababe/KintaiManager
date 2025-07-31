@extends('layouts.app')

@section('content')
<div class="container">
    <h2>勤怠詳細</h2>

    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- バリデーションエラーメッセージ --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <table>
            <tr>
                <th>名前</th>
                <td colspan="2">{{ $user->name }}</td>
            </tr>
            <tr>
                <th>出勤</th>
                <td colspan="2">
                    <input type="time" name="clock_in" value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}">
                </td>
            </tr>
            <tr>
                <th>退勤</th>
                <td colspan="2">
                    <input type="time" name="clock_out" value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}">
                </td>
            </tr>

            @foreach ($breaks as $i => $break)
            <tr>
                <th>休憩{{ $i + 1 }}</th>
                <td>
                    <input type="time" name="breaks[{{ $i }}][break_in]" value="{{ \Carbon\Carbon::parse($break->break_in)->format('H:i') }}">
                </td>
                <td>
                    <input type="time" name="breaks[{{ $i }}][break_out]" value="{{ \Carbon\Carbon::parse($break->break_out)->format('H:i') }}">
                </td>
            </tr>
            @endforeach

            <tr>
                <th>備考</th>
                <td colspan="2">
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                </td>
            </tr>
        </table>

        <button type="submit" class="btn btn-primary">修正申請</button>
    </form>
</div>
@endsection