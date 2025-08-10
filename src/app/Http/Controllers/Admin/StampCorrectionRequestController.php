<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;

class StampCorrectionRequestController extends Controller
{
    // 管理者用の申請一覧
    public function index()
    {
        $pending  = StampCorrectionRequest::where('status', 'pending')->get();
        $approved = StampCorrectionRequest::where('status', 'approved')->get();

        return view('admin.request.index', compact('pending', 'approved'));
    }

    // 管理者用の申請詳細（承認画面）
    public function show(StampCorrectionRequest $attendance_correct_request)
    {
        // 申請のユーザー・紐づく勤怠(あれば)・休憩をロード
        $attendance_correct_request->load(['user', 'attendance.breaks']);

        // 紐づく勤怠が無い場合は、ユーザーIDと日付から推測して補完
        $attendance = $attendance_correct_request->attendance;
        if (!$attendance && $attendance_correct_request->user_id && $attendance_correct_request->date) {
            $attendance = Attendance::with('breaks')
                ->where('user_id', $attendance_correct_request->user_id)
                ->whereDate('date', $attendance_correct_request->date)
                ->first();
        }

        return view('admin.request.show', [
            'request'    => $attendance_correct_request, // Blade では $request という変数名で受けます
            'user'       => $attendance_correct_request->user,
            'attendance' => $attendance,  // 無ければ null のまま渡す（Blade 側で安全に表示）
        ]);
    }

    // 承認
    public function approve(StampCorrectionRequest $attendance_correct_request)
    {
        // すでに承認済みなら何もしない
        if ($attendance_correct_request->status === 'approved') {
            return redirect()
                ->route('stamp_correction_request.list')
                ->with('info', 'この申請は既に承認済みです。');
        }

        $attendance_correct_request->load('attendance');
        $attendance = $attendance_correct_request->attendance;

        if ($attendance) {
            $attendance->update([
                'clock_in'  => $attendance_correct_request->clock_in,
                'clock_out' => $attendance_correct_request->clock_out,
                'note'      => $attendance_correct_request->reason ?? $attendance_correct_request->note,
            ]);
        }

        $attendance_correct_request->update(['status' => 'approved']);

        return redirect()
            ->route('stamp_correction_request.list')
            ->with('success', '申請を承認しました。');
    }
}
