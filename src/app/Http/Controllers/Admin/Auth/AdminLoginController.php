<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 認証処理（is_admin チェックは削除）
        if (Auth::attempt($credentials)) {
            return redirect()->intended('/admin/attendance/list');
        }

        // 認証失敗時のメッセージ（常にこれだけ）
        return back()->withErrors([
            'auth' => 'ログイン情報が登録されていません',
        ])->withInput();
    }
}
