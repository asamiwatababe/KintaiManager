<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function makeAttendance(User $user, string $date, ?string $in, ?string $out): Attendance
    {
        return Attendance::factory()->create([
            'user_id'  => $user->id,
            'date'     => $date,
            'clock_in' => $in,
            'clock_out' => $out,
        ]);
    }

    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        // 基準日：2025-01-15
        Carbon::setTestNow('2025-01-15 10:00:00');

        $me     = User::factory()->create();
        $other  = User::factory()->create();

        // 自分の今月の勤怠（2件）
        $a1 = $this->makeAttendance($me,  '2025-01-02', '09:00:00', '18:00:00');
        $a2 = $this->makeAttendance($me,  '2025-01-10', '10:00:00', '19:00:00');
        // 別ユーザーの勤怠（混入しないことを確認）
        $this->makeAttendance($other, '2025-01-03', '08:00:00', '17:00:00');

        // 一覧（今月）を表示
        /** @var User $me */
        $this->actingAs($me)
            ->get(route('attendance.list', ['month' => '2025-01']))
            ->assertOk()
            // 自分の2件の時刻が見えている
            ->assertSee('09:00:00')
            ->assertSee('18:00:00')
            ->assertSee('10:00:00')
            ->assertSee('19:00:00')
            // 他人の時刻は出ない
            ->assertDontSee('08:00:00')
            ->assertDontSee('17:00:00');
    }

    /** @test */
    public function 一覧に遷移した際_現在の月が表示される()
    {
        Carbon::setTestNow('2025-01-15 10:00:00');

        $me = User::factory()->create();
        // レコードが無くても月表示はされる仕様
        /** @var User $me */

        $this->actingAs($me)
            ->get(route('attendance.list')) // month 指定なし → 現在の月
            ->assertOk()
            ->assertSee('2025/01'); // 画面の「Y/m」表記に一致
    }

    /** @test */
    public function 前月ボタン相当_前月の情報が表示される()
    {
        Carbon::setTestNow('2025-01-15 10:00:00');

        $me = User::factory()->create();
        // 前月(2024-12)にだけユニークな時刻を入れておく
        $this->makeAttendance($me, '2024-12-05', '09:30:00', '17:30:00');
        // 今月データ（混入チェック用）
        $this->makeAttendance($me, '2025-01-02', '09:00:00', '18:00:00');
        /** @var User $me */

        $this->actingAs($me)
            ->get(route('attendance.list', ['month' => '2024-12']))
            ->assertOk()
            ->assertSee('2024/12')
            ->assertSee('09:30:00')
            ->assertSee('17:30:00')
            ->assertDontSee('09:00:00') // 今月のは出ない
            ->assertDontSee('18:00:00');
    }

    /** @test */
    public function 翌月ボタン相当_翌月の情報が表示される()
    {
        Carbon::setTestNow('2025-01-15 10:00:00');

        $me = User::factory()->create();
        // 翌月(2025-02)にユニークな時刻
        $this->makeAttendance($me, '2025-02-05', '12:34:00', '20:45:00');
        // 今月データ（混入チェック用）
        $this->makeAttendance($me, '2025-01-02', '09:00:00', '18:00:00');
        /** @var User $me */

        $this->actingAs($me)
            ->get(route('attendance.list', ['month' => '2025-02']))
            ->assertOk()
            ->assertSee('2025/02')
            ->assertSee('12:34:00')
            ->assertSee('20:45:00')
            ->assertDontSee('09:00:00') // 今月のは出ない
            ->assertDontSee('18:00:00');
    }

    /** @test */
    public function 詳細リンクでその日の勤怠詳細に遷移する()
    {
        Carbon::setTestNow('2025-01-15 10:00:00');

        $me = User::factory()->create();
        // status を pending 以外に（通常の詳細に飛ぶため）
        $attendance = $this->makeAttendance($me, '2025-01-10', '10:00:00', '19:00:00');
        /** @var User $me */

        $this->actingAs($me)
            ->get(route('attendance.list', ['month' => '2025-01']))
            ->assertOk()
            ->assertSee('詳細'); // リンクが出ている

        // 実際に詳細にアクセスして 200
        /** @var User $me */

        $this->actingAs($me)
            ->get(route('attendance.show', $attendance->id))
            ->assertOk()
            ->assertSee('勤怠詳細');
    }
}
