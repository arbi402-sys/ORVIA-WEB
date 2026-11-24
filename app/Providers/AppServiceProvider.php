<?php

namespace App\Providers;

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
        if ($this->app->runningInConsole() === false) {
            $router = $this->app->make('router');
            if (method_exists($router, 'pushMiddlewareToGroup')) {
                $router->pushMiddlewareToGroup('web', \App\Http\Middleware\LogAuthSession::class);
                $router->pushMiddlewareToGroup('web', \App\Http\Middleware\UpdateLastActivity::class);
                $router->pushMiddlewareToGroup('web', \App\Http\Middleware\PreventPageCache::class);
            }
        }

        // Share unread message & notification counters globally for sidebar badges
        View::composer('*', function ($view) {
            $unreadMessages = 0;
            $unreadNotifications = 0;
            $pendingCollaborations = 0;
            $pendingInstitutionsCount = 0;

            if (Auth::check()) {
                $user = Auth::user();
                try {
                    if (Schema::hasTable('messages') && Schema::hasTable('conversation_participants')) {
                        $unreadMessages = $user->unreadMessagesCount();
                    }
                    if (Schema::hasTable('user_notifications')) {
                        $unreadNotifications = $user->unreadNotificationsCount();
                    }
                    if ($user->isVerifiedInstitution() && Schema::hasTable('collaborations')) {
                        $pendingCollaborations = $user->incomingCollaborations()->where('status', 'pending')->count();
                    }
                    if ($user->isAdmin() && Schema::hasTable('users')) {
                        $pendingInstitutionsCount = \App\Models\User::where('role', 'institution')
                            ->where('verification_status', 'pending')
                            ->count();
                    }
                } catch (\Throwable $e) {
                    // Fail safely during early migrations or partial schema states
                }
            }

            $view->with([
                'globalUnreadMessages' => $unreadMessages,
                'globalUnreadNotifications' => $unreadNotifications,
                'globalPendingCollaborations' => $pendingCollaborations,
                'globalPendingInstitutionsCount' => $pendingInstitutionsCount,
            ]);
        });
    }
}
