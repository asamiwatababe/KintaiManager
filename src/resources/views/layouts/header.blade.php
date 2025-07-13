<header class="header">
    <div class="logo-text">COACHTECH</div>
    <nav>
        <a href="{{ route('attendance') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('stamp_correction_request.list') }}">申請</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">ログアウト</button>
        </form>
    </nav>
</header>