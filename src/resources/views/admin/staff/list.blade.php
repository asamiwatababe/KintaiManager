@extends('layouts.admin_app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/pending_detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<h2 class="title">スタッフ一覧</h2>

<div class="table-card">
    <table class="staff-table">
        <thead>
            <tr>
                <th style="width:38%">名前</th>
                <th style="width:47%">メールアドレス</th>
                <th style="width:15%; text-align:center;">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
            @if (!empty($user->name))
            <tr>
                <td>{{ $user->name }}</td>
                <td class="break-email">{{ $user->email }}</td>
                <td class="cell-center">
                    <a class="btn-link" href="{{ route('admin.attendance.staff', $user->id) }}">詳細</a>
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="3" class="cell-empty">スタッフが見つかりませんでした</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection