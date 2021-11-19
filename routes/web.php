<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AchievementsController;
use App\Models\User;
use App\Events\LessonWatched;
use App\Events\CommentWritten;
use App\Models\Lesson;
use App\Models\Comment;

Route::get('/users/{user}/achievements', [AchievementsController::class, 'index']);
Route::get('/lessonWatched/{userId}', function($userId) {
    $user = User::findOrFail($userId);
    event(new LessonWatched(new Lesson, $user));
    });
Route::get('/commentWritten/{userId}', function($userId) {
    $user = User::findOrFail($userId);
    $comment = $user->comments()->latest('created_at')->first();
    $comment = Comment::findOrFail($comment->id??0);
    event(new CommentWritten($comment));
});