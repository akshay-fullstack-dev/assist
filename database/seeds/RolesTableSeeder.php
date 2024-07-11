<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder {

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run() {


        \DB::table('roles')->delete();

        \DB::table('roles')->insert(array(
            1 =>
            array(
                'id' => 1,
                'name' => 'User',
                'slug' => 'user',
                'description' => 'userrole',
                'level' => 1,
                'created_at' => '2016-01-03 13:07:22',
                'updated_at' => '2016-01-03 13:07:22',
            ),
            2 =>
            array(
                'id' => 2,
                'name' => 'Vendor',
                'slug' => 'vendor',
                'description' => 'vendor',
                'level' => 2,
                'created_at' => '2016-04-25 07:16:31',
                'updated_at' => '2016-04-25 07:16:31',
            ),
            3 =>
            array(
                'id' => 3,
                'name' => 'Agency',
                'slug' => 'agency',
                'description' => 'agency',
                'level' => 3,
                'created_at' => '2019-08-22 07:16:31',
                'updated_at' => '2019-08-22 07:16:31',
            ),
        ));
    }

}
