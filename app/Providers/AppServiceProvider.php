<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Models\Course;
use App\Models\JoinRequest;
use App\Models\Enrollment;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
            View::composer('layouts.admin', function ($view) {
        $view->with([
            'layoutTotalUsers'      => User::count(),
            'layoutActiveCourses'   => Course::where('is_closed', false)->count(),
            'layoutPendingRequests' => JoinRequest::where('status', 'PENDING')->count(),
            'layoutTotalEnrollments'=> Enrollment::count(),
        ]);
    });
    }
}
