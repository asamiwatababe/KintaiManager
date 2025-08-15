<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampController;
use App\Http\Controllers\User\StampCorrectionRequestController as UserStampController;

Route::get('/', fn() => redirect()->route('login'));

// ==========================
// 一般/管理 共通（勤怠）
// ==========================
Route::middleware(['auth'])->group(function () {
    // 打刻トップ
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');

    // 打刻操作
    Route::post('/attendance/clock-in',  [AttendanceController::class, 'clockIn'])->name('attendance.clockin');
    Route::post('/attendance/clockout',  [AttendanceController::class, 'clockOut'])->name('attendance.clockout');
    Route::post('/attendance/break-in',  [AttendanceController::class, 'breakIn'])->name('attendance.breakin');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakout');

    // 一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // ★詳細：一般/管理者で同じURLに統一（中で役割に応じて表示を出し分け）
    Route::get('/attendance/{id}', function (int $id) {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        if (!empty($user->is_admin) && $user->is_admin) {
            // 管理者ビューへ
            return app(AdminAttendanceController::class)->show($id);
        }
        // 一般ユーザーの詳細ビューへ
        return app(AttendanceController::class)->showDetail($id);
    })->whereNumber('id')->name('attendance.show');

    // 承認待ち詳細（一般ユーザーのみ利用）
    Route::get('/attendance/{id}/pending', [AttendanceController::class, 'showPending'])
        ->whereNumber('id')->name('attendance.pending');

    // 一般ユーザーの修正申請送信
    Route::put('/attendance/{id}', [AttendanceController::class, 'requestUpdate'])
        ->whereNumber('id')->name('attendance.update');
});

// ==========================
// 申請一覧・詳細・承認（共通パス）
// ==========================
Route::get('/stamp_correction_request/list', function () {
    $user = Auth::user();
    if (!$user) return redirect()->route('login');

    return $user->is_admin
        ? app(AdminStampController::class)->index()
        : app(UserStampController::class)->index();
})->middleware('auth')->name('stamp_correction_request.list');

// 詳細：/stamp_correction_request/{id}（権限で出し分け）
Route::get('/stamp_correction_request/{attendance_correct_request}', function (int $attendance_correct_request) {
    $user = Auth::user();
    if (!$user) return redirect()->route('login');

    if ($user->is_admin) {
        $model = \App\Models\StampCorrectionRequest::findOrFail($attendance_correct_request);
        return app(\App\Http\Controllers\Admin\StampCorrectionRequestController::class)->show($model);
    }
    return app(\App\Http\Controllers\User\StampCorrectionRequestController::class)
        ->redirectToAttendance($attendance_correct_request);
})->whereNumber('attendance_correct_request')->middleware('auth')->name('stamp_correction_request.show');

// 承認（管理者）
Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminStampController::class, 'approve'])
    ->whereNumber('attendance_correct_request')->middleware(['auth', 'is_admin'])
    ->name('stamp_correction_request.approve');

// ==========================
// 管理者ログイン/ログアウト
// ==========================
Route::get('/admin/login', fn() => view('admin.auth.login'))->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', function () {
    Auth::logout();
    return redirect('/admin/login');
})->name('admin.logout');

// ==========================
// 管理者（/admin 配下）
// ==========================
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {
    // 日次勤怠一覧
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendance.list');

    // ★旧URL互換：/admin/attendance/{id} → /attendance/{id} にリダイレクト
    Route::get('/attendance/{id}', function (int $id) {
        return redirect()->route('attendance.show', $id);
    })->whereNumber('id')->name('attendance.show');

    // 直接修正（PUT）は管理者のみ
    Route::put('/attendance/{id}/update', [AdminAttendanceController::class, 'adminUpdate'])
        ->whereNumber('id')->name('attendance.update');

    // スタッフ
    Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])->name('staff.list');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showStaffAttendance'])
        ->whereNumber('id')->name('attendance.staff');
    Route::get('/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffAttendanceCsv'])
        ->whereNumber('id')->name('attendance.staff.csv');

    // （任意）管理者の旧 申請詳細URLは必要なら残せますが、基本は共通パスを使用
    // Route::get('/stamp_correction_request/{attendance_correct_request}', [AdminStampController::class, 'show'])
    //     ->whereNumber('attendance_correct_request')->name('stamp_correction_request.show');
});

// ==========================
// 一般ユーザー（申請登録のみ /user）
// ==========================
Route::middleware(['auth'])->prefix('user')->name('user.')->group(function () {
    Route::post('/stamp_correction_request/{attendance}', [UserStampController::class, 'store'])
        ->whereNumber('attendance')->name('stamp_correction_request.store');
});
