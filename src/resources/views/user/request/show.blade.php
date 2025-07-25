@extends('layouts.app') {{-- 一般ユーザー用レイアウト --}}

@section('title', '修正申請 詳細')

@section('content')
<h2 class="title">▌修正申請 詳細</h2>

<table class="detail-table">
    <tr>
        <th>名前</th>
        <td>{{ $request->user->name }}</td>
    </tr>
    <tr>
        <th>対象日時</th>
        <td>{{ $request->target_date }}</td>
    </tr>
    <tr>
        <th>申請理由</th>
        <td>{{ $request->reason }}</td>
    </tr>
    <tr>
        <th>申請日時</th>
        <td>{{ $request->created_at->format('Y/m/d H:i') }}</td>
    </tr>
    <tr>
        <th>状態</th>
        <td>{{ $request->status === 'approved' ? '承認済み' : '承認待ち' }}</td>
    </tr>
</table>

<div class="button-area">
    <a href="{{ route('stamp_correction_request.list') }}" class="back-btn">一覧へ戻る</a>
</div>
@endsection