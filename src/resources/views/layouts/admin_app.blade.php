{{-- resources/views/layouts/admin_app.blade.php --}}
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', '管理者ページ')</title>
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    @yield('css')
</head>

<body>
    @include('layouts.admin_header') {{-- 管理者用ヘッダー --}}
    <div class="container">
        @yield('content')
    </div>
</body>

</html>