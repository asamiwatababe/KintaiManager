<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Carbon\Carbon;

class AttendanceNowFormatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // UI 要件に合わせてタイムゾーン/ロケールを固定
        config(['app.timezone' => 'Asia/Tokyo']);
        app()->setLocale('ja');

        // テスト時刻を固定（レースコンディション防止）
        Carbon::setTestNow(Carbon::create(2025, 1, 2, 9, 8, 0, 'Asia/Tokyo'));
    }

    protected function tearDown(): void
    {
        // テスト時刻を元に戻す
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function 勤怠打刻画面の日時が現在時刻と同じ形式で表示される()
    {
        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // Controller 実装（AttendanceController@index）に合わせたフォーマット
        $expectedDate = Carbon::now()->format('Y年n月j日 (D)');
        $expectedTime = Carbon::now()->format('H:i');

        $res = $this->get(route('attendance'));

        $res->assertStatus(200);

        // 画面表示テキストに含まれていること
        $res->assertSee($expectedDate, false);
        $res->assertSee($expectedTime, false);

        // ビュー変数に正しい値が渡っていること（より厳密）
        $res->assertViewHas('date', $expectedDate);
        $res->assertViewHas('time', $expectedTime);
    }
}
