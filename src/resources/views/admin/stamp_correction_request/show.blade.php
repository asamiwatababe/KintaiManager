@extends('layouts.admin_app')

@section('title', '勤怠詳細')

@section('content')
<div class="detail-card">
    <h2 class="title">勤怠詳細</h2>
    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ optional($attendance->date) ? \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') : '未登録' }}</td>

        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>{{ $attendance_correct_request->clock_in }} 〜 {{ $attendance_correct_request->clock_out }}</td>
        </tr>

        @foreach ($attendance_correct_request->breaks as $index => $break)
        <tr>
            <th>休憩{{ $index + 1 }}</th>
            <td>{{ $break->break_in }} 〜 {{ $break->break_out }}</td>
        </tr>
        @endforeach

        <tr>
            <th>備考</th>
            <td>{{ $attendance_correct_request->reason }}</td>
        </tr>
    </table>

    <form method="POST" action="{{ route('stamp_correction_request.approve', $request->id) }}">
        @csrf
        <button type="submit" class="submit-btn">承認済み</button>
    </form>
</div>
@endsection