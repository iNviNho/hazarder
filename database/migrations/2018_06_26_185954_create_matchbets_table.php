<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchbetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matchbets', function (Blueprint $table) {
            $table->increments('id');

            $table->string("name");
            $table->string("value");

            $table->string("datainfo");
            $table->string("dataodd");

            $table->unsignedInteger("match_id");
            $table->foreign('match_id')->references('id')->on('matches');

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
        Schema::dropIfExists('matchbets');
    }
}
