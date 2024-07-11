<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesInUserAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->dropColumn('name');
            $table->dropColumn('house_no');
            $table->dropColumn('landmark');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_addresses', function (Blueprint $table) {
            $table->string('gender', 255);
            $table->string('name', 255);
            $table->string('house_no', 255);
            $table->string('landmark', 255);
        });
    }
}
