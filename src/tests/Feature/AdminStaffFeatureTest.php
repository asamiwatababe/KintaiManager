<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** スタッフ一覧に表示する一般ユーザー2名を作成 */
    private function makeUsersForList(): array
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'is_admin' => 1,
            'name'     => '管理者A',
            'email'    => 'admin@example.com',
        ]);

        /** @var User $u1 */
        $u1 = User::factory()->create([
            'is_admin' => 0,
            'name'     => '山田太郎',
            'email'    => 'taro@example.com',
        ]);

        /** @var User $u2 */
        $u2 = User::factory()->create([
            'is_admin' => 0,
            'name'     => '鈴木花子',
            'email'    => 'hanako@example.com',
        ]);

        return [$admin, $u1, $u2];
    }

    /** スタッフ月次用のデータ（同一ユーザーに複数月の勤怠） */
    private function makeStaffMonthlyData(): array
    {
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => 1]);

        /** @var User $staff */
        $staff = User::factory()->create([
            'is_admin' => 0,
            'name'     => '山田太郎',
            'email'    => 'taro@example.com',
        ]);

        // 2025-01（当月想定）
        /** @var Attendance $aJan10 */
        $aJan10 = Attendance::factory()->create([
            'user_id'   => $staff->id,
            'date'      => '2025-01-10',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
        /** @var Attendance $aJan20 */
        $aJan20 = Attendance::factory()->create([
            'user_id'   => $staff->id,
            'date'      => '2025-01-20',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 前月 2024-12
        Attendance::factory()->create([
            'user_id'   => $staff->id,
            'date'      => '2024-12-25',
            'clock_in'  => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        // 翌月 2025-02
        Attendance::factory()->create([
            'user_id'   => $staff->id,
            'date'      => '2025-02-03',
            'clock_in'  => '08:30:00',
            'clock_out' => '17:30:00',
        ]);

        return [$admin, $staff, $aJan10, $aJan20];
    }

    /** @test */
    public function 管理者はスタッフ一覧で一般ユーザーの氏名とメールを確認できる()
    {
        [$admin, $u1, $u2] = $this->makeUsersForList();

        /** @var User $admin */
        $this->actingAs($admin)
            ->get(route('admin.staff.list'))
            ->assertOk()
            ->assertSee('山田太郎')
            ->assertSee('taro@example.com')
            ->assertSee('鈴木花子')
            ->assertSee('hanako@example.com');
        // ※ 実装によっては管理者自身も出ることがありますが、要件は
        //   「一般ユーザーが見えること」なので存在確認のみとします。
    }

    /** @test */
    public function 管理者はスタッフの月次勤怠一覧で当月の情報を確認できる()
    {
        [$admin, $staff, $aJan10, $aJan20] = $this->makeStaffMonthlyData();

        /** @var User $admin */
        $this->actingAs($admin)
            ->get(route('admin.attendance.staff', ['id' => $staff->id, 'month' => '2025-01']))
            ->assertOk()
            ->assertSee('勤怠一覧')     // タイトル
            ->assertSee('2025/01')      // 見出しの月（Y/m想定）
            ->assertSee('山田太郎')     // 対象ユーザー名
            ->assertSee('01/10')        // 日付（m/d(D)表示の部分一致）
            ->assertSee('01/20')
            ->assertSee('09:00')        // 時刻は分までの表示想定
            ->assertSee('18:00');
    }

    /** @test */
    public function 管理者はスタッフ月次で前月と翌月に切替できる()
    {
        [$admin, $staff] = $this->makeStaffMonthlyData();

        $this->actingAs($admin);

        // 当月
        $this->get(route('admin.attendance.staff', ['id' => $staff->id, 'month' => '2025-01']))
            ->assertOk()
            ->assertSee('2025/01')
            ->assertSee($staff->name);

        // 前月
        $this->get(route('admin.attendance.staff', ['id' => $staff->id, 'month' => '2024-12']))
            ->assertOk()
            ->assertSee('2024/12')
            ->assertSee($staff->name);

        // 翌月
        $this->get(route('admin.attendance.staff', ['id' => $staff->id, 'month' => '2025-02']))
            ->assertOk()
            ->assertSee('2025/02')
            ->assertSee($staff->name);
    }


    /** @test */
    public function 管理者はスタッフ月次一覧から勤怠詳細へ遷移できるリンクを確認できる()
    {
        [$admin, $staff, $aJan10] = $this->makeStaffMonthlyData();

        /** @var User $admin */
        $this->actingAs($admin)
            ->get(route('admin.attendance.staff', ['id' => $staff->id, 'month' => '2025-01']))
            ->assertOk()
            // 画面内に /attendance/{id} へのリンクが含まれていること（統一済みの詳細パス）
            ->assertSee('/attendance/' . $aJan10->id);
    }
}
