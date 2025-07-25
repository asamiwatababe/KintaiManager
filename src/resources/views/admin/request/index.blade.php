@extends('layouts.admin_app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<h2 class="title">申請一覧(管理者)</h2>

<div class="tabs">
    <a class="tab active">承認待ち</a>
    <a class="tab">承認済み</a>
</div>

<table class="request-table">
    <thead>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($pending as $request)
        <tr>
            <td>承認待ち</td>
            <td>{{ $request->user->name }}</td>
            <td>{{ $request->target_date }}</td>
            <td>{{ $request->reason }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
            <td><a href="{{ route('stamp_correction_request.show', $request->id) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="request-table" style="display: none;">
    <thead>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($approved as $request)
        <tr>
            <td>承認済み</td>
            <td>{{ $request->user->name }}</td>
            <td>{{ $request->target_date }}</td>
            <td>{{ $request->reason }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
            <td><a href="{{ route('stamp_correction_request.show', $request->id) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection