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
        $collection = $event->comment->user()->get();
        $user;
        foreach ($collection as $value) {
            $user = $value;
        }
        /**
         * Since the beginner badge requires 0 achievements
         * and need to happen before the a achievement is unlocked.
         * doing a check here.
         * Alternatively we could trigger a event when a user registers for a course in this scenario
         */
        $currentAchievements = count($user->unlockedAchievements()->get());
        $userBadges = $user->userBadges()->where('user_id', '=', $user->id)->get();
        $BadgeList = new BadgeList();
        foreach ($BadgeList->badgeList()->get() as $badge) {
            if (count($userBadges) === 0 && $badge->count === $currentAchievements ) {
                $inserted = UserBadge::create([
                    'user_id' => $user->id,
                    'badge_id' => $badge->id
                ]);
                event(new BadgeUnlocked($badge->name, $user));
                break;
            }
        }
        $commentsWrittenByUser = count($user->comments()->get());
        $commentWrittenAchievements = AchievementsList::where('type', 'Comment')->get();
        $achievementIds = [];
        foreach ($commentWrittenAchievements as  $value) {
            $achievementIds[] = $value->id;
        }
        $getuserAchievements = UserAchievements::select('achievement_id')
                                            ->where('user_id', $user->id)
                                            ->whereIn('achievement_id', $achievementIds)
                                            ->get();
        $unlockedAchievements = [];
        foreach ($getuserAchievements as $value) {
            $unlockedAchievements[] = $value->achievement_id;
        }
        foreach ($commentWrittenAchievements as $achievement) {
            if ($achievement->count === $commentsWrittenByUser && !in_array($achievement->id, $unlockedAchievements)) {
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
