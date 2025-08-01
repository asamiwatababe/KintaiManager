@extends('layouts.admin_app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<h2 class="title">申請一覧(管理者)</h2>

<div class="tabs">
    <a href="javascript:void(0);" class="tab active" onclick="switchTab('pending')">承認待ち</a>
    <a href="javascript:void(0);" class="tab" onclick="switchTab('approved')">承認済み</a>
</div>

<table id="pending-table" class="request-table">
    <thead>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <p>対象日: {{ $attendance?->date ?? '未登録' }}</p>
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
            <td><a href="{{ route('admin.stamp_correction_request.show', $request->id) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>

<table id="approved-table" class="request-table" style="display: none;">
    <thead>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <p>対象日: {{ $attendance?->date ?? '未登録' }}</p>
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
            <td><a href="{{ route('admin.stamp_correction_request.show', $request->id) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>

<script>
    function switchTab(status) {
        // タブ切り替え
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`.tab[onclick*="${status}"]`).classList.add('active');

        // テーブル表示切り替え
        document.querySelectorAll('.request-table').forEach(table => {
            table.style.display = 'none';
        });
        document.getElementById(`${status}-table`).style.display = 'table';
    }
</script>

@endsection