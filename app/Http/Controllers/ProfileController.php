<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Category;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show($identifier, Request $request)
    {
        if (is_numeric($identifier)) {
            $user = User::find($identifier);
        } else {
            $decoded = rawurldecode($identifier);
            $alt = str_replace('-', ' ', $decoded);
            $lower = mb_strtolower($decoded);

            $user = User::where('username', $decoded)
                ->orWhere('username', $alt)
                ->orWhereRaw('lower(username) = ?', [$lower])
                ->first();
        }

        if (!$user) {
            return response()->view('errors.profile_not_found', [
                'identifier' => $identifier,
                'authUser' => Auth::user(),
            ], 404);
        }

        $tab = $request->get('tab', 'activity'); // activity, posts, events, articles, topics

        $posts = Schema::hasTable('posts')
            ? $user->posts()->with(['user', 'topic', 'acceptedCollaborators.collaborator'])
                ->withCount(['likedBy', 'comments'])
                ->where('status', 'published')
                ->latest()->paginate(8, ['*'], 'posts_page')
            : collect();

        $articles = Schema::hasTable('articles')
            ? $user->articles()->with(['user', 'acceptedCollaborators.collaborator'])
                ->withCount(['likedBy', 'comments'])
                ->where('status', 'published')
                ->latest()->paginate(8, ['*'], 'articles_page')
            : collect();

        $events = Schema::hasTable('events')
            ? $user->events()->with(['organizer', 'acceptedCollaborators.collaborator'])
                ->withCount('registrations')
                ->orderBy('event_date', 'desc')
                ->paginate(8, ['*'], 'events_page')
            : collect();

        $topics = Schema::hasTable('topics')
            ? $user->createdTopics()->withCount(['members', 'posts'])->latest()->get()
            : collect();

        $joinedTopics = Schema::hasTable('topic_members')
            ? $user->joinedTopics()->withCount(['members', 'posts'])->latest()->get()
            : collect();

        $artworks = Schema::hasTable('artworks')
            ? $user->artworks()->with('category')->latest()->limit(12)->get()
            : collect();

        $followersCount = Schema::hasTable('follows') ? $user->followers()->count() : 0;
        $followingCount = Schema::hasTable('follows') ? $user->following()->count() : 0;
        $isFollowing = Auth::check() && Schema::hasTable('follows')
            ? Auth::user()->following()->where('followed_id', $user->id)->exists()
            : false;

        $totalPostsCount = ($user->posts()->where('status', 'published')->count())
            + ($user->articles()->where('status', 'published')->count());

        $userLikedPostIds = Auth::check() ? Auth::user()->likedPosts()->pluck('posts.id')->all() : [];
        $userLikedArticleIds = Auth::check() ? Auth::user()->likedArticles()->pluck('articles.id')->all() : [];

        return view('profile.show', compact(
            'user',
            'tab',
            'posts',
            'articles',
            'events',
            'topics',
            'joinedTopics',
            'artworks',
            'followersCount',
            'followingCount',
            'isFollowing',
            'totalPostsCount',
            'userLikedPostIds',
            'userLikedArticleIds'
        ));
    }

    public function edit()
    {
        return redirect()->route('settings.index', ['section' => 'profile']);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'nullable|string|max:120',
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'username' => 'nullable|string|max:50|unique:users,username,' . $user->id,
            'bio' => 'nullable|string|max:500',
            'headline' => 'nullable|string|max:150',
            'institution' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:150',
            'website' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'social_instagram' => 'nullable|string|max:255',
            'social_whatsapp' => 'nullable|string|max:255',
            'social_x' => 'nullable|string|max:255',
            'avatar' => 'nullable|image|max:4096',
            'cover_image' => 'nullable|image|max:6144',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        if (empty($data['name']) && (!empty($data['first_name']) || !empty($data['last_name']))) {
            $data['name'] = trim(($data['first_name'] ?? $user->first_name) . ' ' . ($data['last_name'] ?? $user->last_name));
        }

        if (empty($data['username'])) {
            unset($data['username']);
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function storeArtwork(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'required|image|max:10240',
        ]);

        $path = $request->file('image')->store('artworks', 'public');

        Artwork::create([
            'user_id' => Auth::id(),
            'category_id' => $request->input('category_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'image_path' => $path,
        ]);

        return back()->with('success', 'Karya berhasil diunggah.');
    }

    public function destroyArtwork(Request $request, Artwork $art)
    {
        $user = Auth::user();
        if (!$user || ($user->id !== $art->user_id && !$user->isAdmin())) {
            abort(403);
        }

        if ($art->image_path && Storage::disk('public')->exists($art->image_path)) {
            Storage::disk('public')->delete($art->image_path);
        }

        $art->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Karya dihapus.');
    }

    public function follow($username)
    {
        $target = User::where('username', $username)->orWhere('id', is_numeric($username) ? $username : 0)->firstOrFail();
        $me = Auth::user();

        if ($me->id === $target->id) {
            return back();
        }

        $me->following()->syncWithoutDetaching([$target->id]);

        UserNotification::notifyUser(
            $target->id,
            $me->id,
            'follow',
            'Mulai mengikuti Anda',
            route('profile.show', $me->username ?: $me->id, false)
        );

        return back()->with('success', 'Anda sekarang mengikuti ' . $target->name);
    }

    public function unfollow($username)
    {
        $target = User::where('username', $username)->orWhere('id', is_numeric($username) ? $username : 0)->firstOrFail();
        $me = Auth::user();
        $me->following()->detach($target->id);

        return back()->with('success', 'Berhenti mengikuti ' . $target->name);
    }
}
