<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BadgeListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('badge_lists')->insert([
            [
                'name' => 'Beginner',
                'count' => 0,
                'order' => 1
            ],
            [
                'name' => 'Intermediate',
                'count' => 4,
                'order' => 2
            ],
            [
                'name' => 'Advanced',
                'count' => 8,
                'order' => 3
            ],
            [
                'name' => 'Master',
                'count' => 10,
                'order' => 4
            ]
        ]);
    }
}
