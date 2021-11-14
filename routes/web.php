<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AchievementsController;
use App\Models\User;
use App\Events\LessonWatched;
use App\Events\CommentWritten;
use App\Models\Lesson;
use App\Models\Comment;

Route::get('/users/{user}/achievements', [AchievementsController::class, 'index']);
Route::get('/lessonWatched', function() {
    $user = User::find(1);
    event(new LessonWatched(new Lesson, $user));
});
Route::get('/commentWritten', function() {
    $comment = Comment::find(1);
    event(new CommentWritten($comment));
});