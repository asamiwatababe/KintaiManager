<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;

class StampCorrectionRequestController extends Controller
{
    // 管理者用の申請一覧画面
    public function index()
    {
        $pending = StampCorrectionRequest::where('status', 'pending')->get();
        $approved = StampCorrectionRequest::where('status', 'approved')->get();

        return view('admin.request.index', compact('pending', 'approved'));
    }

    // 管理者用の申請詳細画面
    public function show(StampCorrectionRequest $attendance_correct_request)
    {
        return view('admin.stamp_correction_request.show', [
            'request' => $attendance_correct_request,
            'user' => $attendance_correct_request->user,
            'attendance' => $attendance_correct_request->attendance,
        ]);
    }

    // 申請の承認処理
    public function approve(StampCorrectionRequest $attendance_correct_request)
    {
        $attendance = $attendance_correct_request->attendance;

        // 勤怠情報の更新
        $attendance->update([
            'clock_in' => $attendance_correct_request->clock_in,
            'clock_out' => $attendance_correct_request->clock_out,
            'note' => $attendance_correct_request->reason,
        ]);

        // 申請のステータス変更
        $attendance_correct_request->update([
            'status' => '承認済み',
        ]);

        return redirect()->route('admin.stamp_correction_request.list')->with('success', '修正申請を承認しました。');
    }

    // 管理者は申請を登録しないので store は不要
}
