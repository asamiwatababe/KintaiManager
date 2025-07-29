<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        if (Auth::user()->is_admin) {
            // 管理者：全件取得
            $pending = StampCorrectionRequest::where('status', 'pending')->get();
            $approved = StampCorrectionRequest::where('status', 'approved')->get();
            return view('admin.request.index', compact('pending', 'approved'));
        } else {
            // 一般ユーザー：自分の分だけ取得
            $userId = Auth::id();
            $pending = StampCorrectionRequest::where('status', 'pending')->where('user_id', $userId)->get();
            $approved = StampCorrectionRequest::where('status', 'approved')->where('user_id', $userId)->get();
            return view('user.request.index', compact('pending', 'approved'));
        }
    }

    // 申請一覧詳細画面
    public function show(StampCorrectionRequest $attendance_correct_request)
    {
        // 管理者かどうかを判定
        if (auth()->user()->is_admin) {
            // 管理者ならこれを表示
            return view('admin.stamp_correction_request.show', [
                'request' => $attendance_correct_request,
                'user' => $attendance_correct_request->user,
                'attendance' => $attendance_correct_request->attendance,
            ]);
        } else {
            // 一般ユーザーなら自分の申請かをuser_idで確認
            if ($attendance_correct_request->user_id !== auth()->id()) {
                abort(403, '許可されていないアクセスです。');
            }

            return view('user.stamp_correction_request.show', [
                'request' => $attendance_correct_request,
                'user' => $attendance_correct_request->user,
                'attendance' => $attendance_correct_request->attendance,
            ]);
        }
    }

    // 申請承認画面（管理者が申請を小鬼んして勤怠情報に反映）
    public function approve(StampCorrectionRequest $attendance_correct_request)
    {
        // 対象の勤怠データを取得
        $attendance = $attendance_correct_request->attendance;

        // 勤怠情報を修正申請内容で更新
        $attendance->update([
            'clock_in' => $attendance_correct_request->clock_in,
            'clock_out' => $attendance_correct_request->clock_out,
            'note' => $attendance_correct_request->reason,
        ]);

        // 申請のステータスを「承認済み」に更新
        $attendance_correct_request->update([
            'status' => '承認済み',
        ]);

        return redirect()->route('stamp_correction_request.list')->with('success', '修正申請を承認しました。');
    }
}
