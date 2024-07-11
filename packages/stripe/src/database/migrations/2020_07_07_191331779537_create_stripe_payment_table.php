<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStripePaymentTable extends Migration
{
    public function up()
    {
        Schema::create('stripe_payment_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payment_intent_id');
            $table->string('charge_id');
            $table->string('user_stripe_id');
            $table->string('card_id');
            $table->string('order_id');
            $table->string('paid_to')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stripe_payment_records');
    }
}
