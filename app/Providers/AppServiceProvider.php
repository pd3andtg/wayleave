<?php
namespace App\Providers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('layouts.dashboard', function ($view) {
            $pendingCount = 0;
            if (Auth::check() && Auth::user()->hasAnyRole(['admin', 'officer'])) {
                $pendingCount = User::where('status', 'pending')->count();
            }
            $view->with('pendingCount', $pendingCount);
        });
    }
}
