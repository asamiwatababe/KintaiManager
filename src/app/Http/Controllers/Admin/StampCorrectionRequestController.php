<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;

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
        // リレーションを明示的にロード
        $attendance_correct_request->load(['user', 'attendance.breaks']);

        return view('admin.request.show', [
            'request' => $attendance_correct_request,
            'user' => $attendance_correct_request->user,
            'attendance' => $attendance_correct_request->attendance,
        ]);
    }

    // 申請の承認処理
    public function approve(StampCorrectionRequest $attendance_correct_request)
    {
        // リレーションをロード
        $attendance_correct_request->load('attendance');

        $attendance = $attendance_correct_request->attendance;

        // 勤怠情報が存在する場合に更新
        if ($attendance) {
            $attendance->update([
                'clock_in' => $attendance_correct_request->clock_in,
                'clock_out' => $attendance_correct_request->clock_out,
                'note' => $attendance_correct_request->reason,
            ]);
        }

        // 修正申請ステータスを更新
        $attendance_correct_request->update([
            'status' => 'approved',
        ]);

        return redirect()
            ->route('admin.stamp_correction_request.list');
    }
}
