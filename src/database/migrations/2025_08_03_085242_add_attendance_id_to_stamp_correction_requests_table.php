<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttendanceIdToStampCorrectionRequestsTable extends Migration
{
    public function up()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_id')->nullable()->after('user_id');

            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('stamp_correction_requests', function (Blueprint $table) {
            $table->dropForeign(['attendance_id']);
            $table->dropColumn('attendance_id');
        });
    }
}
