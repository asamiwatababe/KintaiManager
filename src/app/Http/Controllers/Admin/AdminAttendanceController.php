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
    public function list(Request $request)
    {
        $date = $request->input('date') ?? now()->toDateString();

        $attendances = Attendance::with(['user', 'breaks'])
            ->where('date', $date)
            ->get();

        return view('admin.attendance.list', [
            'attendances'  => $attendances,
            'currentDate'  => $date,
            'previousDate' => Carbon::parse($date)->subDay()->toDateString(),
            'nextDate'     => Carbon::parse($date)->addDay()->toDateString(),
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
        $month = $request->input('month') ?? now()->format('Y-m');

        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            ->orderBy('date')
            ->get();

        return view('admin.attendance.staff_detail', [
            'user'         => $user,
            'attendances'  => $attendances,
            'currentMonth' => $month,
        ]);
    }

    // CSV（ルート名に合わせて exportStaffAttendanceCsv に統一）
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
     * - 休憩は break_times（relations: breaks）へ
     *   既存の休憩は一旦削除して、フォームの1本分を入れ直す
     * - バリデーションは AdminAttendanceUpdateRequest で実施
     */
    public function adminUpdate(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $data = $request->validated(); // clock_in, clock_out, break_start, break_end, memo

        // attendances を更新（備考は note カラム）
        $attendance->update([
            'clock_in'  => $data['clock_in'],
            'clock_out' => $data['clock_out'],
            'note'      => $data['memo'],
            'status'    => 'approved', // 直接修正は承認済み扱いに寄せる場合
        ]);

        // 休憩を break_times で更新
        $attendance->breaks()->delete();

        $breakStart = $data['break_start'] ?? null; // H:i or null
        $breakEnd   = $data['break_end'] ?? null;   // H:i or null

        if ($breakStart || $breakEnd) {
            $attendance->breaks()->create([
                'break_in'  => $breakStart ? Carbon::parse($attendance->date . ' ' . $breakStart) : null,
                'break_out' => $breakEnd   ? Carbon::parse($attendance->date . ' ' . $breakEnd)   : null,
            ]);
        }

        return redirect()->route('attendance.show', $attendance->id)
            ->with('success', '勤怠情報を修正しました。');
    }
}
