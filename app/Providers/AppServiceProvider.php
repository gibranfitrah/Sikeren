<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Notification;

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
    if (app()->environment('production')) {
        URL::forceScheme('https');
    }
    View::composer('*', function ($view) {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Menggunakan method bawaan relasi notifikasi Laravel
            $notifications = $user->notifications()->latest()->take(5)->get();
            $jumlah_notif  = $user->unreadNotifications()->count();
            $view->with('notifications', $notifications);
            $view->with('jumlah_notif', $jumlah_notif);
        }
    });
}
}