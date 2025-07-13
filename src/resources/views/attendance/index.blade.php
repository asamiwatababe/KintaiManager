@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@section('content')

<main class="register-container">
    {{-- ステータスバッジ --}}
    @if ($status === '未出勤')
    <span>勤務外</span>
    @elseif ($status === '出勤中')
    <span>出勤中</span>
    @elseif ($status === '休憩中')
    <span>休憩中</span>
    @elseif ($status === '退勤済')
    <span>退勤済</span>
    @endif
<body>
    <h2>{{ $date }}</h2>
    <h1>{{ $time }}</h1>

    {{-- 出勤ボタン（未出勤時のみ表示） --}}
    @if ($status === '未出勤')
    <form method="POST" action="{{ route('attendance.clockin') }}">
        @csrf
        <button type="submit">出勤</button>
    </form>
    @endif

    {{-- 出勤中：休憩と退勤ボタン表示 --}}
    @if ($status === '出勤中')
    <form method="POST" action="{{ route('attendance.breakin') }}">
        @csrf
        <button type="submit">休憩入</button>
    </form>
    <form method="POST" action="{{ route('attendance.clockout') }}">
        @csrf
        <button type="submit">退勤</button>
    </form>
    @endif

    {{-- 休憩中：休憩戻と退勤ボタン表示 --}}
    @if ($status === '休憩中')
    <form method="POST" action="{{ route('attendance.breakout') }}">
        @csrf
        <button type="submit">休憩戻</button>
    </form>
    @endif

    @if ($status === '休憩戻')
    <form method="POST" action="{{ route('attendance.clockout') }}">
        @csrf
        <button type="submit">退勤</button>
    </form>
    @endif

    @if ($status === '退勤済')
    <p>お疲れ様でした。</p>
    @endif

</main>
</body>
@endsection