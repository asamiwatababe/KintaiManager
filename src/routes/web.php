<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampController;
use App\Http\Controllers\User\StampCorrectionRequestController as UserStampController;

Route::get('/', fn() => redirect()->route('login'));

// ===== 一般ユーザー =====
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockin');
    Route::post('/attendance/clockout', [AttendanceController::class, 'clockOut'])->name('attendance.clockout');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakin');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakout');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->whereNumber('id')->name('attendance.show');
    Route::get('/attendance/{id}/pending', [AttendanceController::class, 'showPending'])->whereNumber('id')->name('attendance.pending');
    Route::put('/attendance/{id}', [AttendanceController::class, 'requestUpdate'])->whereNumber('id')->name('attendance.update');
});

// ===== 申請一覧（共通パスに統一）=====
Route::get('/stamp_correction_request/list', function () {
    $user = Auth::user();
    if (!$user) return redirect()->route('login');
    return $user->is_admin
        ? app(AdminStampController::class)->index()
        : app(UserStampController::class)->index();
})->middleware('auth')->name('stamp_correction_request.list');

// ===== 申請承認（管理者のみ）=====
Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminStampController::class, 'approve'])
    ->whereNumber('attendance_correct_request')
    ->middleware(['auth', 'is_admin'])
    ->name('stamp_correction_request.approve');

// ===== 管理者ログイン/ログアウト =====
Route::get('/admin/login', fn() => view('admin.auth.login'))->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', function () {
    Auth::logout();
    return redirect('/admin/login');
})->name('admin.logout');

// ===== 管理者 =====
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/stamp_correction_request/{attendance_correct_request}', [AdminStampController::class, 'show'])
        ->whereNumber('attendance_correct_request')->name('stamp_correction_request.show');

    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->whereNumber('id')->name('attendance.show');
    Route::put('/attendance/{id}/update', [AdminAttendanceController::class, 'adminUpdate'])->whereNumber('id')->name('attendance.update');

    Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])->name('staff.list');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showStaffAttendance'])->whereNumber('id')->name('attendance.staff');
    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffCsv'])->whereNumber('id')->name('attendance.staff.csv');
});

// ===== 旧URLの完全廃止（/user 側は登録だけ残す）=====
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::post('/stamp_correction_request/{attendance}', [UserStampController::class, 'store'])
        ->whereNumber('attendance')->name('stamp_correction_request.store');
    // ※ /user/stamp_correction_request/list のGETルートは作らない（廃止）
});
