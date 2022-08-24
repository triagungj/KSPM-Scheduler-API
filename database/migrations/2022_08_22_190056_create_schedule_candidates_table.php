<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_candidates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_request_id');
            $table->unsignedBigInteger('session_id');
            $table->foreign('schedule_request_id')
                ->references('id')->on('schedule_requests')->onDelete('cascade')->constrained('schedule_requests');
            $table->foreign('session_id')
                ->references('id')->on('sesis')->onDelete('cascade')->constrained('sesis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedule_candidates');
    }
}
