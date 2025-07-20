@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stamp_list.css') }}">

<div class="container">
    <h2 class="title">申請一覧</h2>

    <div class="tabs">
        <a href="?status=pending" class="tab {{ request('status', 'pending') === 'pending' ? 'active' : '' }}">承認待ち</a>
        <a href="?status=approved" class="tab {{ request('status') === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>

    <table class="request-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>備考</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
            <tr>
                <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                <td>{{ $request->user->name }}</td>
                <td>{{ $request->date }}</td>
                <td>{{ $request->note ?? '―' }}</td>
                <td>{{ $request->created_at->format('Y/m/d') }}</td>
                <td>
                    <a href="{{ route('stamp_correction_request.detail', $request->id) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection