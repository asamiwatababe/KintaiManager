{{-- resources/views/admin/request/show.blade.php --}}
@extends('layouts.admin_app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h2 class="title">勤怠詳細</h2>

    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ $request->clock_in ? \Carbon\Carbon::parse($request->clock_in)->format('H:i') : '--:--' }}
                〜
                {{ $request->clock_out ? \Carbon\Carbon::parse($request->clock_out)->format('H:i') : '--:--' }}
            </td>
        </tr>
        <tr>
            <th>休憩1</th>
            <td>
                {{ $request->break_in ? \Carbon\Carbon::parse($request->break_in)->format('H:i') : '--:--' }}
                〜
                {{ $request->break_out ? \Carbon\Carbon::parse($request->break_out)->format('H:i') : '--:--' }}
            </td>
        </tr>
        {{-- 休憩2は申請テーブルにカラムがないため表示しません --}}
        <tr>
            <th>備考</th>
            <td>{{ $request->note }}</td>
        </tr>
    </table>

    <form method="POST" action="{{ route('stamp_correction_request.approve', $request->id) }}">
        @csrf
        <div class="button-area">
            <button type="submit" class="submit-btn"
                @if($request->status === 'approved') disabled @endif>
                {{ $request->status === 'approved' ? '承認済み' : '承認する' }}
            </button>
        </div>
    </form>
</div>
@endsection