<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    /**
     * 管理者：月次勤怠一覧
     * ?month=YYYY-MM（省略時は当月）
     */
    public function index(Request $request)
    {
        $month = $request->input('month') ?? now()->format('Y-m');

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        return view('admin.attendance.list', [
            'attendances'   => $attendances,
            'currentMonth'  => $month,
            'previousMonth' => $start->copy()->subMonth()->format('Y-m'),
            'nextMonth'     => $start->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['breaks', 'user'])->findOrFail($id);
        return view('admin.attendance.admindetail', compact('attendance'));
    }

    public function staffList()
    {
        $users = User::all();
        return view('admin.staff.list', compact('users'));
    }

    public function showStaffAttendance($id, Request $request)
    {
        $user  = User::findOrFail($id);

        // 表示対象の月（YYYY-MM）
        $month = $request->input('month') ?? now()->format('Y-m');

        // 当月の1日と前後月を計算
        $base      = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $prevMonth = $base->copy()->subMonth()->format('Y-m');
        $nextMonth = $base->copy()->addMonth()->format('Y-m');

        // 指定月だけを取得（YYYY-MM% で絞り込み）
        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date')
            ->get();

        return view('admin.attendance.staff_detail', [
            'user'         => $user,
            'attendances'  => $attendances,
            'currentMonth' => $month,
            'previousMonth' => $prevMonth,
            'nextMonth'    => $nextMonth,
        ]);
    }


    // CSV
    public function exportStaffAttendanceCsv($id)
    {
        $user = User::findOrFail($id);
        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->orderBy('date')
            ->get();

        $response = new StreamedResponse(function () use ($attendances, $user) {
            $handle = fopen('php://output', 'w');

            // CSVヘッダー
            fputcsv($handle, ['氏名', '日付', '出勤', '退勤', '休憩合計', '勤務時間']);

            foreach ($attendances as $attendance) {
                $clockIn  = $attendance->clock_in  ? Carbon::parse($attendance->clock_in)->format('H:i')  : '';
                $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';

                $totalBreak = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->break_in && $break->break_out) {
                        $in  = Carbon::parse($break->break_in);
                        $out = Carbon::parse($break->break_out);
                        $totalBreak += $out->diffInMinutes($in);
                    }
                }

                $totalBreakFormatted = floor($totalBreak / 60) . ':' . sprintf('%02d', $totalBreak % 60);

                $workTime = '-';
                if ($attendance->clock_in && $attendance->clock_out) {
                    $start   = Carbon::parse($attendance->clock_in);
                    $end     = Carbon::parse($attendance->clock_out);
                    $minutes = $end->diffInMinutes($start) - $totalBreak;
                    $workTime = floor($minutes / 60) . ':' . sprintf('%02d', $minutes % 60);
                }

                fputcsv($handle, [
                    $user->name,
                    $attendance->date,
                    $clockIn,
                    $clockOut,
                    $totalBreakFormatted,
                    $workTime,
                ]);
            }

            fclose($handle);
        });

        $filename = $user->name . '_勤怠一覧_' . now()->format('Ymd_His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }

    /**
     * 管理者：勤怠詳細の修正保存
     * - 出勤/退勤/備考は attendances へ
     * - 休憩は break_times へ（既存削除→2本入れ直し）
     * - バリデーションは AdminAttendanceUpdateRequest
     */
    public function adminUpdate(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $data = $request->validated();

        // 勤怠本体（備考は note に保存）
        $attendance->update([
            'clock_in'  => $data['clock_in'],
            'clock_out' => $data['clock_out'],
            'note'      => $data['memo'] ?? null,
            'status'    => 'approved',
        ]);

        // 休憩を全削除 → 2本を入れ直し
        $attendance->breaks()->delete();

        $pairs = [
            ['start' => $data['break_1_start'] ?? null, 'end' => $data['break_1_end'] ?? null],
            ['start' => $data['break_2_start'] ?? null, 'end' => $data['break_2_end'] ?? null],
        ];

        foreach ($pairs as $p) {
            if ($p['start'] || $p['end']) {
                $attendance->breaks()->create([
                    'break_in'  => $p['start'] ? Carbon::parse($attendance->date . ' ' . $p['start']) : null,
                    'break_out' => $p['end']   ? Carbon::parse($attendance->date . ' ' . $p['end'])   : null,
                ]);
            }
        }

        return redirect()->route('attendance.show', $attendance->id)
            ->with('success', '勤怠情報を修正しました。');
    }
}
