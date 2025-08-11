<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ルートで is_admin ミドルウェア済みなので true でOK
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in'   => ['required', 'date_format:H:i'],
            'clock_out'  => ['required', 'date_format:H:i', 'after:clock_in'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end'  => ['nullable', 'date_format:H:i', 'after:break_start'],
            'memo'       => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            // 要件の固定文言（管理者は「です。」）
            'clock_out.after'   => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_end.after'   => '休憩時間が勤務時間外です。',
            'memo.required'     => '備考を記入してください。',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $ci = $this->input('clock_in');
            $co = $this->input('clock_out');
            $bs = $this->input('break_start');
            $be = $this->input('break_end');

            if (!$ci || !$co) return;

            $clockIn  = Carbon::createFromFormat('H:i', $ci);
            $clockOut = Carbon::createFromFormat('H:i', $co);

            // 休憩が勤務時間外に出ていないか（どちらか片方だけ入っててもチェック）
            if ($bs) {
                $breakStart = Carbon::createFromFormat('H:i', $bs);
                if ($breakStart->lt($clockIn) || $breakStart->gt($clockOut)) {
                    $v->errors()->add('break_start', '休憩時間が勤務時間外です。');
                }
            }
            if ($be) {
                $breakEnd = Carbon::createFromFormat('H:i', $be);
                if ($breakEnd->lt($clockIn) || $breakEnd->gt($clockOut)) {
                    $v->errors()->add('break_end', '休憩時間が勤務時間外です。');
                }
            }
        });
    }
}
