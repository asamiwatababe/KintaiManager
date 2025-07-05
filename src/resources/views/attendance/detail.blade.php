<!-- 勤怠一覧画面 -->
<h2>休憩履歴</h2>
<table>
    <thead>
        <tr>
            <th>開始時刻</th>
            <th>終了時刻</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($breaks as $break)
        <tr>
            <td>{{ $break->break_in }}</td>
            <td>{{ $break->break_out ?? '---' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>