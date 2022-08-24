<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partisipant_id');
            $table->unsignedBigInteger('petugas_id')->nullable();
            $table->enum('status', ['requested', 'accepted', 'rejected'])->nullable();
            $table->string('catatan_partisipant')->nullable();
            $table->string('catatan_petugas')->nullable();
            $table->string('bukti')->nullable();
            $table->date('tanggal_validasi')->nullable();
            $table->foreign('partisipant_id')
                ->references('id')->on('partisipants')->onDelete('cascade')->constrained('partisipants');
            $table->foreign('petugas_id')
                ->references('id')->on('petugas')->onDelete('set null')->constrained('petugas');
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
        Schema::dropIfExists('schedule_requests');
    }
}
