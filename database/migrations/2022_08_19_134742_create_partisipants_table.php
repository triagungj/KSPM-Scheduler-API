<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartisipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partisipants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->uuid('jabatan_id');
            $table->string('name');
            $table->string('member_id');
            $table->string('phone_number');
            $table->string('avatar_url')->nullable();
            $table->foreign('jabatan_id')
                ->references('id')->on('jabatans')->onDelete('cascade')->constrained('jabatans');
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
        Schema::dropIfExists('partisipants');
    }
}
