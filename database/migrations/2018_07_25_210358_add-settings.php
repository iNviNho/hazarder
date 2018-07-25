<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {

            $table->increments('id');

            $table->string('username')->default("-");
            $table->string('password')->default("-");

            $table->string('max_oneten')->default("-");
            $table->string('max_onetwenty')->default("-");
            $table->string('max_marcingale')->default("-");
            $table->string('max_opposite')->default("-");

            $table->string('bet_amount')->default("-");

            $table->unsignedInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users');

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
        Schema::dropIfExists('settings');
    }
}
