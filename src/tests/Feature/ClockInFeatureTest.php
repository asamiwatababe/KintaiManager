<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockInFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.timezone' => 'Asia/Tokyo']);
        app()->setLocale('ja');
        Carbon::setTestNow(Carbon::create(2025, 1, 2, 9, 0, 0, 'Asia/Tokyo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function 出勤ボタンが機能し_処理後にステータスが出勤中になる()
    {
        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // 出勤前（勤務外）：ボタン表示
        $res = $this->get(route('attendance'));
        $res->assertStatus(200);
        $res->assertSee('ステータス：', false);
        $res->assertSee('勤務外', false);
        $res->assertSee('出勤', false);

        // 出勤処理
        $post = $this->post(route('attendance.clockin'));
        $post->assertRedirect(route('attendance'));
        $post->assertSessionHas('success', '出勤打刻しました');

        // 画面で出勤中表示
        $res2 = $this->get(route('attendance'));
        $res2->assertStatus(200);
        $res2->assertSee('勤務中', false);

        // DBに本日の勤怠ができていること
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date'    => Carbon::today()->toDateString(),
        ]);
    }

    /** @test */
    public function 出勤は一日一回_退勤済みのユーザーには出勤ボタンが表示されない_二度目の出勤も拒否される()
    {
        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // 当日分を退勤済にしておく
        Attendance::create([
            'user_id'   => $user->id,
            'date'      => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(8)->format('H:i:s'),
            'clock_out' => Carbon::now()->format('H:i:s'),
        ]);

        // 画面で「出勤」ボタンが無い（勤務外のみ表示仕様）
        $res = $this->get(route('attendance'));
        $res->assertStatus(200);
        $res->assertSee('退勤済', false);
        $res->assertDontSee('出勤', false);

        // 二度目の出勤もエラーで拒否
        $post = $this->post(route('attendance.clockin'));
        $post->assertRedirect(route('attendance'));
        $post->assertSessionHas('error', '本日は既に出勤しています');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        /** @var User $user */

        $this->actingAs($user);

        // 出勤
        $this->post(route('attendance.clockin'))->assertRedirect(route('attendance'));

        // 勤怠一覧の当月ページ
        $res = $this->get(route('attendance.list', [
            'month' => Carbon::now()->format('Y-m'),
        ]));

        $res->assertStatus(200);

        // clock_in は H:i:s で表示される実装だが、部分一致（H:i）で検証
        $res->assertSee(Carbon::now()->format('H:i'), false);
        // その日の行が存在すること（必要なら日付も確認）
        $res->assertSee(Carbon::now()->format('m/d'), false);
    }
}
