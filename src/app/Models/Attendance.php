<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;


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

    public function getBreakMinutesAttribute(): int
    {
        $mins = 0;
        foreach ($this->breaks ?? [] as $b) {
            if ($b->break_in && $b->break_out) {
                $mins += Carbon::parse($b->break_out)->diffInMinutes(Carbon::parse($b->break_in));
            }
        }
        return $mins;
    }

    private function formatHm(int $minutes): string
    {
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return "{$h}h " . sprintf('%02dm', $m);
    }

    public function getBreakDurationAttribute(): string
    {
        // 休憩ゼロは "-" でよければこれ、"0h 00m" を出したければ return $this->formatHm(0);
        return $this->break_minutes > 0 ? $this->formatHm($this->break_minutes) : '-';
    }

    public function getWorkDurationAttribute(): string
    {
        if (!$this->clock_in || !$this->clock_out) return '-';
        $start = Carbon::parse($this->clock_in);
        $end   = Carbon::parse($this->clock_out);
        $mins  = $end->diffInMinutes($start) - $this->break_minutes;
        if ($mins < 0) $mins = 0;
        return $this->formatHm($mins);
    }
}
