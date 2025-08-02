<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'break_in',
        'break_out',
        'note',
        'status',
        'admin_comment',
    ];


    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
}
