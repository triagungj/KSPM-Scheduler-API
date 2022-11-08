<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartisipansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partisipans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->uuid('jabatan_id')->nullable();
            $table->string('name');
            $table->string('member_id');
            $table->string('phone_number');
            $table->string('avatar_url')->nullable();
            $table->foreign('jabatan_id')
                ->references('id')->on('jabatans')->onDelete('set null')->constrained('jabatans');
            $table->foreign('username')
                ->references('username')->on('users')->onDelete('cascade')->onUpdate('cascade')->constrained('users');
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
        Schema::dropIfExists('partisipans');
    }
}
