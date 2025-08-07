<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
// use App\Http\Requests\StampCorrectionRequest;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AttendanceCorrectionRequest;




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
                $status = '退勤済';
            }
        }

        return view('attendance.index', compact('user', 'date', 'time', 'status'));
    }


    public function clockIn(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // 今日すでに出勤があるかチェックしてexistingに値があれば出勤済みと判断
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

    // 承認待ち画面
    public function showPending($id)
    {
        $attendance = Attendance::with('breaks', 'user')->findOrFail($id);
        $user = $attendance->user;
        $breaks = $attendance->breaks->sortBy('break_in')->values();

        return view('attendance.pending_detail', compact('attendance', 'user', 'breaks'));
    }

    public function list(Request $request)
    {
        $user = $request->user();

        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        // 休憩データも取得する
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
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

                $attendance->break_duration = floor($breakMinutes / 60) . 'h ' . ($breakMinutes % 60) . 'm';
                $attendance->work_duration = floor(($workMinutes - $breakMinutes) / 60) . 'h ' . (($workMinutes - $breakMinutes) % 60) . 'm';
            } else {
                $attendance->break_duration = '-';
                $attendance->work_duration = '-';
            }
        }

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

    // 管理者か一般ユーザーかでビューを分ける処理
    public function showDetail($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);
        $breaks = $attendance->breaks->sortBy('break_in')->values();
        $user = $attendance->user;

        return view('attendance.detail', compact('attendance', 'breaks', 'user'));
    }


    public function requestUpdate(AttendanceCorrectionRequest $request, $id)
    {
        Log::info('requestUpdate called', ['id' => $id]);
        $attendance = Attendance::findOrFail($id);

        DB::transaction(function () use ($attendance, $request) {
            Log::info('Inside transaction start');

            $attendance->update(['status' => 'pending']);
            Log::info('Attendance status updated');

            StampCorrectionRequest::create([
                'user_id' => auth()->id(),
                'date' => $attendance->date,
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'break_in' => $request->break_start ?? null,
                'break_out' => $request->break_end ?? null,
                'note' => $request->note,
                'status' => 'pending',
            ]);
        });

        return redirect()->route('attendance.list')->with('success', '修正申請を送信しました。');
    }


    // 管理者による直接修正
    public function adminUpdate(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'breaks.*.break_in' => 'nullable|date_format:H:i',
            'breaks.*.break_out' => 'nullable|date_format:H:i|after:breaks.*.break_in',
            'note' => 'required|string|max:255',
        ], [
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'breaks.*.break_out.after' => '休憩時間が勤務時間外です。',
            'note.required' => '備考を記入してください。',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($attendance, $request) {
            $attendance->update([
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'note' => $request->note,
                'status' => 'approved', // 承認済み状態にするなど適宜変更可
            ]);

            $attendance->breaks()->delete();
            foreach ($request->input('breaks', []) as $break) {
                if (!empty($break['break_in']) || !empty($break['break_out'])) {
                    $attendance->breaks()->create([
                        'break_in' => Carbon::parse($attendance->date . ' ' . $break['break_in']),
                        'break_out' => Carbon::parse($attendance->date . ' ' . $break['break_out']),
                    ]);
                }
            }
        });

        return redirect()->route('attendance.detail', $id)->with('success', '勤怠情報を修正しました。');
    }
}
