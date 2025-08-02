@extends('layouts.admin_app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_request_detail.css') }}">
@endsection

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
                {{ optional($request->date) ? \Carbon\Carbon::parse($request->date)->format('Y年n月j日') : '未登録' }}
            </td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>{{ $request->clock_in }} 〜 {{ $request->clock_out }}</td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                @if ($attendance && $attendance->breaks->get(0))
                {{ $attendance->breaks->get(0)->break_in }} 〜 {{ $attendance->breaks->get(0)->break_out }}
                @else
                -
                @endif
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                @if ($attendance && $attendance->breaks->get(1))
                {{ $attendance->breaks->get(1)->break_in }} 〜 {{ $attendance->breaks->get(1)->break_out }}
                @else
                -
                @endif
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{{ $request->note ?? '-' }}</td>
        </tr>
    </table>

    <!-- <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $request->id) }}" class="approve-form">
        @csrf
        <button type="submit" class="submit-btn">承認</button>
    </form> -->

    <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $request->id) }}" class="approve-form">
        @if ($request->status === 'pending')
        @csrf
        <button type="submit" class="submit-btn">承認</button>
    </form>
    @else
    <button type="submit" class="submit-btn">承認済</button>
    @endif

</div>
@endsection