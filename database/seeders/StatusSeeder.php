<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('status')->insert([
            [
                'name' => '101 pending',
                'table_name' => 'booking'
            ],
            [
                'name' => '102 approve',
                'table_name' => 'booking'
            ],
            [
                'name' => '103 complete',
                'table_name' => 'booking'
            ],
            [
                'name' => '201 reject',
                'table_name' => 'booking_item'
            ],
            [
                'name' => '202 pending',
                'table_name' => 'booking_item'
            ],
            [
                'name' => '203 approve',
                'table_name' => 'booking_item'
            ],
            [
                'name' => '204 lending',
                'table_name' => 'booking_item'
            ],
            [
                'name' => '205 returned',
                'table_name' => 'booking_item'
            ],
            [
                'name' => '301 pending',
                'table_name' => 'store_members'
            ],
            [
                'name' => '302 approve',
                'table_name' => 'store_members'
            ],
        ]);
    }
}
