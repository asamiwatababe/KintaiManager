<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'date'      => Carbon::today()->toDateString(),
            // 実装が time カラム/文字列前提なので H:iで入れる
            'clock_in'  => null,
            'clock_out' => null,
            'note'      => null,
            'status'    => 'approved', // 既定（pending/approved 適宜）
        ];
    }

    public function withTimes(string $in = '09:00', string $out = '18:00'): self
    {
        return $this->state(fn() => [
            'clock_in'  => $in . ':00',
            'clock_out' => $out . ':00',
        ]);
    }

    public function today(): self
    {
        return $this->state(fn() => [
            'date' => Carbon::today()->toDateString(),
        ]);
    }
}
