<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Mmigratebetstuff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("tickets", function(Blueprint $table) {
            $table->dropColumn("bet_option");
            $table->dropColumn("bet_amount");
            $table->dropColumn("bet_rate");
            $table->dropColumn("bet_possible_win");
            $table->dropColumn("bet_possible_clear_win");
            $table->dropColumn("bet_win");
        });

        Schema::table("user_tickets", function(Blueprint $table) {
            $table->string("bet_option");
            $table->string("bet_amount");
            $table->string("bet_rate");
            $table->string("bet_possible_win");
            $table->string("bet_possible_clear_win");
            $table->string("bet_win")->default(0);
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
