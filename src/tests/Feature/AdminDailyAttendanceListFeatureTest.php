<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminDailyAttendanceListFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** その日になされた全ユーザーの勤怠情報が正確に確認できる + 画面に当日の日付が出ている */
    public function test_管理者_日次一覧_当日全ユーザーの勤怠が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 2, 9, 0, 0));

        $admin = User::factory()->create(['is_admin' => 1]);
        $u1 = User::factory()->create(['is_admin' => 0, 'name' => '山田太郎']);
        $u2 = User::factory()->create(['is_admin' => 0, 'name' => '鈴木花子']);

        // 当日のデータ
        Attendance::factory()->create([
            'user_id'   => $u1->id,
            'date'      => '2025-01-02',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        Attendance::factory()->create([
            'user_id'   => $u2->id,
            'date'      => '2025-01-02',
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
        ]);
        // 別日（当日画面に出ないことの確認用）
        Attendance::factory()->create([
            'user_id'   => $u1->id,
            'date'      => '2025-01-03',
            'clock_in'  => '08:00:00',
            'clock_out' => '17:00:00',
        ]);

        /** @var User $admin */
        $res = $this->actingAs($admin)->get(route('admin.attendance.list'));
        $res->assertOk();

        // 見出しの日付は "Y年n月j日" or "Y/m/d" いずれでもパスできるように
        $html = $res->getContent();
        $this->assertTrue(
            str_contains($html, '2025年1月2日') || str_contains($html, '2025/01/02'),
            '当日の日付が見出しとして表示されていること'
        );

        // 当日の全ユーザーの勤怠が見えている
        $res->assertSee('山田太郎')
            ->assertSee('鈴木花子')
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('10:00')
            ->assertSee('19:00');

        // 別日の時刻は当日画面には出ない
        $res->assertDontSee('08:00')
            ->assertDontSee('17:00');
    }

    /** 「前日」「翌日」で切替でき、各日のデータが出る */
    public function test_管理者_日次一覧_前日翌日に切替できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 2, 9, 0, 0));

        $admin = User::factory()->create(['is_admin' => 1]);
        $u = User::factory()->create(['is_admin' => 0, 'name' => '当日さん']);

        Attendance::factory()->create([
            'user_id'   => $u->id,
            'date'      => '2025-01-01',
            'clock_in'  => '08:30:00',
            'clock_out' => '17:30:00',
        ]);
        Attendance::factory()->create([
            'user_id'   => $u->id,
            'date'      => '2025-01-02',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        Attendance::factory()->create([
            'user_id'   => $u->id,
            'date'      => '2025-01-03',
            'clock_in'  => '11:00:00',
            'clock_out' => '20:00:00',
        ]);

        // 当日
        /** @var User $admin */

        $res = $this->actingAs($admin)->get(route('admin.attendance.list'));
        $res->assertOk();
        $html = $res->getContent();
        $this->assertTrue(str_contains($html, '2025年1月2日') || str_contains($html, '2025/01/02'));
        $res->assertSee('09:00')->assertSee('18:00');

        // 前日
        $res = $this->get(route('admin.attendance.list', ['date' => '2025-01-01']));
        $res->assertOk();
        $html = $res->getContent();
        $this->assertTrue(str_contains($html, '2025年1月1日') || str_contains($html, '2025/01/01'));
        $res->assertSee('08:30')->assertSee('17:30')->assertDontSee('09:00');

        // 翌日
        $res = $this->get(route('admin.attendance.list', ['date' => '2025-01-03']));
        $res->assertOk();
        $html = $res->getContent();
        $this->assertTrue(str_contains($html, '2025年1月3日') || str_contains($html, '2025/01/03'));
        $res->assertSee('11:00')->assertSee('20:00')->assertDontSee('18:00');
    }

    /** 詳細リンクが勤怠詳細（/attendance/{id}）に向いている */
    public function test_管理者_日次一覧_詳細リンクが勤怠詳細に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 2, 9, 0, 0));

        $admin = User::factory()->create(['is_admin' => 1]);
        $u = User::factory()->create(['is_admin' => 0]);
        $a = Attendance::factory()->create([
            'user_id'   => $u->id,
            'date'      => '2025-01-02',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        /** @var User $admin */

        $this->actingAs($admin)
            ->get(route('admin.attendance.list'))
            ->assertOk()
            ->assertSee('/attendance/' . $a->id); // 画面中にリンク文字列が含まれること
    }
}
