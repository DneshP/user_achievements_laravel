<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AchievementListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('achievement_list')->insert([
            [
                'name' => 'First Lesson Watched',
                'count' => 1,
                'order' => 1,   
                'type' => 'Lesson'
            ],
            [
                'name' => '5 Lessons Watched',
                'count' => 5,
                'order' => 2,   
                'type' => 'Lesson'
            ],
            [
                'name' => '10 Lessons Watched',
                'count' => 10,
                'order' => 3,   
                'type' => 'Lesson'
            ],
            [
                'name' => '25 Lessons Watched',
                'count' => 25,
                'order' => 4,   
                'type' => 'Lesson'
            ],
            [
                'name' => '50 Lessons Watched',
                'count' => 50,
                'order' => 5,   
                'type' => 'Lesson'
            ],
            [
                'name' => 'First Comment Written',
                'count' => 1,
                'order' => 1,   
                'type' => 'Comment'
            ],
            [
                'name' => '3 Comments Written',
                'count' => 3,
                'order' => 2,   
                'type' => 'Comment'
            ],
            [
                'name' => '5 Comments Writtend',
                'count' => 5,
                'order' => 3,   
                'type' => 'Comment'
            ],
            [
                'name' => '10 Comments Writtend',
                'count' => 10,
                'order' => 4,   
                'type' => 'Comment'
            ],
            [
                'name' => '20 Comments Writtend',
                'count' => 20,
                'order' => 5,   
                'type' => 'Comment'
            ]
        ]);
    }
}
