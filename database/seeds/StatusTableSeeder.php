<?php

use Illuminate\Database\Seeder;

class StatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = DB::table('status')->get();
        $count = count($status);

        if ($count==0)
        {
            \DB::table('status')->insert(array(
                1 =>
                array(
                    'status_type' => 'vendor',
                    'label' => 'Order Placed',
                ),
                2 =>
                array(
                    'status_type' => 'vendor',
                    'label' => 'Assist Expert Assigned',
                ),
                3 =>
                array(
                    'status_type' => 'vendor',
                    'label' => 'Assist Expert on the way',
                ),
                4 =>
                array(
                    'status_type' => 'vendor',
                    'label' => 'Order In Progress',
                ),
                5 =>
                array(
                    'status_type' => 'vendor',
                    'label' => 'Order Completed',
                ),
                6 =>
                array(
                    'status_type' => 'booking',
                    'label' => 'Cancelled',
                ),
                7 =>
                array(
                    'status_type' => 'booking',
                    'label' => 'Refund',
                ),
                8 =>
                array(
                    'status_type' => 'booking',
                    'label' => 'Pending',
                ),
                9 =>
                array(
                    'status_type' => 'booking',
                    'label' => 'Completed',
                ),
                10 =>
                array(
                    'status_type' => 'booking',
                    'label' => 'Open',
                ),
                11 =>
                array(
                    'status_type' => 'payment',
                    'label' => 'Vendor Arrived',
                ),
                12 =>
                array(
                    'status_type' => 'payment',
                    'label' => 'Failed',
                ),
                13 =>
                array(
                    'status_type' => 'payment',
                    'label' => 'Pending',
                ),
                14 =>
                array(
                    'status_type' => 'booking',
                    'label' => 'On Hold',
                )
            ));
        }
    }
}
