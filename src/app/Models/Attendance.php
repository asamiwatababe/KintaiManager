<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attendance extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
            'user_id',
            'date',
            'clock_in',
            'clock_out',
            'note',
            'status',
        ];

    /** 所有者ユーザー（多対1） */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** 休憩（1対多） */
    public function breaks(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * 修正申請（1対多）
     * すべての申請にアクセスしたい場合はこちら
     */
    public function stampCorrectionRequests(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    /**
     * 最新の修正申請を1件だけ取得
     * 既存の `correctionRequest()` 呼び出しを壊さないため latest に変更
     */
    public function correctionRequest(): HasOne
    {
        return $this->hasOne(StampCorrectionRequest::class)->latestOfMany();
    }
}
