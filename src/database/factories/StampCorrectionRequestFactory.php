<?php

namespace Database\Factories;

use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;

    public function definition(): array
    {
        $date = Carbon::today()->toDateString();

        return [
            'user_id'       => User::factory(),
            'attendance_id' => null,
            'date'          => $date,
            'clock_in'      => '09:00:00',
            'clock_out'     => '18:00:00',
            'break_in'      => null,
            'break_out'     => null,
            'note'          => $this->faker->sentence(4),
            'status'        => 'pending',
        ];
    }

    public function linkAttendance(Attendance $attendance): self
    {
        return $this->state(fn() => [
            'attendance_id' => $attendance->id,
            'date'          => $attendance->date,
        ]);
    }

    public function approved(): self
    {
        return $this->state(fn() => ['status' => 'approved']);
    }
}
