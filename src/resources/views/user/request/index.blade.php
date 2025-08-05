@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
<h2 class="title">申請一覧</h2>

<div class="tabs">
    <a href="javascript:void(0);" class="tab active" onclick="switchTab('pending')">承認待ち</a>
    <a href="javascript:void(0);" class="tab" onclick="switchTab('approved')">承認済み</a>
</div>

<table class="request-table" id="pending-table">
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
            <td>{{ $request->date }}</td>
            <td>{{ $request->note }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
            <td>
                <a href="{{ route('user.stamp_correction_request.show', $request->id) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>


<table class="request-table" id="approved-table" style="display: none;">
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
        {{-- 承認済み一覧 --}}
        @foreach ($approved as $request)
        <tr>
            <td>承認済み</td>
            <td>{{ $request->user->name }}</td>
            <td>{{ $request->date }}</td>
            <td>{{ $request->note }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
        </tr>
        @endforeach

    </tbody>
</table>

<script>
    function switchTab(tab) {
        const pendingTable = document.getElementById('pending-table');
        const approvedTable = document.getElementById('approved-table');
        const tabs = document.querySelectorAll('.tab');

        if (tab === 'pending') {
            pendingTable.style.display = 'table';
            approvedTable.style.display = 'none';
        } else {
            pendingTable.style.display = 'none';
            approvedTable.style.display = 'table';
        }

        tabs.forEach(t => t.classList.remove('active'));
        document.querySelector(`.tab[onclick="switchTab('${tab}')"]`).classList.add('active');
    }
</script>
@endsection