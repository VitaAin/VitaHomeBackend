<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('roles')->delete();

        \DB::table('roles')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'name' => 'admin',
                    'display_name' => '阁老',
                    'description' => '阁老',
                    'created_at' => '2017-04-26 16:00:00',
                    'updated_at' => '2017-05-24 16:20:48',
                ),
            1 =>
                array(
                    'id' => 2,
                    'name' => 'VIP',
                    'display_name' => '宗师',
                    'description' => '宗师',
                    'created_at' => '2017-04-26 16:00:00',
                    'updated_at' => '2017-05-24 16:20:48',
                ),
            2 =>
                array(
                    'id' => 3,
                    'name' => 'owner',
                    'display_name' => '侠士',
                    'description' => '侠士',
                    'created_at' => '2017-04-26 16:00:00',
                    'updated_at' => '2017-05-24 16:20:48',
                ),
        ));
    }
}
