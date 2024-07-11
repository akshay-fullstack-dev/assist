<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdditionPeramtersForTheUserPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropColumn('expiry_date');
        });
        
        Schema::table('user_packages', function (Blueprint $table) {
            $table->string('platform')->comment('1:- android ; 0:- IOS')->after('package_name');
            $table->string('purchase_token')->after('platform');
            $table->string('product_id')->after('purchase_token')->index();
            $table->string('order_id')->after('product_id');
            $table->string('developer_payload')->after('order_id')->nullable();
            $table->string('status')->after('developer_payload')->comment('0:- not-active ; 1:- active')->default('0');
            $table->date('transaction_date')->after('status');
            $table->dateTime('expiry_date')->after('transaction_date')->nullable();

            // drop the previous columns
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            // remove the new added columns
            $table->dropColumn('platform');
            $table->dropColumn('purchase_token');
            $table->dropColumn('product_id');
            $table->dropColumn('order_id');
            $table->dropColumn('auto_renewing');
            $table->dropColumn('developer_payload');
            $table->dropColumn('transaction_date');

            // add the prevois clolumns
            $table->double('price', 11, 6);
            $table->date('expiry_date')->change();
        });
    }
}
