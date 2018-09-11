<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Altermarcingaleusertickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('marcingale_user_tickets', function (Blueprint $table) {
            $table->unsignedInteger("marcingale_user_round_id")->nullable();
            $table->foreign('marcingale_user_round_id')->references('id')->on('marcingale_user_rounds');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
