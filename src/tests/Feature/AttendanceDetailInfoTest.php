<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceDetailInfoTest extends TestCase
{
    use RefreshDatabase;

    private function seedAttendanceWithTwoBreaks(User $user): Attendance
    {
        $date = '2025-01-10';

        $attendance = Attendance::factory()->create([
            'user_id'  => $user->id,
            'date'     => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 休憩は break_times テーブル（Attendance::breaks()）に 2本登録
        $attendance->breaks()->create([
            'break_in'  => Carbon::parse("$date 12:00:00"),
            'break_out' => Carbon::parse("$date 12:30:00"),
        ]);
        $attendance->breaks()->create([
            'break_in'  => Carbon::parse("$date 15:00:00"),
            'break_out' => Carbon::parse("$date 15:15:00"),
        ]);

        return $attendance->refresh();
    }

    /** @test */
    public function 勤怠詳細_名前がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create(['name' => '山田太郎']);
        $attendance = $this->seedAttendanceWithTwoBreaks($user);
        /** @var User $user */

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertOk()
            ->assertSee('勤怠詳細')
            ->assertSee('山田太郎'); // 名前が表示されている
    }

    /** @test */
    public function 勤怠詳細_日付が選択した日付になっている()
    {
        $user = User::factory()->create();
        $attendance = $this->seedAttendanceWithTwoBreaks($user); // 2025-01-10

        // 画面側は Y年n月j日 で表示している
        $expectedDate = Carbon::parse($attendance->date)->format('Y年n月j日');
        /** @var User $user */

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertOk()
            ->assertSee($expectedDate);
    }

    /** @test */
    public function 勤怠詳細_出勤退勤が打刻と一致している()
    {
        $user = User::factory()->create();
        $attendance = $this->seedAttendanceWithTwoBreaks($user);
        /** @var User $user */

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertOk()
            // 入力欄の value="HH:MM" を直接確認
            ->assertSee('name="clock_in"', false)
            ->assertSee('value="09:00"', false)
            ->assertSee('name="clock_out"', false)
            ->assertSee('value="18:00"', false);
    }

    /** @test */
    public function 勤怠詳細_休憩が打刻と一致している_2本対応()
    {
        $user = User::factory()->create();
        $attendance = $this->seedAttendanceWithTwoBreaks($user);
        /** @var User $user */

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertOk()
            // 休憩1
            ->assertSee('name="break_1_start"', false)
            ->assertSee('value="12:00"', false)
            ->assertSee('name="break_1_end"', false)
            ->assertSee('value="12:30"', false)
            // 休憩2
            ->assertSee('name="break_2_start"', false)
            ->assertSee('value="15:00"', false)
            ->assertSee('name="break_2_end"', false)
            ->assertSee('value="15:15"', false);
    }
}
