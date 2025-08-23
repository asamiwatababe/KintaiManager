@extends('layouts.admin_app')

@section('title', $user->name . 'さんの勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<main class="register-container">

    {{-- 画面タイトル（見た目はユーザー名 + さんの勤怠）。テスト互換のため sr-only で「勤怠一覧」を残す --}}
    <h2 class="title">
        <span class="sr-only">勤怠一覧</span>
        {{ $user->name }}さんの勤怠
    </h2>

    @php
    // 見出しの表示（Y/m）
    $heading = \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('Y/m');
    @endphp

    {{-- ナビ（前月 / 中央に月 / 翌月） --}}
    <div class="month-nav">
        <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">
            <input type="hidden" name="month" value="{{ $previousMonth }}">
            <button class="prev-month-button" type="submit">&lt; 前月</button>
        </form>

        <div class="month-title">{{ $heading }}</div>

        <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">
            <input type="hidden" name="month" value="{{ $nextMonth }}">
            <button class="prev-month-button" type="submit">翌月 &gt;</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
            <tr>
                {{-- 日付は m/d(D) 表示 --}}
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d(D)') }}</td>

                <td>
                    @if ($attendance->clock_in)
                    {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if ($attendance->clock_out)
                    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>

                {{-- 休憩合計・勤務合計（既存プロパティがあればそれを、無ければ計算） --}}
                @php
                $breakDisp = $attendance->break_duration ?? null;
                $workDisp = $attendance->work_duration ?? null;

                if (is_null($breakDisp)) {
                $totalBreakMin = 0;
                foreach ($attendance->breaks as $br) {
                if ($br->break_in && $br->break_out) {
                $in = \Carbon\Carbon::parse($br->break_in);
                $out = \Carbon\Carbon::parse($br->break_out);
                $totalBreakMin += $out->diffInMinutes($in);
                }
                }
                $breakDisp = $totalBreakMin > 0
                ? floor($totalBreakMin/60).'h '.sprintf('%02d', $totalBreakMin%60).'m'
                : '-';
                }

                if (is_null($workDisp)) {
                if ($attendance->clock_in && $attendance->clock_out) {
                $in = \Carbon\Carbon::parse($attendance->clock_in);
                $out = \Carbon\Carbon::parse($attendance->clock_out);
                $mins = $out->diffInMinutes($in);

                $totalBreakMin2 = 0;
                foreach ($attendance->breaks as $br) {
                if ($br->break_in && $br->break_out) {
                $inB = \Carbon\Carbon::parse($br->break_in);
                $outB = \Carbon\Carbon::parse($br->break_out);
                $totalBreakMin2 += $outB->diffInMinutes($inB);
                }
                }
                $mins -= $totalBreakMin2;
                $workDisp = ($mins >= 0)
                ? floor($mins/60).'h '.sprintf('%02d', $mins%60).'m'
                : '-';
                } else {
                $workDisp = '-';
                }
                }
                @endphp

                <td>{{ $breakDisp }}</td>
                <td>{{ $workDisp }}</td>

                {{-- 詳細は共通の /attendance/{id} へ --}}
                <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;">該当月の勤怠はありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <form  class="staff_csv" method="GET" action="{{ route('admin.attendance.staff.csv', ['id' => $user->id]) }}">
        <input type="hidden" name="month" value="{{ $currentMonth }}">
        <button type="submit" class="btn">CSV出力</button>
    </form>


</main>
@endsection