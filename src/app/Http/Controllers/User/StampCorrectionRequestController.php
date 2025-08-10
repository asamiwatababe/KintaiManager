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

    // 一般ユーザーの申請登録のみ使用
    public function store(Request $request)
    {
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $request->date)
            ->first();

        StampCorrectionRequest::create([
            'user_id'       => Auth::id(),
            'attendance_id' => $attendance ? $attendance->id : null,
            'date'          => $request->date,
            'clock_in'      => $request->clock_in,
            'clock_out'     => $request->clock_out,
            'break_in'      => $request->break_in,
            'break_out'     => $request->break_out,
            'note'          => $request->note,
            'status'        => 'pending',
        ]);

        return redirect()->route('stamp_correction_request.list')->with('success', '申請が完了しました');
    }
}
