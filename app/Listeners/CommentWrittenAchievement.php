<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\CommentWritten;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Models\UserAchievements;
use App\Models\AchievementsList;

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
        $commentsWrittenByUser = count($user->comments()->get());
        $commentWrittenAchievements = AchievementsList::where('type', 'Comment')->get();
        $achievementIds = [];
        foreach ($commentWrittenAchievements as  $value) {
            $achievementIds[] = $value->id;
        }
        $getuserAchievements = UserAchievements::select('achievement_id')
                                            ->where('user_id', $user->id)
                                            ->whereIn('achievement_id', $achivementIds)
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
