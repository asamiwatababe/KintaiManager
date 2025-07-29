<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit; //レート制限を無効化
use Illuminate\Support\Facades\RateLimiter;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Http\Responses\LoginResponse as CustomLoginResponse;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use App\Actions\Fortify\CreateNewUser;


class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Fortifyのレスポンスをカスタムクラスで置き換える
        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
        $this->app->singleton(LogoutResponse::class, CustomLogoutResponse::class);
        // ユーザー登録時の処理をFortifyに認識させる
        $this->app->singleton(CreatesNewUsers::class, CreateNewUser::class);
    }

    public function boot()
    {
        // 会員登録画面
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // ログイン画面（管理者か一般かで分岐）
        Fortify::loginView(function () {
            return request()->is('admin/login') ? view('admin.auth.login') : view('auth.login');
        });

        // 認証ロジック（管理者ログインURLでは is_admin = true のみ通す）
        // Fortify::authenticateUsing(function (Request $request) {
        //     $user = User::where('email', $request->email)->first();

        //     if ($user && Hash::check($request->password, $user->password)) {
        //         if ($request->is('admin/login') && !$user->is_admin) {
        //             return null;
        //         }

        //         // ここでセッションに管理者フラグを保存
        //         session(['is_admin' => $user->is_admin]);

        //         return $user;
        //     }

        //     return null;
        // });

        // Fortify::authenticateUsing(function (Request $request) {
        //     $user = User::where('email', $request->email)->first();

        //     if (
        //         $user &&
        //         Hash::check($request->password, $user->password) &&
        //         $user->is_admin === 0 // 管理者はログイン不可にする
        //     ) {
        //         return $user;
        //     }

        //     // ログイン拒否
        //     return null;
        // });
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                // 管理者ログインURLで、is_adminがfalseならNG
                if ($request->is('admin/login') && !$user->is_admin) {
                    return null;
                }

                // 一般ユーザーURLで、is_adminがtrueならNG
                if (!$request->is('admin/login') && $user->is_admin) {
                    return null;
                }

                // どちらも通過
                return $user;
            }

            return null;
        });
    }
}
