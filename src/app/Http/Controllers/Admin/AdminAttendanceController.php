<?php

// namespace App\Http\Controllers\Admin;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\Models\Attendance;
// use Carbon\Carbon;
// // use App\Http\Controllers\Admin\AdminAttendanceController;

// class AdminAttendanceController extends Controller
// {
//     public function list(Request $request)
//     {
//         $date = $request->input('date') ?? now()->toDateString();

//         // 対象日の勤怠を全ユーザー分取得（user・breaksも一緒に）
//         $attendances = Attendance::with(['user', 'breaks'])
//             ->where('date', $date)
//             ->get();

//         foreach ($attendances as $attendance) {
//             if ($attendance->clock_in && $attendance->clock_out) {
//                 $clockIn = Carbon::parse($attendance->clock_in);
//                 $clockOut = Carbon::parse($attendance->clock_out);
//                 $workMinutes = $clockOut->diffInMinutes($clockIn);

//                 $breakMinutes = 0;
//                 foreach ($attendance->breaks as $break) {
//                     if ($break->break_in && $break->break_out) {
//                         $in = Carbon::parse($break->break_in);
//                         $out = Carbon::parse($break->break_out);
//                         $breakMinutes += $out->diffInMinutes($in);
//                     }
//                 }

//                 $attendance->break_duration = sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);
//                 $attendance->work_duration = sprintf('%d:%02d', floor(($workMinutes - $breakMinutes) / 60), ($workMinutes - $breakMinutes) % 60);
//             } else {
//                 $attendance->break_duration = '';
//                 $attendance->work_duration = '';
//             }
//         }

//         return view('admin.attendance.list', [
//             'attendances' => $attendances,
//             'currentDate' => $date,
//             'previousDate' => Carbon::parse($date)->subDay()->toDateString(),
//             'nextDate' => Carbon::parse($date)->addDay()->toDateString(),
//         ]);
//     }
// }

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Response;

class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('date') ?? now()->toDateString();

        $attendances = Attendance::with(['user', 'breaks'])
            ->where('date', $date)
            ->get();

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'currentDate' => $date,
            'previousDate' => Carbon::parse($date)->subDay()->toDateString(),
            'nextDate' => Carbon::parse($date)->addDay()->toDateString(),
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
        $user = User::findOrFail($id);
        $month = $request->input('month') ?? now()->format('Y-m');

        $attendances = Attendance::with('breaks')
            ->where('user_id', $id)
            // ->where('date', 'like', $month . '%')
            ->orderBy('date')
            ->get();

        return view('admin.attendance.staff_detail', [
            'user' => $user,
            'attendances' => $attendances,
            'currentMonth' => $month
        ]);
    }

    // csv 
    public function exportStaffCsv($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $attendances = \App\Models\Attendance::with('breaks')
            ->where('user_id', $id)
            ->orderBy('date')
            ->get();

        $response = new StreamedResponse(function () use ($attendances, $user) {
            $handle = fopen('php://output', 'w');

            // CSVヘッダー
            fputcsv($handle, ['氏名', '日付', '出勤', '退勤', '休憩合計', '勤務時間']);

            foreach ($attendances as $attendance) {
                $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '';
                $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '';

                $totalBreak = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->break_in && $break->break_out) {
                        $in = \Carbon\Carbon::parse($break->break_in);
                        $out = \Carbon\Carbon::parse($break->break_out);
                        $totalBreak += $out->diffInMinutes($in);
                    }
                }

                $totalBreakFormatted = floor($totalBreak / 60) . ':' . sprintf('%02d', $totalBreak % 60);

                $workTime = '-';
                if ($attendance->clock_in && $attendance->clock_out) {
                    $start = \Carbon\Carbon::parse($attendance->clock_in);
                    $end = \Carbon\Carbon::parse($attendance->clock_out);
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

    // 勤怠詳細画面の修正ボタンをクリック
    public function adminUpdate(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'break_start' => $request->input('break_start'),
            'break_end' => $request->input('break_end'),
            'memo' => $request->input('memo'),
        ]);

        return redirect()->back()->with('success', '勤怠情報を更新しました。');
    }
}
