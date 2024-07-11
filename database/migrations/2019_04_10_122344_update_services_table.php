<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function($table) {
            $table->dropColumn('hourly_price');
            $table->dropColumn('fixed_price');
            $table->double('price', 16, 2)->nullable()->after('description');
            $table->string('price_type')->nullable()->after('price');
            
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
