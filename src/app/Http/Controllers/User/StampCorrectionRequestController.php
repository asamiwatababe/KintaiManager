<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $pending = StampCorrectionRequest::where('status', 'pending')->where('user_id', $userId)->get();
        $approved = StampCorrectionRequest::where('status', 'approved')->where('user_id', $userId)->get();

        return view('user.request.index', compact('pending', 'approved'));
    }
}
