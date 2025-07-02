<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // 会員登録画面のカスタムビューを指定
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // ログイン画面のカスタムビューを指定（必要であれば）
        Fortify::loginView(function () {
            return view('auth.login');
        });
    }
}
