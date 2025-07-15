<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Controllers\Auth\LoginResponseController;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(LoginResponse::class, LoginResponseController::class);
    }

    public function boot()
    {
        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 管理者のログイン
        Fortify::loginView(function () {
            return request()->is('admin/*') ? view('admin.auth.login') : view('auth.login');
        });

        // is_admin trueのユーザーだけログイン可能
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (
                $user &&
                Hash::check($request->password, $user->password)
            ) {
                // 管理者画面からのログインなら is_admin チェック
                if (request()->is('admin/*')) {
                    return $user->is_admin ? $user : null;
                }

                // 一般ユーザーのログインはそのままOK
                return $user;
            }

            return null;
        });
    }
}
