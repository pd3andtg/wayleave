<?php
namespace App\Providers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        // Force HTTPS in production so asset URLs are generated with https://.
        // Railway terminates SSL at the proxy level and forwards as HTTP internally.
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

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
