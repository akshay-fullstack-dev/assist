<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserReferalColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('users', function($table) {
            // $table->dropColumn('referal');
            $table->dropColumn('refered_by');
            $table->string('refferal')->after('status')->comment('My refferal code for other persons');
            $table->string('reffer_code')->nullable()->comment('Referal code of person who reffer me');
            
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
