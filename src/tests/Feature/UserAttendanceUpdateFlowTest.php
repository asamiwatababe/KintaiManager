<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAttendanceUpdateFlowTest extends TestCase
{
    use RefreshDatabase;

    /** 共通：勤怠1件を作ってログイン */
    private function seedAttendance(): array
    {
        /** @var User $user */
        $user = User::factory()->create(['is_admin' => 0]);

        /** @var Attendance $attendance */
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => Carbon::today()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($user);

        return [$user, $attendance];
    }

    /** @test */
    public function 出勤時間が退勤時間より後なら_エラーメッセージが表示される()
    {
        [, $attendance] = $this->seedAttendance();

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('attendance.update', $attendance->id), [
                'clock_in'      => '19:00',
                'clock_out'     => '18:00',
                'break_1_start' => null,
                'break_1_end'   => null,
                'break_2_start' => null,
                'break_2_end'   => null,
                'note'          => '理由',
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始が退勤時間より後なら_エラーメッセージが表示される()
    {
        [, $attendance] = $this->seedAttendance();

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('attendance.update', $attendance->id), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_1_start' => '19:00',
                'break_1_end'   => '19:15',
                'break_2_start' => null,
                'break_2_end'   => null,
                'note'          => '理由',
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'break_1_start' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    public function 休憩終了が退勤時間より後なら_エラーメッセージが表示される()
    {
        [, $attendance] = $this->seedAttendance();

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('attendance.update', $attendance->id), [
                'clock_in'      => '09:00',
                'clock_out'     => '18:00',
                'break_1_start' => '17:30',
                'break_1_end'   => '19:00',
                'break_2_start' => null,
                'break_2_end'   => null,
                'note'          => '理由',
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'break_1_end' => '休憩時間が勤務時間外です',
        ]);
    }

    /** @test */
    public function 備考未入力なら_エラーメッセージが表示される()
    {
        [, $attendance] = $this->seedAttendance();

        $res = $this->from(route('attendance.show', $attendance->id))
            ->put(route('attendance.update', $attendance->id), [
                'clock_in'      => '09:10',
                'clock_out'     => '18:00',
                'break_1_start' => '12:00',
                'break_1_end'   => '12:30',
                'break_2_start' => null,
                'break_2_end'   => null,
                'note'          => '', // 未入力
            ]);

        $res->assertRedirect(route('attendance.show', $attendance->id));
        $res->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function 修正申請処理が実行され_承認画面と申請一覧に反映される()
    {
        [$user, $attendance] = $this->seedAttendance();

        // 1) ユーザーが修正申請
        $res = $this->put(route('attendance.update', $attendance->id), [
            'clock_in'      => '09:05',
            'clock_out'     => '17:55',
            'break_1_start' => '12:00',
            'break_1_end'   => '12:30',
            'break_2_start' => '15:30',
            'break_2_end'   => '15:45',
            'note'          => '電車遅延のため',
        ]);

        // ✅ 成功時は一覧へリダイレクト（実装と一致）
        $res->assertRedirect(route('attendance.list'));

        // 申請レコードが pending で作られること
        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'date'          => $attendance->date,
            'clock_in'      => '09:05:00',
            'clock_out'     => '17:55:00',
            'break_in'      => '12:00:00',
            'break_out'     => '12:30:00',
            'note'          => '電車遅延のため',
            'status'        => 'pending',
        ]);

        $req = StampCorrectionRequest::latest('id')->first();

        // 2) 申請一覧（一般）：承認待ちに自分の申請が見える
        $this->get('/stamp_correction_request/list')
            ->assertOk()
            ->assertSee('申請一覧')
            ->assertSee($user->name)
            ->assertSee($attendance->date)
            ->assertSee('電車遅延のため');

        // 3) 詳細リンク（共通パス）：一般は勤怠詳細へリダイレクトされる可能性があるので 200/302 許容
        $r = $this->get("/stamp_correction_request/{$req->id}");
        $this->assertTrue(in_array($r->getStatusCode(), [200, 302]));

        // 4) 管理者が承認
        $admin = User::factory()->create(['is_admin' => 1]);
        /** @var User $admin */
        $this->actingAs($admin)
            ->post(route('stamp_correction_request.approve', $req->id))
            ->assertRedirect();

        // DB 反映（承認済み＆勤怠更新）
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id'     => $req->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id'        => $attendance->id,
            'clock_in'  => '09:05:00',
            'clock_out' => '17:55:00',
        ]);

        // 5) 一般の申請一覧：承認済みに載る
        $this->actingAs($user)
            ->get('/stamp_correction_request/list')
            ->assertOk()
            ->assertSee('承認済み')
            ->assertSee('電車遅延のため');
    }
}
