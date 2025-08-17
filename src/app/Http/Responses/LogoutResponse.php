<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        $isAdmin = session('is_admin', false); // セッションから取得
        session()->forget('is_admin');         // ログアウト後はセッションを破棄

        return redirect($isAdmin ? '/admin/login' : '/login');
    }
}
