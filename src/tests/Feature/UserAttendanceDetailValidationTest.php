<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class UserAttendanceDetailValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 一般ユーザー_勤怠詳細_出退勤の時系列エラーを表示する()
    {
        $user = User::factory()->create(['is_admin' => 0]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today()->toDateString(),
        ]);
        /** @var User $user */
        $this->actingAs($user);

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('attendance.update', $attendance->id), [
                'clock_in'      => '12:00',
                'clock_out'     => '10:00', // 逆転
                // 休憩は省略（任意）
                'note'          => 'テスト',
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 一般ユーザー_勤怠詳細_備考未入力でエラーを表示する()
    {
        $user = User::factory()->create(['is_admin' => 0]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today()->toDateString(),
        ]);
        /** @var User $user */
        $this->actingAs($user);

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('attendance.update', $attendance->id), [
                'clock_in'  => '09:00',
                'clock_out' => '18:00',
                // note 未入力
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function 一般ユーザー_修正申請が登録され承認待ちになる()
    {
        $user = User::factory()->create(['is_admin' => 0]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today()->toDateString(),
        ]);
        /** @var User $user */
        $this->actingAs($user);

        $res = $this->put(route('attendance.update', $attendance->id), [
            'clock_in'      => '09:00',
            'clock_out'     => '18:00',
            // 2本対応の実装であれば以下のキーが吸収されます。1本実装でも問題なし。
            'break_1_start' => '12:00',
            'break_1_end'   => '13:00',
            'note'          => '時間の修正',
        ]);

        $res->assertRedirect(route('attendance.list'));

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id'  => $user->id,
            'date'     => $attendance->date,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'status'   => 'pending',
        ]);
    }
}
