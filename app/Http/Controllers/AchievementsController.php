<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UserAchievements;
use App\Models\AchievementsList;
use App\Models\UserBadge;
use App\Models\BadgeList;
use App\Events\BadgeUnlocked;

class AchievementsController extends Controller
{
    private $user;

    public function index(User $user)
    {
        $this->user = $user;

        return response()->json([
            'unlocked_achievements' => $this->getUnlockedAchievementNames(),
            'next_available_achievements' => $this->getNextAvailableAchievements(),
            'current_badge' => $this->displayCurrentBadgeName(),
            'next_badge' => $this->displayNextBadgeName(),
            'remaing_to_unlock_next_badge' => $this->getRemainingAchievements()
        ]);
    }

    /**
     * Fetch UnlockedAchievements
     */
    private function getUnlockedAchievements()
    {
        return $this->user->achievements()
                            ->leftjoin('achievement_list as al', 'al.id', '=', 'user_achievements.achievement_id')
                            ->get();
    }

    /**
     * Fetch Available Achievements
     */
    public function availableAchievements()
    {
        $achievementGroup = [];
        $achievements = AchievementsList::select('id', 'order', 'name', 'type')->orderBy('order')->get();
        foreach ($achievements as $achievement) {
            $achievementGroup[$achievement->type][] = $achievement;
        }
        return $achievementGroup;
    }

    /**
     * Fetch Unlocked Badges
     */
    public function getUserBadges()
    {
        return $this->user->badges()->select('user_badges.id', 'user_badges.user_id', 'user_badges.badge_id', 'bl.order', 'bl.name')
                                                ->leftjoin('badge_lists as bl', 'bl.id', '=', 'user_badges.badge_id')
                                                ->where('user_id', $this->user->id)
                                                ->orderBy('bl.order')
                                                ->get();
    }

    /**
     * Fetches Available Badges
     */
    public function availableBadges()
    {
        return BadgeList::select('id', 'name', 'count', 'order')->orderBy('count')->get();
    }

    /**
     * Achievements Unlocked by the user
     */
    public function getUnlockedAchievementNames()
    {
        $achievements = array_map(fn($value) => $value['name'], $this->getUnlockedAchievements()->toArray());
        return $achievements;
    }

    /**
     * Next Available Achievements.
     */
    public function getNextAvailableAchievements()
    {
        $availableAchievements = $this->availableAchievements();
        $currentAchievement = [];
        $nextAchievements = [];
        /**
         * currentAchievements based on categories
         */
        foreach ($this->getUnlockedAchievements() as $achievement) {
            $value = ['type' => $achievement->type, 'order' => $achievement->order];
            $currentAchievement[$achievement->type] = (object) $value;
        }
        /**
         * If the category does not exist in the currentAchivement
         * the first available achievement is added
         */
        foreach ($availableAchievements as $key => $value) {
            if (!array_key_exists($key, $currentAchievement)) {
                $nextAchievements[] =  $value[0]->name;
            }
        }
        foreach ($currentAchievement as $key => $value) {
            $achievementList = $availableAchievements[$key];
            foreach ($achievementList as $achievement) {
                //since it is a sorted list, exit when found matching
                if ($value->order < $achievement->order) {
                    $nextAchievements[] = $achievement->name;
                    break; 
                }
            }
        }
        return $nextAchievements;
    }

    /**
     * Get Current User Badge
     */
    public function getCurrentUserBadge()
    {
        $userBadges = $this->getUserBadges();
        if (count($userBadges) === 0) {
            $currentAchievement = count($this->user->achievements);
            foreach ($this->availableBadges() as $badge) {
                if ($currentAchievement === $badge->count) {
                    $this->updateUserBadge($badge);
                    return $badge;
                }
            }

        } else {
            $userBadges = $this->getUserBadges();
            return $userBadges[count($userBadges)-1] ?? false;
        }
    }

    /**
     * Display Current Badge Name
     */
    public function displayCurrentBadgeName()
    {
        $current = $this->getCurrentUserBadge();
        if ($current) {
            return $current->name;
        }
    }

    /**
     * Fetch User Next Badge
     */
    public function getUserNextBadge()
    {
        $userBadges = $this->getUserBadges();
        if (count($userBadges) > 0) {
            $currentOrder = $userBadges[count($userBadges)-1]->order;
            foreach ($this->availableBadges() as $badge) {
                if ($currentOrder < $badge->order) {
                    return $badge;
                }
            }
            return false;
        }
    }

    /**
     * Get User Next Badge Name
     */
    public function displayNextBadgeName()
    {
        $next = $this->getUserNextBadge();
        if ($next) {
            return $next->name;
        }
    }

    /**
     * Get Remaining Achievements To Unlock Next Badge
     */
    public function getRemainingAchievements()
    {
        $currentAchievements = count($this->getUnlockedAchievements());
        $nextBadgeAchievements = $this->getUserNextBadge();
        if ($nextBadgeAchievements) {
            return $nextBadgeAchievements->count - $currentAchievements;
        }
    }

    /**
     * Update User Achieved Badge
     * @param object $badge
     */
    private function updateUserBadge($badge)
    {
        /**
         * To unlock the beginner badge in case the user vists the stat page
         * The Achievement event is not triggered yet 
         */
        try {
            $inserted = UserBadge::create([
                'user_id' => $this->user->id,
                'badge_id' => $badge->id
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'error' => 'Please Contact Support' . $th
            ]);
        }
        event(new BadgeUnlocked($badge->name, $this->user));
        $this->unlockedBadges = false;
        $this->getUserBadges();
    }
}
