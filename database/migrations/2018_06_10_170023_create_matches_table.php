<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->increments('id');

            $table->string("category");
            $table->string("name");
            $table->string("type");

            $table->string("teama")->nullable();
            $table->string("teamb")->nullable();

            $table->string("a", 10)->nullable();
            $table->string("b", 10)->nullable();
            $table->string("c", 10)->nullable();
            $table->string("d", 10)->nullable();
            $table->string("e", 10)->nullable();
            $table->dateTime("date_of_game");

            $table->string("unique_name");


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
        Schema::dropIfExists('matches');
    }
}
