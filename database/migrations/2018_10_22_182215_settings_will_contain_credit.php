<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SettingsWillContainCredit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string("credit")->default("0");
            $table->timestamp("credit_update_time")->useCurrent()->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("credit");
            $table->dropColumn("credit_update_time");
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
