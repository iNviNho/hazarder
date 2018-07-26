<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewTicketsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_tickets', function (Blueprint $table) {

            $table->increments('id');

            $table->string('status')->default("approved"); //  approved, canceled, bet, betanddone
            $table->string("external_ticket_id")->nullable();

            $table->unsignedInteger("user_id");
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedInteger("ticket_id");
            $table->foreign('ticket_id')->references('id')->on('tickets');

            $table->timestamps();
        });

        Schema::table("tickets", function(Blueprint $table) {
            $table->dropColumn("external_ticket_id");
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
