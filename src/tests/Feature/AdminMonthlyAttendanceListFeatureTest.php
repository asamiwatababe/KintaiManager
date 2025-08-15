<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminMonthlyAttendanceListFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** 現在の月が表示され、当月の全ユーザーの勤怠が見える */
    public function test_管理者_月次一覧_現在の月と当月データが表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => 1]);

        $u1 = User::factory()->create(['is_admin' => 0, 'name' => '山田太郎']);
        $u2 = User::factory()->create(['is_admin' => 0, 'name' => '鈴木花子']);

        // 当月(2025-01)の勤怠
        Attendance::factory()->create([
            'user_id'   => $u1->id,
            'date'      => '2025-01-10',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        Attendance::factory()->create([
            'user_id'   => $u2->id,
            'date'      => '2025-01-20',
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.list')) // month指定なし=当月
            ->assertOk()
            ->assertSee('勤怠一覧')
            ->assertSee('2025/01')        // 見出しの月
            ->assertSee('山田太郎')
            ->assertSee('鈴木花子')
            ->assertSee('01/10')          // 日付表示は m/d(D) なので部分一致
            ->assertSee('01/20')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('10:00')
            ->assertSee('19:00');
    }

    /** 前月・翌月へ切替でき、各月のデータが表示される */
    public function test_管理者_月次一覧_前月翌月に切替できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => 1]);

        $u = User::factory()->create(['is_admin' => 0, 'name' => 'テストユーザー']);

        // 前月(2024-12)・当月(2025-01)・翌月(2025-02)に跨るデータ
        Attendance::factory()->create([
            'user_id'   => $u->id,
            'date'      => '2024-12-15',
            'clock_in'  => '08:30:00',
            'clock_out' => '17:30:00',
        ]);
        Attendance::factory()->create([
            'user_id'   => $u->id,
            'date'      => '2025-01-05',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        Attendance::factory()->create([
            'user_id'   => $u->id,
            'date'      => '2025-02-01',
            'clock_in'  => '11:00:00',
            'clock_out' => '20:00:00',
        ]);

        // 当月
        $this->actingAs($admin)
            ->get(route('admin.attendance.list'))
            ->assertOk()
            ->assertSee('2025/01')
            ->assertSee('01/05');

        // 前月（?month=2024-12）
        $this->get(route('admin.attendance.list', ['month' => '2024-12']))
            ->assertOk()
            ->assertSee('2024/12')
            ->assertSee('12/15')
            ->assertSee('08:30')
            ->assertSee('17:30');

        // 翌月（?month=2025-02）
        $this->get(route('admin.attendance.list', ['month' => '2025-02']))
            ->assertOk()
            ->assertSee('2025/02')
            ->assertSee('02/01')
            ->assertSee('11:00')
            ->assertSee('20:00');
    }

    /** 「詳細」リンクが共通の勤怠詳細（/attendance/{id}）に向いている */
    public function test_管理者_月次一覧_詳細リンクが勤怠詳細に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 15, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => 1]);

        $u = User::factory()->create(['is_admin' => 0]);
        $a = Attendance::factory()->create([
            'user_id'   => $u->id,
            'date'      => '2025-01-10',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.list'))
            ->assertOk()
            // 画面中に /attendance/{id} へのリンクが含まれる想定
            ->assertSee('/attendance/' . $a->id);
    }
}
