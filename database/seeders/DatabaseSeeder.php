<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\User;
use App\Models\Comment;
use App\Models\UserAchievements;
use Illuminate\Database\Seeder;
use Database\Seeders\AchievementListSeeder;
use Database\Seeders\UserLessonSeeder;
use Database\Seeders\BadgeListSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()
        ->count(3)
        ->create();

        $lessons = Lesson::factory()
        ->count(10)
        ->create();

        $comments = Comment::factory()
        ->count(1)
        ->create();

        $this->call([
            AchievementListSeeder::class,
            BadgeListSeeder::class
        ]);
    }
}
