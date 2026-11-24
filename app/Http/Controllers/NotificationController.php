<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->userNotifications()
            ->with('actor')
            ->latest()
            ->limit(60)
            ->get();

        $newNotifications = $notifications->filter(fn ($n) => is_null($n->read_at) || $n->created_at->gt(now()->subDay()));
        $thisWeekNotifications = $notifications->reject(fn ($n) => $newNotifications->contains('id', $n->id));

        // Mark all as read when visiting notifications page
        $user->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        $followingIds = $user->following()->pluck('users.id')->all();

        return view('notifications.index', compact('newNotifications', 'thisWeekNotifications', 'followingIds'));
    }

    public function markAllRead()
    {
        Auth::user()->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
