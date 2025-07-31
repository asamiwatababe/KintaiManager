<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampController;
use App\Http\Controllers\User\StampCorrectionRequestController as UserStampController;

// ===== ログイン後の初期遷移先 =====
Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================
// 一般ユーザー用ルート
// ==========================
Route::middleware(['auth'])->group(function () {
    // 打刻関連
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockin');
    Route::post('/attendance/clockout', [AttendanceController::class, 'clockOut'])->name('attendance.clockout');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakin');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakout');

    // 勤怠一覧・詳細・修正申請関連
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.show');
    Route::get('/attendance/{id}/pending', [AttendanceController::class, 'showPending'])->name('attendance.pending');
    Route::put('/attendance/{id}', [AttendanceController::class, 'requestUpdate'])->name('attendance.update');

    // 修正申請：一般ユーザー用
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/stamp_correction_request/list', [UserStampController::class, 'index'])->name('stamp_correction_request.list');
        Route::get('/stamp_correction_request/{attendance_correct_request}', [UserStampController::class, 'show'])->name('stamp_correction_request.show');
        Route::post('/stamp_correction_request/{attendance}', [UserStampController::class, 'store'])->name('stamp_correction_request.store');
    });
});

// ==========================
// 管理者ログイン／ログアウト
// ==========================
Route::get('/admin/login', function () {
    return view('admin.auth.login');
})->name('admin.login');

Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');

Route::post('/admin/logout', function () {
    Auth::logout();
    return redirect('/admin/login');
})->name('admin.logout');

// ==========================
// 管理者用ルート（URLに /admin を含む）
// ==========================
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    // 修正申請（管理者）
    Route::get('/stamp_correction_request/list', [AdminStampController::class, 'index'])->name('stamp_correction_request.list');
    Route::get('/stamp_correction_request/{attendance_correct_request}', [AdminStampController::class, 'show'])->name('stamp_correction_request.show');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminStampController::class, 'approve'])->name('stamp_correction_request.approve');

    // 勤怠管理（管理者）
    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/attendance/{id}/update', [AdminAttendanceController::class, 'adminUpdate'])->name('attendance.update');

    // スタッフ管理
    Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])->name('staff.list');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showStaffAttendance'])->name('attendance.staff');
    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffAttendanceCsv'])->name('attendance.staff.csv');
});
