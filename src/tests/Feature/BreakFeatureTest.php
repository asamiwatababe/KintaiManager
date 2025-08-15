<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 出勤中ユーザーで「休憩入」→ステータスが「休憩中」になる
     * 期待: 「休憩入」ボタンが表示され、処理後は「休憩中」が表示される
     * @test
     */
    public function 休憩ボタンが正しく機能する()
    {
        Carbon::setTestNow('2025-01-02 09:00:00');

        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // まず出勤して「出勤中」状態にする
        $this->post(route('attendance.clockin'))->assertRedirect();

        // 画面に「休憩入」ボタンが見える（出勤中）
        $this->get(route('attendance'))
            ->assertOk()
            ->assertSee('休憩入')
            ->assertSee('勤務中');

        // 12:00 に休憩入
        Carbon::setTestNow('2025-01-02 12:00:00');
        $this->post(route('attendance.breakin'))->assertRedirect();

        // 休憩中になって「休憩戻」ボタンが見える
        $this->get(route('attendance'))
            ->assertOk()
            ->assertSee('休憩中')
            ->assertSee('休憩戻');
    }

    /**
     * 休憩は一日に何回でもできる（1回目の休憩入/戻後、また「休憩入」が表示される）
     * 期待: 1サイクル後に「休憩入」ボタンが再び表示される
     * @test
     */
    public function 休憩は一日に何回でもできる()
    {
        Carbon::setTestNow('2025-01-02 09:00:00');

        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // 出勤
        $this->post(route('attendance.clockin'))->assertRedirect();

        // 休憩入 → 休憩戻
        Carbon::setTestNow('2025-01-02 12:00:00');
        $this->post(route('attendance.breakin'))->assertRedirect();

        Carbon::setTestNow('2025-01-02 12:30:00');
        $this->post(route('attendance.breakout'))->assertRedirect();

        // 出勤中に戻り、再度「休憩入」が出る
        $this->get(route('attendance'))
            ->assertOk()
            ->assertSee('勤務中')
            ->assertSee('休憩入');
    }

    /**
     * 休憩戻ボタンが正しく機能する（休憩入後に休憩戻→出勤中へ）
     * 期待: 休憩中では「休憩戻」ボタンが表示され、処理後は「出勤中」に変わる
     * @test
     */
    public function 休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow('2025-01-02 09:00:00');

        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // 出勤
        $this->post(route('attendance.clockin'))->assertRedirect();

        // 休憩入
        Carbon::setTestNow('2025-01-02 12:00:00');
        $this->post(route('attendance.breakin'))->assertRedirect();

        // 休憩中で「休憩戻」が見える
        $this->get(route('attendance'))
            ->assertOk()
            ->assertSee('休憩中')
            ->assertSee('休憩戻');

        // 休憩戻
        Carbon::setTestNow('2025-01-02 12:30:00');
        $this->post(route('attendance.breakout'))->assertRedirect();

        // 出勤中に戻る
        $this->get(route('attendance'))
            ->assertOk()
            ->assertSee('勤務中')
            ->assertSee('休憩入');
    }

    /**
     * 休憩戻は一日に何回でもできる（2本目でも「休憩戻」ボタンが出る）
     * 期待: 1本目の入/戻後、再度 2本目の休憩入で「休憩戻」が表示される
     * @test
     */
    public function 休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow('2025-01-02 09:00:00');

        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // 出勤
        $this->post(route('attendance.clockin'))->assertRedirect();

        // 1本目: 12:00→12:30
        Carbon::setTestNow('2025-01-02 12:00:00');
        $this->post(route('attendance.breakin'))->assertRedirect();

        Carbon::setTestNow('2025-01-02 12:30:00');
        $this->post(route('attendance.breakout'))->assertRedirect();

        // 2本目: 15:00 に休憩入 → この時点で「休憩戻」が見える（休憩中）
        Carbon::setTestNow('2025-01-02 15:00:00');
        $this->post(route('attendance.breakin'))->assertRedirect();

        $this->get(route('attendance'))
            ->assertOk()
            ->assertSee('休憩中')
            ->assertSee('休憩戻');
    }

    /**
     * 休憩時刻が勤怠一覧で確認できる
     * 期待: 2本(12:00-12:30, 15:00-15:15)の合計 45分が「0h 45m」として表示
     * @test
     */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow('2025-01-02 09:00:00');

        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // 出勤
        $this->post(route('attendance.clockin'))->assertRedirect();

        // 1本目: 12:00 → 12:30
        Carbon::setTestNow('2025-01-02 12:00:00');
        $this->post(route('attendance.breakin'))->assertRedirect();

        Carbon::setTestNow('2025-01-02 12:30:00');
        $this->post(route('attendance.breakout'))->assertRedirect();

        // 2本目: 15:00 → 15:15
        Carbon::setTestNow('2025-01-02 15:00:00');
        $this->post(route('attendance.breakin'))->assertRedirect();

        Carbon::setTestNow('2025-01-02 15:15:00');
        $this->post(route('attendance.breakout'))->assertRedirect();

        // 退勤
        Carbon::setTestNow('2025-01-02 18:00:00');
        $this->post(route('attendance.clockout'))->assertRedirect();

        // 月次一覧で休憩合計が 0h 45m と表示される（AttendanceController@list の仕様に合わせる）
        $month = Carbon::now()->format('Y-m');
        $this->get(route('attendance.list', ['month' => $month]))
            ->assertOk()
            ->assertSee('0h 45m'); // break_duration 表示
    }
}
