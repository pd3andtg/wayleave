<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Share pending user count with the dashboard layout so the navbar
        // badge doesn't run a raw DB query inline on every blade render.
        // Only queries when the user is an admin or officer.
        View::composer('layouts.dashboard', function ($view) {
            $pendingCount = 0;
            if (Auth::check() && Auth::user()->hasAnyRole(['admin', 'officer'])) {
                $pendingCount = User::where('status', 'pending')->count();
            }
            $view->with('pendingCount', $pendingCount);
        });
    }
}
