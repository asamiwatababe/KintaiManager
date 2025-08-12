{{-- resources/views/admin/request/index.blade.php --}}
@extends('layouts.admin_app')

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
        @php
        // 「（休憩2: HH:MM〜HH:MM）」を表示から除去（全角/半角・〜/~ 対応）
        $reason = $request->reason ?? $request->note ?? '';
        $reason = preg_replace('/（\s*休憩2\s*[:：]?\s*\d{2}:\d{2}\s*[〜~]\s*\d{2}:\d{2}\s*）/u', '', $reason);
        @endphp
        <tr>
            <td>承認待ち</td>
            <td>{{ $request->user->name }}</td>
            <td>{{ $request->date ?? $request->target_date }}</td>
            <td>{{ trim($reason) }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
            {{-- 詳細は共通パス --}}
            <td><a href="{{ route('stamp_correction_request.show', $request->id) }}">詳細</a></td>
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
        @foreach ($approved as $request)
        @php
        $reason = $request->reason ?? $request->note ?? '';
        $reason = preg_replace('/（\s*休憩2\s*[:：]?\s*\d{2}:\d{2}\s*[〜~]\s*\d{2}:\d{2}\s*）/u', '', $reason);
        @endphp
        <tr>
            <td>承認済み</td>
            <td>{{ $request->user->name }}</td>
            <td>{{ $request->date ?? $request->target_date }}</td>
            <td>{{ trim($reason) }}</td>
            <td>{{ $request->created_at->format('Y/m/d') }}</td>
            <td>—</td>
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