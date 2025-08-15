<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceDetailValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin()
    {
        return User::factory()->create(['is_admin' => 1, 'email' => 'admin@example.com']);
    }

    /** @test */
    public function 管理者_勤怠詳細_出退勤の時系列エラーを表示する()
    {
        $admin = $this->makeAdmin();
        $user  = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today()->toDateString(),
        ]);
        /** @var User $admin */
        $this->actingAs($admin);

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in'       => '12:00',
                'clock_out'      => '10:00', // 逆転
                'break_1_start'  => null,
                'break_1_end'    => null,
                'memo'           => 'メモ',
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 管理者_勤怠詳細_休憩が勤務時間外ならエラーを表示する()
    {
        $admin = $this->makeAdmin();
        $user  = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today()->toDateString(),
        ]);

        /** @var User $admin */
        $this->actingAs($admin);

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_1_start' => '08:00', // 出勤前 → 範囲外
                'break_1_end'   => '08:30',
                'memo'          => 'メモ',
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'break_1_start' => '休憩時間が勤務時間外です。',
        ]);
    }

    /** @test */
    public function 管理者_備考未入力でエラーを表示する()
    {
        $admin = $this->makeAdmin();
        $user  = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today()->toDateString(),
        ]);
        /** @var User $admin */
        $this->actingAs($admin);

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_1_start' => null,
                'break_1_end'   => null,
                // memo 未入力
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'memo' => '備考を記入してください。',
        ]);
    }
}
