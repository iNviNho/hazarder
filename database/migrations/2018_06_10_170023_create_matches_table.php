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
            $table->string("unique_id");
            $table->string("type")->nullable();

            $table->string("teama")->nullable();
            $table->string("teamb")->nullable();

            $table->timestamp("date_of_game"); // should be dateatime

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
