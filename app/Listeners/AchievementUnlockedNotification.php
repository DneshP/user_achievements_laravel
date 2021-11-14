<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use App\Models\UserBadge;
use App\Models\BadgeList;

class AchievementUnlockedNotification
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
     * @param  AchievementUnlocked  $event
     * @return void
     */
    public function handle(AchievementUnlocked $event)
    {
        $user = $event->user;
        $unlockedAchievements = count($user->unlockedAchievements()->where('user_id', $user->id)->get()->toArray());
        $getUserBadges = UserBadge::select('badge_id')
                                    ->where('user_id', $user->id)
                                    ->get();
        $unlockedBadges = [];
        foreach ($getUserBadges as $value) {
            $unlockedBadges[] = $value->badge_id;
        }
        $badges = BadgeList::select('id', 'name', 'count')
                    ->orderBy('order')
                    ->get();
        foreach ($badges as $badge) {
            if ($badge->count === $unlockedAchievements && !in_array($badge->id, $unlockedBadges)) {
                //Release a Badge woo
                $inserted = UserBadge::create([
                    'badge_id' => $badge->id,
                    'user_id' => $user->id
                ]);
                $unlockedBadges[] = $inserted->id;
            }
        }
    }
}
