<?php

namespace Database\Factories;

use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition(): array
    {
        $date = Carbon::today()->toDateString();

        return [
            'attendance_id' => Attendance::factory(),
            'break_in'      => Carbon::parse("$date 12:00:00"),
            'break_out'     => Carbon::parse("$date 12:30:00"),
        ];
    }

    public function at(string $in, string $out, ?string $date = null): self
    {
        $date = $date ?? Carbon::today()->toDateString();

        return $this->state(fn() => [
            'break_in'  => Carbon::parse("$date $in:00"),
            'break_out' => Carbon::parse("$date $out:00"),
        ]);
    }
}
