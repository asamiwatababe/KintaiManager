<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // UI要件どおりに固定
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
    public function 勤務外の場合_ステータスが勤務外と表示される()
    {
        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        // 本日分のAttendanceを作らない → 勤務外
        $res = $this->get(route('attendance'));

        $res->assertStatus(200);
        $res->assertViewHas('status', '勤務外');  // 変数
        $res->assertSee('勤務外', false);         // 画面表示
    }

    /** @test */
    public function 出勤中の場合_ステータスが出勤中と表示される()
    {
        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        Attendance::create([
            'user_id'  => $user->id,
            'date'     => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->format('H:i:s'),
            // clock_out なし → 出勤中
        ]);

        $res = $this->get(route('attendance'));

        $res->assertStatus(200);
        $res->assertViewHas('status', '勤務中');
        $res->assertSee('勤務中', false);
    }

    /** @test */
    public function 休憩中の場合_ステータスが休憩中と表示される()
    {
        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id'  => $user->id,
            'date'     => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->subHour()->format('H:i:s'),
        ]);

        // 直近の休憩に break_out が無い → 休憩中
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in'      => Carbon::now()->subMinutes(10),
            // 'break_out'   => null
        ]);

        $res = $this->get(route('attendance'));

        $res->assertStatus(200);
        $res->assertViewHas('status', '休憩中');
        $res->assertSee('休憩中', false);
    }

    /** @test */
    public function 退勤済の場合_ステータスが退勤済と表示される()
    {
        $user = User::factory()->create();
        /** @var User $user */
        $this->actingAs($user);

        Attendance::create([
            'user_id'   => $user->id,
            'date'      => Carbon::today()->toDateString(),
            'clock_in'  => Carbon::now()->subHours(8)->format('H:i:s'),
            'clock_out' => Carbon::now()->format('H:i:s'),
        ]);

        $res = $this->get(route('attendance'));

        $res->assertStatus(200);
        $res->assertViewHas('status', '退勤済');
        $res->assertSee('退勤済', false);
    }
}
