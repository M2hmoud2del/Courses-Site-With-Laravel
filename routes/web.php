<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});



Route::prefix('admin')->name('admin.')->group(function () {
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
        Route::get('/create', [AdminController::class, 'createCourse'])->name('create');
        Route::post('/store', [AdminController::class, 'storeCourse'])->name('store');
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





Route::get('/dashboard', [StudentController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
