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
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $request->date)
            ->first();

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

        return redirect()->route('stamp_correction_request.list')->with('success', '申請が完了しました');
    }

    /**
     * 共通パス /stamp_correction_request/{id} から
     * 一般ユーザーを勤怠詳細（pending/通常）へリダイレクトする。
     */
    public function redirectToAttendance(int $id)
    {
        $req = StampCorrectionRequest::with('attendance')
            ->where('user_id', Auth::id()) // 自分の申請のみ
            ->findOrFail($id);

        $attendance = $req->attendance;
        if (!$attendance) {
            abort(404);
        }

        // 承認待ち→承認待ちの詳細へ / 承認済→通常の詳細へ
        if ($req->status === 'pending') {
            return redirect()->route('attendance.pending', $attendance->id);
        }
        return redirect()->route('attendance.show', $attendance->id);
    }
}
