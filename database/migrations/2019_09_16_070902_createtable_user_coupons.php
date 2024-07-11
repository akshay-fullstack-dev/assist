<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatetableUserCoupons extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('user_coupons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->comment('Id of user who can use this coupon');
            $table->string('name', 100);
            $table->string('code', 50);
            $table->string('type', 50);
            $table->double('discount', 10, 2)->nullable();
            $table->double('minAmount', 10, 2)->nullable()->comment('Min amount for coupon to be used');
            $table->unsignedBigInteger('maxTotalUse')->nullable()->comment('Max number of times the coupon can be used in total');
            $table->unsignedBigInteger('totalUsed')->nullable()->comment('Coupon used how many time');
            $table->enum('status', ['0', '1'])->default('0')->comment('Enabled or disabled');
            $table->dateTimeTz('startDateTime');
            $table->dateTimeTz('endDateTime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }

}
