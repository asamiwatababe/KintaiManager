@extends('layouts.app')

@section('title', '勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="container">
    <main class="register-container">

        {{-- 日付・時刻 --}}
        <h2>{{ $date }}</h2>
        <h1>{{ $time }}</h1>

        {{-- ステータス表示 --}}
        <div class="status-row" style="margin: 12px 0;">
            <span class="status-value" data-testid="attendance-status">{{ $status }}</span>
        </div>

        {{-- アクションボタン --}}
        @if ($status === '勤務外')
        <form method="POST" action="{{ route('attendance.clockin') }}">
            @csrf
            <button type="submit">出勤</button>
        </form>
        @elseif ($status === '勤務中')
        <form method="POST" action="{{ route('attendance.breakin') }}">
            @csrf
            <button type="submit">休憩入</button>
        </form>
        <form method="POST" action="{{ route('attendance.clockout') }}">
            @csrf
            <button type="submit">退勤</button>
        </form>
        @elseif ($status === '休憩中')
        <form method="POST" action="{{ route('attendance.breakout') }}">
            @csrf
            <button type="submit">休憩戻</button>
        </form>
        @elseif ($status === '退勤済')
        <p>お疲れ様でした。</p>
        @endif

    </main>
</div>
@endsection