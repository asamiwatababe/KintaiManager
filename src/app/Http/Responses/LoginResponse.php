<?php

// fortifyログイン後のリダイレクト先を管理者かどうか分岐させる
namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->is_admin) {
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->intended('/attendance');
    }
}
