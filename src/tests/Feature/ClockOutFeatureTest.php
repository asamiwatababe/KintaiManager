<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockOutFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤ボタンが機能し_処理後にステータスが退勤済になる()
    {
        // today: 2025-01-02
        Carbon::setTestNow('2025-01-02 09:00');

        $user = User::factory()->create();
        // 出勤済（勤務中）にしておく：当日レコードあり、clock_out なし
        Attendance::factory()->create([
            'user_id'  => $user->id,
            'date'     => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        // 勤怠画面に退勤ボタンが見える
        /** @var User $user */
        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertOk()
            ->assertSee('退勤');

        // 18:00 に退勤
        Carbon::setTestNow('2025-01-02 18:00');
        $this->post(route('attendance.clockout'))
            ->assertRedirect(route('attendance'));

        // 画面のステータスが「退勤済」になっている
        $this->get(route('attendance'))
            ->assertOk()
            ->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        // today: 2025-01-03
        Carbon::setTestNow('2025-01-03 09:00');

        $user = User::factory()->create();

        // まず出勤（当日レコードを作る）
        Attendance::factory()->create([
            'user_id'  => $user->id,
            'date'     => Carbon::today()->toDateString(),
            'clock_in' => '09:00',
            'clock_out' => null,
        ]);

        // 18:00 に退勤
        Carbon::setTestNow('2025-01-03 18:00');
        /** @var User $user */
        $this->actingAs($user)
            ->post(route('attendance.clockout'))
            ->assertRedirect(route('attendance'));

        // 月次一覧で退勤時刻が表示される（ビューは H:i:s をそのまま出力）
        Carbon::setTestNow('2025-01-03 18:05');
        $month = Carbon::now()->format('Y-m');

        $this->get(route('attendance.list', ['month' => $month]))
            ->assertOk()
            ->assertSee('18:00');
    }
}
