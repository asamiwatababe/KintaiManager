<!-- <!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>管理者画面</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('styles') {{-- カスタムCSS用 --}}
</head>

<body>
    @include('admin.components.admin_header') {{-- ここで admin_header.blade.php を読み込む --}}

    <div class="content">
        @yield('content')
    </div>
</body>

</html> -->
<!DOCTYPE html>
<html>

<head>
    <title>管理者画面</title>
</head>

<body>
    @include('admin.components.admin_header')

    <div class="content">
        @yield('content')
    </div>
</body>

</html>


</html>