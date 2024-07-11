<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableReviews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('review', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('vender_id');
            $table->integer('booking_id');
            $table->float('rating');
            $table->enum('is_like', ['0', '1'])->default('1');
            $table->integer('review_submitted_by')->comment('User who has submitted review');
            $table->integer('review_submitted_to')->comment('User to whom review is submitted');
            $table->integer('review_type')->comment('What is type of review with for booking or any other review');
            $table->text('feedback_message')->nullable();
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
