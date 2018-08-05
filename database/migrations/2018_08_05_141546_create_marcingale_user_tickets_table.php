<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarcingaleUserTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marcingale_user_tickets', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger("user_ticket_id");
            $table->foreign('user_ticket_id')->references('id')->on('user_tickets');

            $table->unsignedInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users');

            $table->integer("level"); // 1,2,3,4 ...
            $table->integer("round"); // 1,2,3,4 ...
            // bet = is currently bet
            // needy = game lost so new marcingale user ticket needed
            // success = successful marcingale round
            // fail = failed marcingale round because of user max marcingale level set
            $table->string("status");

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
        Schema::dropIfExists('marcingale_tickets');
    }
}
