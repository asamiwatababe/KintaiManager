<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
        'user_id',
        'attendance_id',
        'date',
        'clock_in',
        'clock_out',
        'break_in',
        'break_out',
        'note',
        'status',
        'admin_comment',
    ];

    /** 申請者ユーザー（多対1） */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** 対象勤怠（多対1） */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
