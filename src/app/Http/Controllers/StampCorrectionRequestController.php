<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class StampCorrectionRequestController extends Controller
{
    /**
     * 申請一覧を表示する（承認待ち・承認済み）
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */

    public function list(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = Attendance::with('user')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.list', compact('requests'));
    }
}
