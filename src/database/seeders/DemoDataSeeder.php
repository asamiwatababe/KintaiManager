<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 管理者（何度実行しても同じメールは更新に回す）
            User::updateOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name'              => '管理者',
                    'password'          => Hash::make('password'), // 開発用パスワード
                    'is_admin'          => 1,
                    'email_verified_at' => now(),
                ]
            );

            // 一般ユーザー 2名
            $staffsData = [
                ['name' => '山田太郎', 'email' => 'yamada@example.com'],
                ['name' => '鈴木花子', 'email' => 'suzuki@example.com'],
            ];

            $staffs = collect($staffsData)->map(function ($row) {
                return User::updateOrCreate(
                    ['email' => $row['email']],
                    [
                        'name'              => $row['name'],
                        'password'          => Hash::make('password'),
                        'is_admin'          => 0,
                        'email_verified_at' => now(),
                    ]
                );
            });

            // サンプル勤怠（2025/01/10, 2025/01/20 の2日ぶん）
            $days = [
                Carbon::create(2025, 1, 10),
                Carbon::create(2025, 1, 20),
            ];

            foreach ($staffs as $user) {
                foreach ($days as $day) {
                    // user_id + date で upsert 的に
                    $attendance = Attendance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date'    => $day->toDateString(),
                        ],
                        [
                            'clock_in'  => $day->copy()->setTime(9, 0),   // 09:00
                            'clock_out' => $day->copy()->setTime(18, 0),  // 18:00
                            'status'    => '退勤済',
                            'note'      => null,
                        ]
                    );

                    // 休憩は一旦全削除して固定2本を入れ直す（冪等化のため）
                    $attendance->breaks()->delete();
                    $attendance->breaks()->create([
                        'break_in'  => $day->copy()->setTime(12, 0),
                        'break_out' => $day->copy()->setTime(12, 30),
                    ]);
                    $attendance->breaks()->create([
                        'break_in'  => $day->copy()->setTime(15, 30),
                        'break_out' => $day->copy()->setTime(15, 45),
                    ]);
                }
            }
        });
    }
}
