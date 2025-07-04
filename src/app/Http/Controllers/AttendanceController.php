<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $date = Carbon::now()->format('Y年n月j日 (D)');
        $time = Carbon::now()->format('H:i');

        return view('attendance.index', compact('user', 'date', 'time'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 同じ日に既に出勤記録があるか確認
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
}
