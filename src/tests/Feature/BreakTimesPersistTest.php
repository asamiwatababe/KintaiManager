<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimesPersistTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者更新で休憩2本が保存される()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user  = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 既存休憩をクリアしてから保存される実装を検証
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in'  => Carbon::parse($attendance->date . ' 12:00'),
            'break_out' => Carbon::parse($attendance->date . ' 13:00'),
        ]);
        /** @var User $admin */
        $this->actingAs($admin);

        $res = $this->put(route('admin.attendance.update', $attendance->id), [
            'clock_in'       => '09:00',
            'clock_out'      => '18:00',
            'break_1_start'  => '12:00',
            'break_1_end'    => '12:30',
            'break_2_start'  => '15:00',
            'break_2_end'    => '15:15',
            'memo'           => '管理者修正',
        ]);

        $res->assertRedirect(); // 成功で元画面へ

        $attendance->refresh();
        $this->assertEquals('09:00:00', $attendance->clock_in);
        $this->assertEquals('18:00:00', $attendance->clock_out);

        $breaks = $attendance->breaks()->orderBy('break_in')->get();
        $this->assertCount(2, $breaks);
        $this->assertEquals('12:00:00', $breaks[0]->break_in->format('H:i:s'));
        $this->assertEquals('12:30:00', $breaks[0]->break_out->format('H:i:s'));
        $this->assertEquals('15:00:00', $breaks[1]->break_in->format('H:i:s'));
        $this->assertEquals('15:15:00', $breaks[1]->break_out->format('H:i:s'));
    }
}
