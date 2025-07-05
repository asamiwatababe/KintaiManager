<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreaksTable extends Migration
{
    public function up()
    {
        Schema::create('break_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->timestamp('break_in')->nullable();
            $table->timestamp('break_out')->nullable();
            $table->timestamps();

            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('breaks');
    }
}
