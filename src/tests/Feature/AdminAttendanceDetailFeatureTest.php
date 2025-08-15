<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** 管理者 + 対象勤怠（休憩2本付き）を用意 */
    private function seedAdminAndAttendance(): array
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => 1]);

        /** @var User $member */
        $member = User::factory()->create(['is_admin' => 0, 'name' => '山田太郎']);

        /** @var Attendance $attendance */
        $attendance = Attendance::factory()->create([
            'user_id'   => $member->id,
            'date'      => '2025-01-02',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'note'      => '既存の備考',
        ]);

        // 休憩2本
        $attendance->breaks()->create([
            'break_in'  => Carbon::parse('2025-01-02 12:00:00'),
            'break_out' => Carbon::parse('2025-01-02 12:30:00'),
        ]);
        $attendance->breaks()->create([
            'break_in'  => Carbon::parse('2025-01-02 15:30:00'),
            'break_out' => Carbon::parse('2025-01-02 15:45:00'),
        ]);

        return [$admin, $attendance, $member];
    }

    /** @test */
    public function 勤怠詳細画面に選択したデータが表示される()
    {
        [$admin, $attendance, $member] = $this->seedAdminAndAttendance();

        /** @var User $admin */
        $this->actingAs($admin)
            // 表示は共通パス /attendance/{id}
            ->get(route('attendance.show', $attendance->id))
            ->assertOk()
            // 氏名
            ->assertSee($member->name)
            // 日付（Y年n月j日）
            ->assertSee('2025年1月2日')
            // 出勤・退勤（分まで）
            ->assertSee('09:00')
            ->assertSee('18:00')
            // 休憩（2本分）
            ->assertSee('12:00')->assertSee('12:30')
            ->assertSee('15:30')->assertSee('15:45');
    }

    /** @test */
    public function 出勤時間が退勤時間より後なら_エラーメッセージが表示される()
    {
        [$admin, $attendance] = $this->seedAdminAndAttendance();

        $this->actingAs($admin)
            // 戻り先も共通パス
            ->from(route('attendance.show', $attendance->id))
            // 更新は管理者用ルート
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in'      => '19:00',   // 退勤より後
                'clock_out'     => '18:00',
                'break_1_start' => '12:00',
                'break_1_end'   => '12:30',
                'break_2_start' => '15:30',
                'break_2_end'   => '15:45',
                'memo'          => '理由',
            ])
            // リダイレクト先は共通パス
            ->assertRedirect(route('attendance.show', $attendance->id))
            ->assertSessionHasErrors([
                'clock_out' => '出勤時間もしくは退勤時間が不適切な値です。', // Admin用メッセージ（句点あり）
            ]);
    }

    /** @test */
    public function 休憩開始が勤務時間外なら_エラーメッセージが表示される()
    {
        [$admin, $attendance] = $this->seedAdminAndAttendance();

        $this->actingAs($admin)
            ->from(route('attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_1_start' => '19:00',   // 勤務時間外
                'break_1_end'   => '19:15',
                'break_2_start' => null,
                'break_2_end'   => null,
                'memo'          => '理由',
            ])
            ->assertRedirect(route('attendance.show', $attendance->id))
            ->assertSessionHasErrors([
                'break_1_start' => '休憩時間が勤務時間外です。', // Admin用メッセージ
            ]);
    }

    /** @test */
    public function 休憩終了が勤務時間外なら_エラーメッセージが表示される()
    {
        [$admin, $attendance] = $this->seedAdminAndAttendance();

        $this->actingAs($admin)
            ->from(route('attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_1_start' => '17:30',
                'break_1_end'   => '19:00',   // 勤務時間外
                'break_2_start' => null,
                'break_2_end'   => null,
                'memo'          => '理由',
            ])
            ->assertRedirect(route('attendance.show', $attendance->id))
            ->assertSessionHasErrors([
                'break_1_end' => '休憩時間が勤務時間外です。', // Admin用メッセージ
            ]);
    }

    /** @test */
    public function 備考未入力なら_エラーメッセージが表示される()
    {
        [$admin, $attendance] = $this->seedAdminAndAttendance();

        $this->actingAs($admin)
            ->from(route('attendance.show', $attendance->id))
            ->put(route('admin.attendance.update', $attendance->id), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_1_start' => '12:00',
                'break_1_end'   => '12:30',
                'break_2_start' => null,
                'break_2_end'   => null,
                'memo'          => '', // 未入力
            ])
            ->assertRedirect(route('attendance.show', $attendance->id))
            ->assertSessionHasErrors([
                'memo' => '備考を記入してください。', // Admin用メッセージ
            ]);
    }
}
