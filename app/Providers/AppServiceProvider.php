<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\AdminNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            if (auth()->check() && in_array(auth()->user()->role, ['admin', 'koordinator'])) {
                try {
                    if (!Schema::hasTable('admin_notifications')) {
                        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                    }

                    if (Schema::hasTable('admin_notifications')) {
                        $notifications = AdminNotification::latest()
                            ->take(12)
                            ->get();
                        $unreadCount = AdminNotification::where('is_read', false)->count();

                        $view->with([
                            'adminNotifications' => $notifications,
                            'adminUnreadNotificationCount' => $unreadCount,
                        ]);
                        return;
                    }
                } catch (\Throwable $e) {
                    // Fallback on error or unmigrated database
                }
            }

            $view->with([
                'adminNotifications' => collect(),
                'adminUnreadNotificationCount' => 0,
            ]);
        });
    }
}
