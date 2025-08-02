@extends('layouts.admin_app')

@section('title', 'スタッフ一覧')

@section('content')
<h2 class="title">スタッフ一覧</h2>

<table>
    <thead>
        <tr>
            <th>氏名</th>
            <th>メールアドレス</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        @if (!empty($user->name))
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><a href="{{ route('admin.attendance.staff', $user->id) }}">詳細</a></td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
@endsection