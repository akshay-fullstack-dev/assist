<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookedEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booked_equipments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_id')->unsigned()->index();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->integer('equipment_id')->unsigned()->index();
            $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
            $table->string('equipment_name')->nullable();
            $table->float('price', 11, 2)->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booked_equipment');
    }
}
