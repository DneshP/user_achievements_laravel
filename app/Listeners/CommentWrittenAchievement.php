<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\CommentWritten;
use App\Events\BadgeUnlocked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\UserAchievements;
use App\Models\AchievementsList;
use App\Models\BadgeList;
use App\Models\UserBadge;

class CommentWrittenAchievement
{
    use SerializesModels;

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
     * @param  CommentWritten  $event
     * @return void
     */
    public function handle(CommentWritten $event)
    {
        $user = $event->comment->user;
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

        $commentsWrittenByUser = count($user->comments);
        $commentWrittenAchievements = AchievementsList::select('id', 'name', 'count')
                                                ->where('type', 'Comment')->get();
        $achievementIds = [];
        foreach ($commentWrittenAchievements as  $value) {
            $achievementIds[] = $value->id;
        }
        $unlockedAchievements = $user->achievements()->select('achievement_id')
                                                ->whereIn('achievement_id', $achievementIds)
                                                ->get()->toArray();
        $unlockedAchievementIds = array_map(fn($value) => $value['achievement_id'], $unlockedAchievements);
        foreach ($commentWrittenAchievements as $key => $achievement) {
            if ($achievement->count === $commentsWrittenByUser && !in_array($achievement->id, $unlockedAchievementIds)) {
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
