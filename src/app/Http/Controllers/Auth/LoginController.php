<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/'); // ログイン後の遷移先
        }

        // 入力値のバリデーションではなく認証処理の結果エラー
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput();
    }
}
