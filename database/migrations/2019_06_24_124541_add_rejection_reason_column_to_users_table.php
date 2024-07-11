<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRejectionReasonColumnToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['0', '1', '2', '3'])->after('online')->comment('0 => Inactive, 1 => Active, 2 => Pending, 3 => Rejected');
            $table->text('rejection_reason')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'rejection_reason']);
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['0', '1'])->after('online');
        });
    }
}
