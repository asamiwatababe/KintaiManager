<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function login(Request $request)
    {
        // 入力チェック
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ]);

        // 認証試行
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // 管理者でなければ失敗扱いにして戻す
            $user = Auth::user();
            if (!$user || !$user->is_admin) {
                Auth::logout();
                return back()
                    ->withErrors(['email' => trans('auth.failed')]) // 「ログイン情報が登録されていません」
                    ->onlyInput('email');
            }

            // 成功：管理者の一覧へ
            return redirect()->route('admin.attendance.list');
        }

        // 認証失敗：Fortify と同様に email キーで返す
        return back()
            ->withErrors(['email' => trans('auth.failed')])
            ->onlyInput('email');
    }
}
