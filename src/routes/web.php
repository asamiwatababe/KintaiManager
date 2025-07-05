<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;

//会員登録画面
Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

//ログイン画面
Route::post('/login', [LoginController::class, 'login'])->name('login');

// 打刻画面
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockin');
});

// 休憩ボタンの処理
Route::middleware(['auth'])->group(function () {
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakin');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakout');
});

// 勤怠一覧画面
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}/detail', [AttendanceController::class, 'showDetail'])->name('attendance.detail');
});

// 退勤の処理
Route::post('/attendance/clockout', [AttendanceController::class, 'clockOut'])->name('attendance.clockout');
