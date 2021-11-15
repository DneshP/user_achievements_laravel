<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserLessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('lesson_user')
            ->insert([
                [
                    'user_id' => 3,
                    'lesson_id' => 1,
                    'watched' => true
                ]
            ]);
    }
}
