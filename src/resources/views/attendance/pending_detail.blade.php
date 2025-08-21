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

        {{-- 出勤・退勤（表示用変数を必ず使う） --}}
        <tr>
            <th>出勤・退勤</th>
            <td>{{ $displayClockIn  !== '' ? $displayClockIn  : '--:--' }}</td>
            <td>{{ $displayClockOut !== '' ? $displayClockOut : '--:--' }}</td>
        </tr>

        {{-- 休憩（表示用変数を必ず使う） --}}
        <tr>
            <th>休憩</th>
            <td>{{ $break1In  !== '' ? $break1In  : '--:--' }}</td>
            <td>{{ $break1Out !== '' ? $break1Out : '--:--' }}</td>
        </tr>

        {{-- 備考（表示用変数を必ず使う） --}}
        <tr>
            <th>備考</th>
            <td colspan="2">{{ trim($displayNote ?? '') !== '' ? trim($displayNote) : '―' }}</td>
        </tr>
    </table>

    @if(!empty($isLocked))
    <p class="note">※承認待ちのため修正はできません。</p>
    @endif
</div>
@endsection