<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangesInServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'standard_price'))
                $table->dropColumn('standard_price');
            if (Schema::hasColumn('services', 'pro_price'))
                $table->dropColumn('pro_price');
            if (Schema::hasColumn('services', 'price_type'))
                $table->dropColumn('price_type');
            if (Schema::hasColumn('services', 'price'))
                $table->dropColumn('price');
            if (Schema::hasColumn('services', 'additional_service_price'))
                $table->dropColumn('additional_service_price');
            $table->string('service_question', 500)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->double('standard_price');
            $table->double('pro_price');
            $table->double('price');
            $table->integer('price_type');
            $table->dropColumn(['service_question']);
        });
    }
}
