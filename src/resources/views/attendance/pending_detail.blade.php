@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pending_detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h2>勤怠詳細</h2>
    <table>
        <tr>
            <th>名前</th>
            <td colspan="2">{{ $user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td colspan="2">
                {{ $attendance ? \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') : '勤怠データなし' }}
            </td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '--:--' }}
            </td>
            <td>
                {{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '--:--' }}
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                {{ $attendance && $attendance->breaks && $attendance->breaks->get(0) && $attendance->breaks->get(0)->break_in ? \Carbon\Carbon::parse($attendance->breaks->get(0)->break_in)->format('H:i') : '--:--' }}
            </td>
            <td>
                {{ $attendance && $attendance->breaks && $attendance->breaks->get(0) && $attendance->breaks->get(0)->break_out ? \Carbon\Carbon::parse($attendance->breaks->get(0)->break_out)->format('H:i') : '--:--' }}
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td colspan="2">{{ $attendance && $attendance->note ? $attendance->note : '―' }}</td>
        </tr>
    </table>

    <p class="note">※承認待ちのため修正はできません。</p>
</div>
@endsection