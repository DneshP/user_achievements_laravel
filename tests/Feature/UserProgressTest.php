<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use App\Models\User;
use App\Models\UserLesson;
use App\Models\AchievementsList;
use App\Models\Comment;
use App\Models\Lesson;
use App\Models\BadgeList;
use App\Events\LessonWatched;
use App\Events\AchievementUnlocked;
use App\Events\CommentWritten;
use App\Events\BadgeUnlocked;
use App\Listeners\AchievementUnlockedNotification;
use Database\Seeders\UserLessonSeeder;

class UserProgressTest extends TestCase
{
    private $availableBadges;

    /**
     * Beginner Badge Unlocked
     */
    public function test_beginner_badge_unlocked()
    {
        $this->migrateAndSeed();
        $user = User::factory()->create();
        
        $response = $this->get("/users/{$user->id}/achievements");
        $data = $response->getData();

        $response->assertStatus(200);

        $this->assertEquals($data->unlocked_achievements, []);
        $this->assertEquals(count($data->next_available_achievements), 2);
        $this->assertContains('First Comment Written', $data->next_available_achievements);
        $this->assertContains('First Lesson Watched', $data->next_available_achievements);
        $this->validateBadgeStatus($data, 0);
    }

    /**
     * lesson watched by user
     */
    public function test_lesson_watched()
    {
        $this->migrateAndSeed();
        $user = User::factory()->create();
        UserLesson::factory()->create([
            'user_id' =>$user->id,
            'lesson_id' => 1,
            'watched' => 1
        ]);

        $this->get("/lessonWatched/{$user->id}");
        $response = $this->get("/users/{$user->id}/achievements");
        $data = $response->getData();
        
        $response->assertStatus(200);

        $this->assertCount(
            1,
            $data->unlocked_achievements, "Unlocked Achievements should have 1 achievement"
        );
        $this->assertCount(
            2,
            $data->next_available_achievements, "Next Achievements should have 2 achievement"
        );
        $this->assertContains('First Comment Written', $data->next_available_achievements);
        $this->assertContains('5 Lessons Watched', $data->next_available_achievements);
        $this->validateBadgeStatus($data, 1);
    }

    /**
     * Comment Written by user
     */
    public function test_comment_written()
    {
        $this->migrateAndSeed();
        $user = User::factory()->create();
        $inserted = Comment::factory()->create([
            'body' => 'test',
            'user_id' => $user->id
        ]);

        $this->get("/commentWritten/{$inserted->id}");
        $response = $this->get("/users/{$user->id}/achievements");
        $data = $response->getData();
        $response->assertStatus(200);
        $this->assertContains('3 Comments Written', $data->next_available_achievements);
        $this->assertCount(
                1,
                $data->unlocked_achievements, "Unlocked Achievements should have 1 achievement"
            );
        $this->assertCount(
            2,
            $data->next_available_achievements, "Next Achievements should have 2 achievement"
        );
            $this->validateBadgeStatus($data, 1);
    }

    /**
     * Lessons watched by user
     * @todo to automate all scenarios
     */
    public function all_lessons_watched_achievements()
    {
        $this->migrateAndSeed();
        $user = User::factory()->create();
        // UserLesson::factory()->create([
        //         'user_id' =>$user->id,
        //         'lesson_id' => 1,
        //         'watched' => 1
        //     ]);
        $lessonWatchedAchievements = AchievementsList::where('type', 'Lesson')->get();
        foreach ($lessonWatchedAchievements as $key => $value) {
            $index = (int) $key;
            $lessons = (array_key_exists($index - 1,$lessonWatchedAchievements)) ? $value->count - $lessonWatchedAchievements[$index - 1]->count : $value->count;
            for ($i=0; $i < $lessons; $i++) { 
                UserLesson::factory()->create([
                'user_id' =>$user->id,
                'lesson_id' => 1,
                'watched' => 1
            ]);
            }
            // $this->get("/lessonWatched/{$user->id}");
            // $response = $this->get("/users/{$user->id}/achievements");
            // $data = $response->getData();
        }
     

  
    }

    /**
     * Test dispatched events
     * @todo AchievementUnlocked
     */
    public function test_events_dispatched()
    {
        $this->migrateAndSeed();
        $user = User::factory()->create();
    

        Event::fake();

        $this->get("/users/{$user->id}/achievements");
        Event::assertDispatched(BadgeUnlocked::class);

        UserLesson::factory()->create([
            'user_id' =>$user->id,
            'lesson_id' => 1,
            'watched' => 1
        ]);
    
        $this->get("/lessonWatched/{$user->id}");
        Event::assertDispatched(LessonWatched::class);

        $inserted = Comment::factory()->create([
            'body' => 'test',
            'user_id' => $user->id
        ]);
        $this->get("/commentWritten/{$inserted->id}");
        Event::assertDispatched(CommentWritten::class);

        Event::assertListening(
            AchievementUnlocked::class,
            AchievementUnlockedNotification::class
        );
    }

    /**
     * Validate Badge Status
     */
    private function validateBadgeStatus($data, $unlockedAchievements)
    {
        $badges = $this->availableBadges();
        foreach ($badges as $key => $badge) {
            if ($badge->count === $unlockedAchievements) {
                $this->assertEquals($data->current_badge, $badge->name);
                $this->assertEquals($data->next_badge, $badges[$key+1]->name ?? '');
                $this->assertEquals($data->remaing_to_unlock_next_badge, isset($badges[$key+1]) ? $badges[$key+1]->count - $unlockedAchievements : 0);
            }
        }
    }

      /**
     * Fetches Available Badges
     */
    private function availableBadges()
    {
        if (!$this->availableBadges) {
            $this->availableBadges = BadgeList::select('id', 'name', 'count', 'order')->orderBy('count')->get();
        }
        return $this->availableBadges;
    }
    /**
     * Database Reset
     */
    private function migrateAndSeed()
    {
        $this->artisan('migrate:fresh');
        $this->seed();
    }
}
