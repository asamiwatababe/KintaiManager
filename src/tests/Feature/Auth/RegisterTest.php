<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    private const REGISTER_URL = '/register';

    protected function setUp(): void
    {
        parent::setUp();
        // 日本語メッセージで検証
        app()->setLocale('ja');
    }

    /** @test */
    public function 名前未入力なら_お名前を入力してください_が表示される()
    {
        $res = $this->from(self::REGISTER_URL)->post(self::REGISTER_URL, [
            'name' => '',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $res->assertRedirect(self::REGISTER_URL);
        // 厳密に文言を検証
        $res->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
        $this->assertGuest();
    }

    /** @test */
    public function メール未入力なら_メールアドレスを入力してください_が表示される()
    {
        $res = $this->from(self::REGISTER_URL)->post(self::REGISTER_URL, [
            'name' => '山田太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $res->assertRedirect(self::REGISTER_URL);
        $res->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
        $this->assertGuest();
    }

    /** @test */
    public function パスワード未入力なら_パスワードを入力してください_が表示される()
    {
        $res = $this->from(self::REGISTER_URL)->post(self::REGISTER_URL, [
            'name' => '山田太郎',
            'email' => 'user@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $res->assertRedirect(self::REGISTER_URL);
        $res->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
        $this->assertGuest();
    }

    /** @test */
    public function パスワードが8文字未満なら_パスワードは8文字以上で入力してください_が表示される()
    {
        $res = $this->from(self::REGISTER_URL)->post(self::REGISTER_URL, [
            'name' => '山田太郎',
            'email' => 'user@example.com',
            'password' => 'short', // 5文字
            'password_confirmation' => 'short',
        ]);

        $res->assertRedirect(self::REGISTER_URL);
        $res->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
        $this->assertGuest();
    }

    /** @test */
    public function パスワード不一致なら_パスワードと一致しません_が表示される()
    {
        $res = $this->from(self::REGISTER_URL)->post(self::REGISTER_URL, [
            'name' => '山田太郎',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'not-match',
        ]);

        $res->assertRedirect(self::REGISTER_URL);
        $res->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
        $this->assertGuest();
    }

    /** @test */
    public function 正常登録でユーザーが保存されログイン状態になり打刻画面へリダイレクトされる()
    {
        $email = 'taro' . Str::random(5) . '@example.com';

        $res = $this->post(self::REGISTER_URL, [
            'name' => '山田太郎',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 要件：登録後、打刻画面( /attendance )へ
        $res->assertRedirect(route('attendance'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'name'  => '山田太郎',
        ]);
    }
}
