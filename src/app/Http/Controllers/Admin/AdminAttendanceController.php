<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
// use App\Http\Controllers\Admin\AdminAttendanceController;

class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('date') ?? now()->toDateString();

        // 対象日の勤怠を全ユーザー分取得（user・breaksも一緒に）
        $attendances = Attendance::with(['user', 'breaks'])
            ->where('date', $date)
            ->get();

        foreach ($attendances as $attendance) {
            if ($attendance->clock_in && $attendance->clock_out) {
                $clockIn = Carbon::parse($attendance->clock_in);
                $clockOut = Carbon::parse($attendance->clock_out);
                $workMinutes = $clockOut->diffInMinutes($clockIn);

                $breakMinutes = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->break_in && $break->break_out) {
                        $in = Carbon::parse($break->break_in);
                        $out = Carbon::parse($break->break_out);
                        $breakMinutes += $out->diffInMinutes($in);
                    }
                }

                $attendance->break_duration = sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
                $attendance->work_duration = sprintf('%d:%02d', floor(($workMinutes - $breakMinutes) / 60), ($workMinutes - $breakMinutes) % 60);
            } else {
                $attendance->break_duration = '';
                $attendance->work_duration = '';
            }
        }

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'currentDate' => $date,
            'previousDate' => Carbon::parse($date)->subDay()->toDateString(),
            'nextDate' => Carbon::parse($date)->addDay()->toDateString(),
        ]);
    }
}
