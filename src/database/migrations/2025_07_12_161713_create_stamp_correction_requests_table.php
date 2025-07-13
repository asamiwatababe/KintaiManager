<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStampCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ユーザーとの関連
            $table->date('date'); // 修正対象日
            $table->time('clock_in')->nullable(); // 出勤時間（修正後）
            $table->time('clock_out')->nullable(); // 退勤時間（修正後）
            $table->time('break_in')->nullable(); // 休憩開始（修正後）
            $table->time('break_out')->nullable(); // 休憩終了（修正後）
            $table->text('note')->nullable(); // 申請者の備考
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // ステータス
            $table->text('admin_comment')->nullable(); // 管理者のコメント
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
}
