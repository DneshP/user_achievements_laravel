<?php

namespace App\Listeners;

use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
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
        $unlockedAchievements = count($user->achievements);
        $unlockedBadges = array_map(fn($value) => $value['badge_id'], $user->badges()->toArray());
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
                event(new BadgeUnlocked($badge->name, $user));
            }
        }
    }
}
