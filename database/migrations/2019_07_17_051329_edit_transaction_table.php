<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditTransactionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('credit');
            $table->integer('vender_id')->after('trans_id')->nullable();
            $table->integer('booking_id')->after('vender_id')->nullable();
            $table->double('vender_amount', 8, 2)->after('amount')->nullable();
            $table->double('admin_amount', 8, 2)->after('vender_amount')->nullable();
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
