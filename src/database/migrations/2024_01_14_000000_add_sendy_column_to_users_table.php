<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendyColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = config('sendy.target_table', 'users');
        
        Schema::table($table, function (Blueprint $table) {
            $table->boolean('sent_to_sendy')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table = config('sendy.target_table', 'users');
        
        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('sent_to_sendy');
        });
    }
}
