<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::now()->format('Y年n月j日 (D)');
        $time = Carbon::now()->format('H:i');

        // 本日の勤怠を取得してステータスを決定
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->first();

        $status = '未出勤';

        if ($attendance) {
            $status = '出勤中';

            $latestBreak = $attendance->breaks()->latest()->first();
            if ($latestBreak && $latestBreak->break_in && !$latestBreak->break_out) {
                $status = '休憩中';
            }

            if ($attendance->clock_out) {
                $status = '退勤済み';
            }
        }

        return view('attendance.index', compact('user', 'date', 'time', 'status'));
    }


    public function clockIn(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $existing = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($existing) {
            return redirect()->route('attendance')->with('error', '本日は既に出勤しています');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => Carbon::now()->format('H:i:s'),
        ]);

        return redirect()->route('attendance')->with('success', '出勤打刻しました');
    }

    public function breakIn(Request $request)
    {
        $user = $request->user();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', now()->toDateString())->first();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in' => now(),
        ]);

        return redirect()->back();
    }

    public function breakOut(Request $request)
    {
        $user = $request->user();
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', now()->toDateString())->first();

        $lastBreak = $attendance->breaks()->whereNull('break_out')->latest()->first();

        if ($lastBreak) {
            $lastBreak->update([
                'break_out' => now(),
            ]);
        }

        return redirect()->back();
    }

    public function showDetail(Request $request)
    {
        $user = $request->user();

        $breaks = BreakTime::whereHas('attendance', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->orderBy('break_in', 'asc')->get();

        return view('attendance.detail', compact('breaks'));
    }

    public function list(Request $request)
    {
        $user = $request->user();

        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get();

        return view('attendance.list', [
            'attendances' => $attendances,
            'currentMonth' => $month,
            'previousMonth' => Carbon::parse($month)->subMonth()->format('Y-m'),
            'nextMonth' => Carbon::parse($month)->addMonth()->format('Y-m'),
        ]);
    }

    // 退勤処理
    public function clockOut(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance')->with('error', '出勤記録が見つかりません');
        }

        if ($attendance->clock_out) {
            return redirect()->route('attendance')->with('error', '本日は既に退勤しています');
        }

        $attendance->update([
            'clock_out' => Carbon::now()->format('H:i:s'),
        ]);

        return redirect()->route('attendance')->with('success', 'お疲れ様でした。');
    }
}
