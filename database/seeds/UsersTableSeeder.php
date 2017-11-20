<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('users')->delete();

        \DB::table('users')->insert(array(
            0 => array(
                'id' => 0,
                'name' => 'test',
                'email' => '12345@qq.com',
                'password' => '1234567',
                'created_at' => '2017-11-20 17:45:26',
                'updated_at' => '2017-11-20 17:45:26'
            )
        ));
    }
}
