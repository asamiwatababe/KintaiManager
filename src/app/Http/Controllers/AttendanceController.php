<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $date = Carbon::now()->format('Y年n月j日 (D)');
        $time = Carbon::now()->format('H:i');

        return view('attendance.index', compact('user', 'date', 'time'));
    }
}
