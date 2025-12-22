<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Instructor\ContentController;
use App\Http\Controllers\InstructorController;

Route::get('/', function () {
    return view('welcome');
});



Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:ADMIN'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminController::class, 'users'])->name('index');          // admin.users.index
        Route::get('/create', [AdminController::class, 'createUser'])->name('create'); // admin.users.create
        Route::post('/store', [AdminController::class, 'storeUser'])->name('store'); // أضف هذا
        Route::get('/{id}', [AdminController::class, 'showUser'])->name('show');    // admin.users.show
        Route::get('/{id}/edit', [AdminController::class, 'editUser'])->name('edit'); // admin.users.edit
        Route::put('/{id}/update', [AdminController::class, 'updateUser'])->name('update'); // أضف هذا
        Route::delete('/{id}/delete', [AdminController::class, 'deleteUser'])->name('delete'); // أضف هذا
    });

    // Courses
    Route::prefix('courses')->name('courses.')->group(function () {
        Route::get('/', [AdminController::class, 'courses'])->name('index');
        Route::get('/{id}', [AdminController::class, 'showCourse'])->name('show');
        Route::get('/{id}/edit', [AdminController::class, 'editCourse'])->name('edit');
        Route::put('/{id}/update', [AdminController::class, 'updateCourse'])->name('update');
        Route::delete('/{id}/delete', [AdminController::class, 'deleteCourse'])->name('delete');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [AdminController::class, 'categories'])->name('index');
        Route::post('/store', [AdminController::class, 'storeCategory'])->name('store');
        Route::put('/{id}/update', [AdminController::class, 'updateCategory'])->name('update');
        Route::delete('/{id}/delete', [AdminController::class, 'deleteCategory'])->name('delete');
    });

    // Statistics
    Route::get('/statistics', [AdminController::class, 'statistics'])->name('statistics');
});


// Instructor Routes
Route::prefix('instructor')->name('instructor.')->middleware(['auth', 'role:INSTRUCTOR'])->group(function () {
    Route::get('/dashboard', [InstructorController::class, 'dashboard'])->name('dashboard');

    // Course management
    Route::get('/courses', [InstructorController::class, 'courses'])->name('courses.index');
    Route::get('/courses/create', [InstructorController::class, 'createCourse'])->name('courses.create');
    Route::post('/courses', [InstructorController::class, 'storeCourse'])->name('courses.store');
    Route::get('/courses/{id}', [InstructorController::class, 'showCourse'])->name('courses.show');
    // Add this route to the instructor routes
    Route::get('/courses/{id}/edit', [InstructorController::class, 'editCourse'])->name('courses.edit');
    Route::put('/courses/{id}', [InstructorController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/courses/{id}', [InstructorController::class, 'deleteCourse'])->name('courses.delete');

    // Student management
    Route::get('/courses/{courseId}/students', [InstructorController::class, 'students'])->name('courses.students');

    // Join requests
    Route::get('/join-requests', [InstructorController::class, 'joinRequests'])->name('join-requests.index');
    Route::post('/join-requests/{id}/approve', [InstructorController::class, 'approveJoinRequest'])->name('join-requests.approve');
    Route::post('/join-requests/{id}/reject', [InstructorController::class, 'rejectJoinRequest'])->name('join-requests.reject');

    // Enrollments
    Route::get('/enrollments', [InstructorController::class, 'enrollments'])->name('enrollments.index');
    Route::delete('/enrollments/{id}', [InstructorController::class, 'removeEnrollment'])->name('enrollments.remove');

    // Course analytics
    Route::get('/analytics', [InstructorController::class, 'analytics'])->name('analytics');

    // Add these to your instructor routes group
    Route::prefix('courses/{courseId}/content')->name('content.')->group(function () {
        Route::get('/', [ContentController::class, 'index'])->name('index');
        Route::get('/create', [ContentController::class, 'create'])->name('create');
        Route::post('/', [ContentController::class, 'store'])->name('store');
        Route::get('/{contentId}/edit', [ContentController::class, 'edit'])->name('edit');
        Route::put('/{contentId}', [ContentController::class, 'update'])->name('update');
        Route::delete('/{contentId}', [ContentController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [ContentController::class, 'reorder'])->name('reorder');
    });
});



Route::get('/dashboard', [StudentController::class, 'dashboard'])
    ->middleware(['auth', 'verified', 'role:STUDENT'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:STUDENT'])->prefix('student')->name('student.')->group(function () {
    Route::get('/browse', [StudentController::class, 'browse'])->name('browse');
    Route::get('/enrolled', [StudentController::class, 'enrolled'])->name('enrolled');
    Route::get('/courses/{course}', [StudentController::class, 'showCourse'])->name('courses.show');
    Route::post('/join/{course}', [StudentController::class, 'requestJoin'])->name('join');
    Route::delete('/requests/{request}', [StudentController::class, 'cancelJoinRequest'])->name('requests.cancel');
    Route::get('/notifications', [StudentController::class, 'notifications'])->name('notifications');
    Route::patch('/notifications/{id}/read', [StudentController::class, 'markNotificationAsRead'])->name('notifications.read');
    Route::post('/enroll/{course}', [StudentController::class, 'enroll'])->name('enroll');
    Route::post('/profile', [StudentController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [StudentController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/remove-picture', [ProfileController::class, 'removeProfilePicture'])->name('profile.remove-picture');
});

require __DIR__ . '/auth.php';
