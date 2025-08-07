<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'break_start' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],
            'break_end' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in', 'before_or_equal:clock_out'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }


    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'break_start.after_or_equal' => '休憩開始時間は出勤時間以降にしてください',
            'break_start.before_or_equal' => '休憩時間が勤務時間外です',
            'break_end.after_or_equal' => '休憩終了時間は出勤時間以降にしてください',
            'break_end.before_or_equal' => '休憩時間が勤務時間外です',
            'note.required' => '備考を記入してください',
        ];
    }
}
