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

// ===== 申請一覧（共通パス）=====
Route::get('/stamp_correction_request/list', function () {
    $user = Auth::user();
    if (!$user) return redirect()->route('login');

    return $user->is_admin
        ? app(AdminStampController::class)->index()
        : app(UserStampController::class)->index();
})->middleware('auth')->name('stamp_correction_request.list');

// ===== 申請「詳細」（共通パス /stamp_correction_request/{id}）=====
// 管理者なら管理者の承認詳細ビューへ、一般なら勤怠詳細（pending/通常）へ振り分け
Route::get('/stamp_correction_request/{attendance_correct_request}', function (int $id) {
    $user = Auth::user();
    if (!$user) return redirect()->route('login');

    if ($user->is_admin) {
        $model = \App\Models\StampCorrectionRequest::findOrFail($id);
        return app(\App\Http\Controllers\Admin\StampCorrectionRequestController::class)->show($model);
    }

    // 一般ユーザーは勤怠詳細へ誘導（pending/approvedで切替）
    return app(\App\Http\Controllers\User\StampCorrectionRequestController::class)->redirectToAttendance($id);
})->whereNumber('attendance_correct_request')->middleware('auth')->name('stamp_correction_request.show');

// ===== 申請承認（管理者のみ／共通パス）=====
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

// ===== 管理者（/admin 配下）=====
// ※ 既存互換のために残すが、リンクは使わない（詳細リンクは上の共通パスへ）
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/stamp_correction_request/{attendance_correct_request}', [AdminStampController::class, 'show'])
        ->whereNumber('attendance_correct_request')->name('stamp_correction_request.show');

    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->whereNumber('id')->name('attendance.show');
    Route::put('/attendance/{id}/update', [AdminAttendanceController::class, 'adminUpdate'])->whereNumber('id')->name('attendance.update');

    Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])->name('staff.list');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showStaffAttendance'])->whereNumber('id')->name('attendance.staff');
    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffAttendanceCsv'])->whereNumber('id')->name('attendance.staff.csv');
});

// ===== 一般ユーザー（申請登録のみ /user）=====
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::post('/stamp_correction_request/{attendance}', [UserStampController::class, 'store'])
        ->whereNumber('attendance')->name('stamp_correction_request.store');
});
