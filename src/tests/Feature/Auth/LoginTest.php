<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const LOGIN_URL = '/login';

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('ja'); // 日本語メッセージで検証
    }

    /** @test */
    public function メール未入力なら_メールアドレスを入力してください_が表示される()
    {
        // 既存ユーザーがいてもいなくてもこのケースには影響なし
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $res = $this->from(self::LOGIN_URL)->post(self::LOGIN_URL, [
            'email' => '',
            'password' => 'password123',
        ]);

        $res->assertRedirect(self::LOGIN_URL);
        $res->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
        $this->assertGuest();
    }

    /** @test */
    public function パスワード未入力なら_パスワードを入力してください_が表示される()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $res = $this->from(self::LOGIN_URL)->post(self::LOGIN_URL, [
            'email' => 'user@example.com',
            'password' => '',
        ]);

        $res->assertRedirect(self::LOGIN_URL);
        $res->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
        $this->assertGuest();
    }

    /** @test */
    public function 登録内容と一致しない場合_ログイン情報が登録されていません_が表示される()
    {
        // 正しいユーザーを作成しておく
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 誤ったメールアドレス（または誤ったパスワード）で試行
        $res = $this->from(self::LOGIN_URL)->post(self::LOGIN_URL, [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $res->assertRedirect(self::LOGIN_URL);
        // Fortify 標準では認証失敗メッセージは 'email' キーに乗ります
        $res->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
        $this->assertGuest();
    }
}
