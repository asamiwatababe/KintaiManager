<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_LOGIN_URL = '/admin/login';

    protected function setUp(): void
    {
        parent::setUp();
        // 日本語メッセージで検証
        app()->setLocale('ja');
    }

    /** @test */
    public function メール未入力なら_メールアドレスを入力してください_が表示される()
    {
        // 管理者ユーザーを作成（存在有無はこのケースに影響しないが、手順に合わせて作成）
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 1,
        ]);

        $res = $this->from(self::ADMIN_LOGIN_URL)->post(self::ADMIN_LOGIN_URL, [
            'email' => '',
            'password' => 'password123',
        ]);

        $res->assertRedirect(self::ADMIN_LOGIN_URL);
        $res->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
        $this->assertGuest(); // 認証されていない
    }

    /** @test */
    public function パスワード未入力なら_パスワードを入力してください_が表示される()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 1,
        ]);

        $res = $this->from(self::ADMIN_LOGIN_URL)->post(self::ADMIN_LOGIN_URL, [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $res->assertRedirect(self::ADMIN_LOGIN_URL);
        $res->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
        $this->assertGuest();
    }

    /** @test */
    public function 登録内容と一致しない場合_ログイン情報が登録されていません_が表示される()
    {
        // 正しい管理者を用意
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => 1,
        ]);

        // 誤ったメール（または誤パス）でログイン試行
        $res = $this->from(self::ADMIN_LOGIN_URL)->post(self::ADMIN_LOGIN_URL, [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $res->assertRedirect(self::ADMIN_LOGIN_URL);
        // 実装で auth.failed（ja/auth.php の failed）を email キーに積む想定
        $res->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
        $this->assertGuest();
    }
}
