<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;

//会員登録画面
// Route::get('/register', [RegisterController::class, 'show'])->name('register');
// Route::post('/register', [RegisterController::class, 'register']);

//ログイン画面
// Route::post('/login', [LoginController::class, 'login'])->name('login');

// ログアウト遷移先
Route::get('/', function () {
    return redirect()->route('login');
});


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
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.show');
});

// 退勤の処理
Route::post('/attendance/clockout', [AttendanceController::class, 'clockOut'])->name('attendance.clockout');

// 承認待ち画面
Route::get('/attendance/{id}/pending', [AttendanceController::class, 'showPending'])->name('attendance.pending');

// 勤怠修正の更新
Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.show');

// 申請一覧画面
Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])
    ->name('stamp_correction_request.list');

Route::get('/stamp_correction_request/detail/{id}', [AttendanceController::class, 'showPending'])
    ->name('stamp_correction_request.detail');

// 一般ユーザー：修正申請
Route::put('/attendance/{id}/request-update', [AttendanceController::class, 'requestUpdate'])
    ->middleware(['auth'])
    ->name('attendance.requestUpdate');




// 管理者ログイン画面
Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

// 管理者ログイン処理
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');

// 管理者のログアウト
Route::post('/admin/logout', function () {
    Auth::logout();
    return redirect('/admin/login');
})->name('admin.logout');


// 管理者の勤怠一覧
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('admin.attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show'); // ★追加
    Route::put('/attendance/{id}/update', [AdminAttendanceController::class, 'adminUpdate'])->name('admin.attendance.update');
    // スタッフ一覧
    Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])->name('admin.staff.list');
    // スタッフ別勤怠一覧
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showStaffAttendance'])->name('admin.attendance.staff');

    // CSV
    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffAttendanceCsv'])->name('admin.attendance.staff.csv');
});

// 管理者：直接修正
Route::put('/admin/attendance/{id}/update', [AdminAttendanceController::class, 'adminUpdate'])
    ->middleware(['auth', 'admin'])
    ->name('admin.attendance.update');
