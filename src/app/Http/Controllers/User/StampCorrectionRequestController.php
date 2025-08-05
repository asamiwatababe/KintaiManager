<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;


class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $pending = StampCorrectionRequest::where('status', 'pending')->where('user_id', $userId)->get();
        $approved = StampCorrectionRequest::where('status', 'approved')->where('user_id', $userId)->get();

        return view('user.request.index', compact('pending', 'approved'));
    }

    public function show($id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance.breaks'])->findOrFail($id);
        $user = $request->user;

        return view('attendance.pending_detail', [
            'request' => $request,
            'user' => $user,
            'attendance' => $request->attendance,
        ]);
    }

    public function store(Request $request)
    {
        // 申請対象の日付の Attendance を取得
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $request->date)
            ->first();

            // 修正した申請を登録
        StampCorrectionRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance ? $attendance->id : null, 
            'date' => $request->date,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'break_in' => $request->break_in,
            'break_out' => $request->break_out,
            'note' => $request->note,
            'status' => 'pending',
        ]);

        return redirect()->route('user.stamp_correction_request.index')->with('success', '申請が完了しました');
    }
}
