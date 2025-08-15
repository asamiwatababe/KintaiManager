<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStampCorrectionFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ち一覧に全ユーザーの未承認申請が表示され_承認済み一覧には承認済みが表示される()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => 1]);

        // 3人分のユーザー + 勤怠 + 申請
        $u1 = User::factory()->create(['is_admin' => 0, 'name' => '山田太郎']);
        $u2 = User::factory()->create(['is_admin' => 0, 'name' => '鈴木花子']);
        $u3 = User::factory()->create(['is_admin' => 0, 'name' => '佐藤次郎']);

        $a1 = Attendance::factory()->create(['user_id' => $u1->id, 'date' => '2025-01-02']);
        $a2 = Attendance::factory()->create(['user_id' => $u2->id, 'date' => '2025-01-02']);
        $a3 = Attendance::factory()->create(['user_id' => $u3->id, 'date' => '2025-01-03']);

        // 承認待ち 2件
        StampCorrectionRequest::create([
            'user_id'       => $u1->id,
            'attendance_id' => $a1->id,
            'date'          => '2025-01-02',
            'clock_in'      => '09:05:00',
            'clock_out'     => '17:55:00',
            'break_in'      => '12:00:00',
            'break_out'     => '12:30:00',
            'note'          => '遅延のため',
            'status'        => 'pending',
        ]);
        StampCorrectionRequest::create([
            'user_id'       => $u2->id,
            'attendance_id' => $a2->id,
            'date'          => '2025-01-02',
            'clock_in'      => '09:10:00',
            'clock_out'     => '18:10:00',
            'note'          => '体調不良',
            'status'        => 'pending',
        ]);

        // 承認済み 1件
        StampCorrectionRequest::create([
            'user_id'       => $u3->id,
            'attendance_id' => $a3->id,
            'date'          => '2025-01-03',
            'clock_in'      => '10:00:00',
            'clock_out'     => '19:00:00',
            'note'          => '業務都合',
            'status'        => 'approved',
        ]);

        // 一覧（共通パス）。管理者では管理者ビューが返る実装
        $this->actingAs($admin)
            ->get('/stamp_correction_request/list')
            ->assertOk()
            ->assertSee('申請一覧')
            // 承認待ち 2名の表示（HTML全体中に含まれていればOK。タブはJSで切替だが両テーブルは同ページに描画される実装）
            ->assertSee('山田太郎')
            ->assertSee('鈴木花子')
            // 承認済み 1名の表示
            ->assertSee('佐藤次郎')
            ->assertSee('承認済み');
    }

    /** @test */
    public function 申請詳細画面で申請内容が正しく表示される()
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
        ]);

        $req = StampCorrectionRequest::create([
            'user_id'       => $member->id,
            'attendance_id' => $attendance->id,
            'date'          => '2025-01-02',
            'clock_in'      => '09:05:00',
            'clock_out'     => '17:55:00',
            'break_in'      => '12:00:00',
            'break_out'     => '12:30:00',
            'note'          => '電車遅延',
            'status'        => 'pending',
        ]);

        $this->actingAs($admin)
            ->get("/stamp_correction_request/{$req->id}")
            ->assertOk()
            ->assertSee('山田太郎')
            ->assertSee('2025年1月2日') // 画面側は Y年n月j日 表示
            ->assertSee('09:05')
            ->assertSee('17:55')
            ->assertSee('12:00')
            ->assertSee('12:30')
            ->assertSee('電車遅延');
    }

    /** @test */
    public function 承認ボタンで修正申請が承認され勤怠が更新される()
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => 1]);
        /** @var User $member */
        $member = User::factory()->create(['is_admin' => 0]);

        /** @var Attendance $attendance */
        $attendance = Attendance::factory()->create([
            'user_id'   => $member->id,
            'date'      => '2025-01-02',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'note'      => '元の備考',
        ]);

        $req = StampCorrectionRequest::create([
            'user_id'       => $member->id,
            'attendance_id' => $attendance->id,
            'date'          => '2025-01-02',
            'clock_in'      => '09:10:00',
            'clock_out'     => '17:50:00',
            'break_in'      => '12:05:00',
            'break_out'     => '12:35:00',
            'note'          => '修正お願いします',
            'status'        => 'pending',
        ]);

        // 承認実行（管理者のみ）
        $res = $this->actingAs($admin)
            ->post(route('stamp_correction_request.approve', $req->id));

        // リダイレクト（どこへでも可）
        $res->assertRedirect();

        // 申請が承認済みになっていること
        $this->assertDatabaseHas('stamp_correction_requests', [
            'id'     => $req->id,
            'status' => 'approved',
        ]);

        // 勤怠に反映されていること（note は req->note を反映する実装）
        $this->assertDatabaseHas('attendances', [
            'id'        => $attendance->id,
            'clock_in'  => '09:10:00',
            'clock_out' => '17:50:00',
            'note'      => '修正お願いします',
        ]);
    }
}
