<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // is_admin ミドルウェアで守られている想定
    }

    public function rules(): array
    {
        return [
            'clock_in'       => ['required', 'date_format:H:i'],
            'clock_out'      => ['required', 'date_format:H:i', 'after:clock_in'],

            'break_1_start'  => ['nullable', 'date_format:H:i'],
            'break_1_end'    => ['nullable', 'date_format:H:i', 'after:break_1_start'],

            'break_2_start'  => ['nullable', 'date_format:H:i'],
            'break_2_end'    => ['nullable', 'date_format:H:i', 'after:break_2_start'],

            'memo'           => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_out.after'     => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_1_end.after'   => '休憩時間が勤務時間外です。',
            'break_2_end.after'   => '休憩時間が勤務時間外です。',
            'memo.required'       => '備考を記入してください。',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $ci = $this->input('clock_in');
            $co = $this->input('clock_out');
            if (!$ci || !$co) return;

            $clockIn  = Carbon::createFromFormat('H:i', $ci);
            $clockOut = Carbon::createFromFormat('H:i', $co);

            // 各休憩が勤務時間内に収まっているかチェック
            foreach ([1, 2] as $i) {
                $bs = $this->input("break_{$i}_start");
                $be = $this->input("break_{$i}_end");

                if ($bs) {
                    $bsC = Carbon::createFromFormat('H:i', $bs);
                    if ($bsC->lt($clockIn) || $bsC->gt($clockOut)) {
                        $v->errors()->add("break_{$i}_start", '休憩時間が勤務時間外です。');
                    }
                }
                if ($be) {
                    $beC = Carbon::createFromFormat('H:i', $be);
                    if ($beC->lt($clockIn) || $beC->gt($clockOut)) {
                        $v->errors()->add("break_{$i}_end", '休憩時間が勤務時間外です。');
                    }
                }
            }
        });
    }
}
