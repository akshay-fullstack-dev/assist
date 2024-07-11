<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableToKeepRecordOfCancelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cancelled_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vender_id')->comment('id of vender who cancelled this booking');
            $table->integer('booking_id')->comment('Booking id which is cancelled by vender');
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
        Schema::dropIfExists('cancelled_bookings');
    }
}
