<?php

// fortifyログイン後のリダイレクト先を管理者かどうか分岐させる
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\Request;

class LoginResponseController extends Controller
{
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user->is_admin) {
            return redirect()->intended('/admin/dashboard');
        }

        return redirect()->intended('/dashboard');
    }
}
