<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CouponHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('coupon_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('Id of user who can use this coupon');
            $table->integer('coupon_id')->comment('applied coupon id');
            $table->integer('booking_id')->nullable();
            $table->string('coupon_code', 50)->nullable;
            $table->double('discount', 10, 2)->nullable();
            $table->string('description')->nullable();
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
        //
    }
}
