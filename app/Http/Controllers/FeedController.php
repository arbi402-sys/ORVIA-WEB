<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Collaboration;
use App\Models\Comment;
use App\Models\Event;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'for-you'); // for-you, popular, following
        $q = trim((string) $request->get('q', ''));
        $topicSlug = $request->get('topic');

        $postsQuery = Post::with(['user', 'topic', 'category', 'acceptedCollaborators.collaborator'])
            ->withCount(['likedBy', 'comments'])
            ->where('status', 'published');

        if ($q !== '') {
            $postsQuery->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('body', 'like', "%{$q}%")
                    ->orWhereHas('topic', fn ($tq) => $tq->where('name', 'like', "%{$q}%"))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$q}%")->orWhere('username', 'like', "%{$q}%"));
            });
        }

        if ($topicSlug) {
            $postsQuery->whereHas('topic', fn ($tq) => $tq->where('slug', $topicSlug));
        }

        if ($tab === 'popular') {
            $postsQuery->orderByDesc('liked_by_count')->orderByDesc('views_count')->latest();
        } elseif ($tab === 'following' && Auth::check()) {
            $me = Auth::user();
            $followingIds = $me->following()->pluck('users.id')->push($me->id);
            $joinedTopicIds = $me->joinedTopics()->pluck('topics.id');

            $postsQuery->where(function ($sub) use ($followingIds, $joinedTopicIds) {
                $sub->whereIn('user_id', $followingIds)
                    ->orWhereIn('topic_id', $joinedTopicIds);
            })->latest();
        } else {
            $postsQuery->latest();
        }

        $posts = $postsQuery->paginate(10)->withQueryString();

        // Highlighted articles on feed (matching Figma Beranda mixing topic posts & institution articles)
        $featuredArticles = Article::with(['user', 'acceptedCollaborators.collaborator'])
            ->withCount(['likedBy', 'comments'])
            ->where('status', 'published')
            ->latest()
            ->limit(3)
            ->get();

        $upcomingEvents = Event::with(['organizer', 'acceptedCollaborators.collaborator'])
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date')
            ->limit(4)
            ->get();

        if ($upcomingEvents->isEmpty()) {
            $upcomingEvents = Event::with(['organizer', 'acceptedCollaborators.collaborator'])
                ->latest('event_date')
                ->limit(4)
                ->get();
        }

        $popularTopics = Topic::withCount(['members', 'posts'])
            ->orderByDesc('members_count')
            ->limit(6)
            ->get();

        $userLikedPostIds = Auth::check()
            ? Auth::user()->likedPosts()->pluck('posts.id')->all()
            : [];
        $userJoinedTopicIds = Auth::check()
            ? Auth::user()->joinedTopics()->pluck('topics.id')->all()
            : [];
        $userFollowingIds = Auth::check()
            ? Auth::user()->following()->pluck('users.id')->all()
            : [];

        return view('feed.index', compact(
            'posts',
            'featuredArticles',
            'upcomingEvents',
            'popularTopics',
            'tab',
            'q',
            'userLikedPostIds',
            'userJoinedTopicIds',
            'userFollowingIds'
        ));
    }

    public function show(Post $post)
    {
        $post->increment('views_count');
        $post->load([
            'user',
            'topic',
            'category',
            'acceptedCollaborators.collaborator',
            'comments' => fn ($q) => $q->whereNull('parent_id')->with(['user', 'replies.user']),
        ]);
        $post->loadCount(['likedBy', 'comments']);

        $isLiked = Auth::check()
            ? Auth::user()->likedPosts()->where('post_id', $post->id)->exists()
            : false;

        $verifiedInstitutions = Auth::check() && Auth::user()->canCollaborate()
            ? User::where('role', User::ROLE_INSTITUTION)
                ->where('is_verified', true)
                ->where('id', '!=', Auth::id())
                ->get()
            : collect();

        return view('feed.show', compact('post', 'isLiked', 'verifiedInstitutions'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'topic_id' => 'nullable|exists:topics,id',
            'category_id' => 'nullable|exists:categories,id',
            'body' => 'nullable|string|max:10000',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,mp4,webm|max:20480',
            'status' => 'nullable|in:published,draft',
            'collaborator_ids' => 'nullable|array',
            'collaborator_ids.*' => 'exists:users,id',
        ]);

        $mediaPath = null;
        $mediaType = null;
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mediaPath = $file->store('posts', 'public');
            $mediaType = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
        }

        $post = Post::create([
            'user_id' => $user->id,
            'topic_id' => $data['topic_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'body' => $data['body'] ?? '',
            'media_path' => $mediaPath,
            'media_type' => $mediaType,
            'status' => $data['status'] ?? 'published',
        ]);

        // Institution collaboration invites
        if ($user->canCollaborate() && !empty($data['collaborator_ids'])) {
            $this->attachCollaborators($post, $user, $data['collaborator_ids']);
        }

        if ($post->status === 'draft') {
            return redirect()->route('dashboard')->with('success', 'Postingan berhasil disimpan sebagai draf.');
        }

        return redirect()->route('posts.show', $post)->with('success', 'Postingan berhasil diterbitkan!');
    }

    public function destroy(Post $post)
    {
        $user = Auth::user();
        if (!$user || ($user->id !== $post->user_id && !$user->isAdmin())) {
            abort(403);
        }

        if ($post->media_path && !str_starts_with($post->media_path, '/')) {
            Storage::disk('public')->delete($post->media_path);
        }

        $post->delete();

        return redirect()->route('beranda')->with('success', 'Postingan berhasil dihapus.');
    }

    public function toggleLike(Post $post)
    {
        $user = Auth::user();
        $exists = $user->likedPosts()->where('post_id', $post->id)->exists();

        if ($exists) {
            $user->likedPosts()->detach($post->id);
            $liked = false;
        } else {
            $user->likedPosts()->attach($post->id);
            $liked = true;
            UserNotification::notifyUser(
                $post->user_id,
                $user->id,
                'like',
                'Menyukai postingan Anda "' . \Illuminate\Support\Str::limit($post->title, 50) . '"',
                route('posts.show', $post, false),
                $post->media_url
            );
        }

        $count = $post->likedBy()->count();

        if (request()->expectsJson()) {
            return response()->json(['liked' => $liked, 'like_count' => $count]);
        }

        return back();
    }

    public function storeComment(Request $request, Post $post)
    {
        $data = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $post->comments()->create([
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ]);

        UserNotification::notifyUser(
            $post->user_id,
            Auth::id(),
            'comment',
            'Mengomentari postingan Anda "' . \Illuminate\Support\Str::limit($post->title, 50) . '"',
            route('posts.show', $post, false) . '#comment-' . $comment->id,
            $post->media_url
        );

        return back()->with('success', 'Komentar berhasil dikirim.');
    }

    public function recordShare(Post $post)
    {
        $post->increment('share_count');
        $post->refresh();

        return response()->json(['share_count' => $post->share_count]);
    }

    private function attachCollaborators(Post $post, User $inviter, array $collaboratorIds): void
    {
        $targets = User::whereIn('id', $collaboratorIds)
            ->where('role', User::ROLE_INSTITUTION)
            ->where('is_verified', true)
            ->where('id', '!=', $inviter->id)
            ->get();

        foreach ($targets as $target) {
            Collaboration::firstOrCreate(
                [
                    'collaboratable_type' => Post::class,
                    'collaboratable_id' => $post->id,
                    'collaborator_id' => $target->id,
                ],
                [
                    'inviter_id' => $inviter->id,
                    'status' => 'pending',
                    'role_label' => 'Co-Author',
                ]
            );

            UserNotification::notifyUser(
                $target->id,
                $inviter->id,
                'collaboration_invite',
                'Mengundang lembaga Anda berkolaborasi pada postingan "' . \Illuminate\Support\Str::limit($post->title, 45) . '"',
                route('dashboard', ['tab' => 'collaborations'], false),
                $post->media_url
            );
        }
    }
}
