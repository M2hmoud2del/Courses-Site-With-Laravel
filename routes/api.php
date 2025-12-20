<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/student/dashboard', [App\Http\Controllers\StudentController::class, 'dashboard']);
    Route::get('/courses', [App\Http\Controllers\StudentController::class, 'browse']);
    Route::get('/student/enrollments', [App\Http\Controllers\StudentController::class, 'enrolled']);
    Route::post('/courses/{course}/join', [App\Http\Controllers\StudentController::class, 'requestJoin']);
    Route::delete('/student/requests/{request}', [App\Http\Controllers\StudentController::class, 'cancelJoinRequest']);
    Route::get('/student/notifications', [App\Http\Controllers\StudentController::class, 'notifications']);
    Route::patch('/student/notifications/{notification}/read', [App\Http\Controllers\StudentController::class, 'markNotificationAsRead']);
    
    Route::get('/categories', [App\Http\Controllers\StudentController::class, 'getCategories']);
});
