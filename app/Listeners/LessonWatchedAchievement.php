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
use App\Models\BadgeList;
use App\Models\UserBadge;
use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;

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
        /**
         * Since the beginner badge requires 0 achievements
         * and need to happen before the a achievement is unlocked.
         * doing a check here.
         * Alternatively we could trigger a event when a user registers for a course in this scenario
         */
        $currentAchievements = count($user->achievements);
        $userBadges = $user->badges()->get()->toArray();
        $userBadgeIds = array_map(fn($value) => $value['badge_id'], $userBadges);
        $badgeList = BadgeList::orderBy('order', 'asc')->get();
        foreach ($badgeList as $badge) {
            if (!in_array($badge->id, $userBadgeIds) && $badge->count === $currentAchievements) {
            $inserted = UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id
                ]);
                event(new BadgeUnlocked($badge->name, $user));
                break;
            }
        }

        $lesson = $event->lesson;
        $lessonsWatchedByUser = count($user->watched);
        $lessonWatchedAchievements = AchievementsList::select('id', 'name', 'count')->where('type', 'Lesson')->get();
        $achievementIds = [];
        foreach ($lessonWatchedAchievements as  $value) {
            $achievementIds[] = $value->id;
        }
        $unlockedAchievements = $user->achievements()->select('achievement_id')
                                        ->whereIn('achievement_id', $achievementIds)
                                        ->get()->toArray();
        $unlockedAchievementIds = array_map(fn($value) => $value['achievement_id'], $unlockedAchievements);
        foreach ($lessonWatchedAchievements as $key => $achievement) {
            if ($achievement->count === $lessonsWatchedByUser && !in_array($achievement->id, $unlockedAchievementIds)) {
                // Unlock Achievement yay
                $inserted = UserAchievements::create([
                    'achievement_id' => $achievement->id,
                    'user_id' => $user->id
                ]);
                event(new AchievementUnlocked($achievement->name, $user));
            }
        }
    }
}
