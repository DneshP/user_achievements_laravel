<?php

namespace App\Providers;

use App\Events\LessonWatched;
use App\Events\CommentWritten;
use App\Events\AchievementUnlocked;
use App\Events\BadgeUnlocked;
use App\Listeners\LessonWatchedAchievement;
use App\Listeners\CommentWrittenAchievement;
use App\Listeners\AchievementUnlockedNotification;
use App\Listeners\BadgeUnlockedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CommentWritten::class => [
            CommentWrittenAchievement::class
        ],
        LessonWatched::class => [
            LessonWatchedAchievement::class
        ],
        AchievementUnlocked::class => [
            AchievementUnlockedNotification::class
        ],
        BadgeUnlocked::class => [
            BadgeUnlockedNotification::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
