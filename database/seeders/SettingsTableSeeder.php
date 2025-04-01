<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'feature_name' => 'orders',
            'is_enabled'   => 0,
            'updated_at'   => Carbon::now(), // Sets the current timestamp
        ]);
    }
}
