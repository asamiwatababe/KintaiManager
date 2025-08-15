<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;

class StampCorrectionRoutesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 申請一覧_共通パスが一般ユーザー用の画面を返す()
    {
        $user = User::factory()->create(['is_admin' => 0]);
        /** @var User $user */
        $this->actingAs($user)
            ->get('/stamp_correction_request/list')
            ->assertOk()
            ->assertSee('申請一覧');
    }

    /** @test */
    public function 申請一覧_共通パスが管理者用の画面を返す()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        /** @var User $admin */
        $this->actingAs($admin)
            ->get('/stamp_correction_request/list')
            ->assertOk()
            ->assertSee('申請一覧');
    }

    /** @test */
    public function 申請詳細_共通パスにアクセスできる()
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $user  = User::factory()->create(['is_admin' => 0]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => Carbon::today()->toDateString(),
        ]);

        $req = StampCorrectionRequest::create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'date'          => $attendance->date,
            'clock_in'      => '09:00:00',
            'clock_out'     => '18:00:00',
            'note'          => '修正',
            'status'        => 'pending',
        ]);

        // 管理者は承認詳細ビュー（200）
        /** @var User $admin */
        $this->actingAs($admin)
            ->get("/stamp_correction_request/{$req->id}")
            ->assertOk();

        // 一般ユーザーは勤怠詳細へ 302 リダイレクト（これが仕様）
        /** @var User $user */
        $res = $this->actingAs($user)
            ->get("/stamp_correction_request/{$req->id}");
        $res->assertStatus(302);
        $res->assertRedirect(route('attendance.pending', $attendance->id));

        // もしくはリダイレクト追従して最終 200 を確認したい場合は以下でもOK
        $this->actingAs($user)
            ->followingRedirects()
            ->get("/stamp_correction_request/{$req->id}")
            ->assertOk()
            ->assertSee('勤怠詳細'); // 最終ページの文言確認
    }
}
