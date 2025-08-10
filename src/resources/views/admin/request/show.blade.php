@extends('layouts.admin_app')

@section('title', '勤怠詳細')

@section('content')
<div class="detail-card">
    <h2 class="title">勤怠詳細</h2>

    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>
                @if(!empty($attendance) && !empty($attendance->date))
                {{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}
                @else
                未登録
                @endif
            </td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>{{ $request->clock_in }} 〜 {{ $request->clock_out }}</td>
        </tr>

        @if(!empty($attendance) && $attendance->breaks && $attendance->breaks->count())
        @foreach ($attendance->breaks as $index => $break)
        <tr>
            <th>休憩{{ $index + 1 }}</th>
            <td>
                {{ $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '-' }}
                〜
                {{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '-' }}
            </td>
        </tr>
        @endforeach
        @else
        <tr>
            <th>休憩</th>
            <td>-</td>
        </tr>
        @endif

        <tr>
            <th>備考</th>
            <td>{{ $request->reason ?? $request->note ?? '-' }}</td>
        </tr>
    </table>

    {{-- ボタン出し分け：承認待ちのみ押下可、承認済みは無効表示 --}}
    @if ($request->status === 'pending')
    <form method="POST" action="{{ route('stamp_correction_request.approve', $request->id) }}" style="margin-top:16px;">
        @csrf
        <button type="submit" class="submit-btn">承認する</button>
    </form>
    @else
    <button type="button" class="submit-btn" disabled style="opacity:.6;cursor:not-allowed;margin-top:16px;">
        承認済み
    </button>
    @endif
</div>
@endsection