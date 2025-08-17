@extends('layouts.admin_app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<main class="register-container">
    <h2 class="title">勤怠一覧</h2>

    @php
    // 見出しの表示（Y/m）
    $heading = \Carbon\Carbon::createFromFormat('Y-m', $currentMonth)->format('Y/m');
    @endphp

    <div class="month-nav">
        <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">
            <input type="hidden" name="month" value="{{ $previousMonth }}">
            <button class="prev-month-button">&lt; 前月</button>
        </form>

        <div class="month-title">{{ $heading }}</div>

        <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">
            <input type="hidden" name="month" value="{{ $nextMonth }}">
            <button class="prev-month-button">翌月 &gt;</button>
        </form>
    </div>

    <div style="margin: 8px 0 16px; font-weight: 600;">
        対象ユーザー：{{ $user->name }}
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
                {{-- 日付は m/d(D) 表示（テストは部分一致 "01/10" を見る） --}}
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d(D)') }}</td>

                {{-- 出退勤は分までの表示に統一 --}}
                <td>
                    @if($attendance->clock_in)
                    {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>
                <td>
                    @if($attendance->clock_out)
                    {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>

                {{-- 休憩合計と勤務合計は既存のアクセサがあればそれを表示。なければフォールバック計算 --}}
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
                // 分単位で計算し、休憩を引く
                $mins = $out->diffInMinutes($in);
                // 休憩合計（上で求めた $totalBreakMin を再利用 or 再計算）
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

                {{-- 詳細リンクは統一パス /attendance/{id} --}}
                <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;">該当月の勤怠はありません</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</main>
@endsection