<?php

namespace App\Listeners;

use App\Events\LessonWatched;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use App\Models\UserAchievements;
use App\Models\User;
use App\Models\AchievementsList;
use App\Events\AchievementUnlocked;

class LessonWatchedAchievement
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LessonWatched  $event
     * @return void
     */
    public function handle(LessonWatched $event)
    {
        $user = $event->user;
        $lesson = $event->lesson;
        $lessonsWatchedByUser = count($user->watched()->get());
        $lessonWatchedAchievements = AchievementsList::where('type', 'Lesson')->get();
        $achivementIds = [];
        foreach ($lessonWatchedAchievements as  $value) {
            $achivementIds[] = $value->id;
        }

        $getuserAchievements = UserAchievements::select('achievement_id')
                                            ->where('user_id', $user->id)
                                            ->whereIn('achievement_id', $achivementIds)
                                            ->get();

        $unlockedAchievements = [];
        foreach ($getuserAchievements as $value) {
            $unlockedAchievements[] = $value->achievement_id;
        }
        foreach ($lessonWatchedAchievements as $achievement) {
            if ($achievement->count === $lessonsWatchedByUser && !in_array($achievement->id, $unlockedAchievements)) {
                // Unlock Achievement yay
                $inserted = UserAchievements::create([
                    'achievement_id' => $achievement->id,
                    'user_id' => $user->id
                ]);
                $unlockedAchievements[] = $inserted->achievement_id;
                event(new AchievementUnlocked($achievement->name, $user));
            }
        }
    }
}
