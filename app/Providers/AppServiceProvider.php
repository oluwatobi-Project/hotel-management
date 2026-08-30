<?php

namespace App\Providers;

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
        try {
            $settings = [
                'hotel_name' => \App\Models\Setting::get('hotel_name', 'Grand Horizon Hotel'),
                'hotel_address' => \App\Models\Setting::get('hotel_address', ''),
                'hotel_phone' => \App\Models\Setting::get('hotel_phone', ''),
                'hotel_email' => \App\Models\Setting::get('hotel_email', ''),
                'currency' => \App\Models\Setting::get('currency', '$'),
            ];
        } catch (\Throwable $e) {
            $settings = [
                'hotel_name' => 'Grand Horizon Hotel',
                'hotel_address' => '',
                'hotel_phone' => '',
                'hotel_email' => '',
                'currency' => '$',
            ];
        }

        view()->share('settings', $settings);

        view()->composer('layouts.app', function ($view) {
            $unreadCount = 0;
            $notifications = collect();

            if (auth()->check()) {
                $notifications = \App\Models\AppNotification::where(function ($q) {
                    $q->where('user_id', auth()->id())->orWhereNull('user_id');
                })->orderByDesc('created_at')->limit(8)->get();

                $unreadCount = \App\Models\AppNotification::where(function ($q) {
                    $q->where('user_id', auth()->id())->orWhereNull('user_id');
                })->where('is_read', false)->count();
            }

            $view->with('notifications', $notifications);
            $view->with('unreadCount', $unreadCount);
        });
    }
}
