<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');

            // prepared, approved, canceled, bet
            $table->string("status")->default("prepared"); // prepared, canceled
            $table->string("result")->default("tobeplayed"); // tobeplayed, canceled, a, b, c, ab, bc
            $table->string("game_type"); // oneten, onetwenty, marcingale

            $table->string("bet_option");
            $table->string("bet_amount");
            $table->string("bet_rate");
            $table->string("bet_possible_win");
            $table->string("bet_possible_clear_win");

            $table->boolean("bet_win")->default(0); // 0, 1

            $table->unsignedInteger("match_id");
            $table->foreign('match_id')->references('id')->on('matches');

            $table->unsignedInteger("matchbet_id");
            $table->foreign('matchbet_id')->references('id')->on('matchbets');

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
        Schema::dropIfExists('tickets');
    }
}
