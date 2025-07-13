<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '勤怠管理システム')</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    {{-- 必要であれば他のCSSもここに --}}
</head>

<body>
    @include('layouts.header') {{-- 共通のヘッダー部分 --}}

    <div class="container">
        @yield('content') {{-- 各画面の中身 --}}
    </div>
</body>

</html>