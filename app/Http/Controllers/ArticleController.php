<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Collaboration;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $topicSlug = $request->get('topic');

        $articles = Article::with(['user', 'category', 'topic', 'acceptedCollaborators.collaborator'])
            ->withCount(['likedBy', 'comments'])
            ->where('status', 'published')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('subtitle', 'like', "%{$q}%")
                        ->orWhere('body', 'like', "%{$q}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('username', 'like', "%{$q}%"));
                });
            })
            ->when($topicSlug, fn ($query) => $query->whereHas('topic', fn ($t) => $t->where('slug', $topicSlug)))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $userLikedArticleIds = Auth::check()
            ? Auth::user()->likedArticles()->pluck('articles.id')->all()
            : [];
        $userFollowingIds = Auth::check()
            ? Auth::user()->following()->pluck('users.id')->all()
            : [];

        return view('articles.index', compact('articles', 'q', 'userLikedArticleIds', 'userFollowingIds'));
    }

    public function show(Article $article)
    {
        $article->increment('views_count');
        $article->loadMissing([
            'user',
            'category',
            'topic',
            'acceptedCollaborators.collaborator',
            'comments' => fn ($q) => $q->whereNull('parent_id')->with(['user', 'replies.user']),
        ]);
        $article->loadCount(['likedBy', 'comments']);

        $isLiked = Auth::check()
            ? Auth::user()->likedArticles()->where('article_id', $article->id)->exists()
            : false;
        $isFollowing = Auth::check() && $article->user
            ? Auth::user()->following()->where('followed_id', $article->user_id)->exists()
            : false;

        $verifiedInstitutions = Auth::check() && Auth::user()->canCollaborate()
            ? User::where('role', User::ROLE_INSTITUTION)
                ->where('is_verified', true)
                ->where('id', '!=', Auth::id())
                ->get()
            : collect();

        return view('articles.show', compact('article', 'isLiked', 'isFollowing', 'verifiedInstitutions'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:300',
            'category_id' => 'nullable|exists:categories,id',
            'topic_id' => 'nullable|exists:topics,id',
            'body' => 'required|string',
            'image' => 'nullable|image|max:10240',
            'status' => 'nullable|in:published,draft',
            'collaborator_ids' => 'nullable|array',
            'collaborator_ids.*' => 'exists:users,id',
        ]);

        $path = $request->hasFile('image') ? $request->file('image')->store('articles', 'public') : null;

        $article = Article::create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'] ?? null,
            'topic_id' => $data['topic_id'] ?? null,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'body' => $data['body'],
            'image_path' => $path,
            'status' => $data['status'] ?? 'published',
        ]);

        if ($user->canCollaborate() && !empty($data['collaborator_ids'])) {
            $targets = User::whereIn('id', $data['collaborator_ids'])
                ->where('role', User::ROLE_INSTITUTION)
                ->where('is_verified', true)
                ->where('id', '!=', $user->id)
                ->get();

            foreach ($targets as $target) {
                Collaboration::firstOrCreate(
                    [
                        'collaboratable_type' => Article::class,
                        'collaboratable_id' => $article->id,
                        'collaborator_id' => $target->id,
                    ],
                    [
                        'inviter_id' => $user->id,
                        'status' => 'pending',
                        'role_label' => 'Mitra Editorial',
                    ]
                );

                UserNotification::notifyUser(
                    $target->id,
                    $user->id,
                    'collaboration_invite',
                    'Mengundang lembaga Anda berkolaborasi pada artikel "' . Str::limit($article->title, 45) . '"',
                    route('dashboard', ['tab' => 'collaborations'], false),
                    $article->image_url
                );
            }
        }

        if ($article->status === 'draft') {
            return redirect()->route('dashboard')->with('success', 'Artikel disimpan sebagai draf.');
        }

        return redirect()->route('articles.show', $article)->with('success', 'Artikel berhasil diterbitkan!');
    }

    public function toggleLike(Article $article)
    {
        $user = Auth::user();
        $exists = $user->likedArticles()->where('article_id', $article->id)->exists();

        if ($exists) {
            $user->likedArticles()->detach($article->id);
            $liked = false;
        } else {
            $user->likedArticles()->attach($article->id);
            $liked = true;
            UserNotification::notifyUser(
                $article->user_id,
                $user->id,
                'like',
                'Menyukai artikel Anda "' . Str::limit($article->title, 50) . '"',
                route('articles.show', $article, false),
                $article->image_url
            );
        }

        $count = $article->likedBy()->count();

        if (request()->expectsJson()) {
            return response()->json(['liked' => $liked, 'like_count' => $count]);
        }

        return back();
    }

    public function storeComment(Request $request, Article $article)
    {
        $data = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $article->comments()->create([
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ]);

        UserNotification::notifyUser(
            $article->user_id,
            Auth::id(),
            'comment',
            'Mengomentari artikel Anda "' . Str::limit($article->title, 50) . '"',
            route('articles.show', $article, false) . '#comment-' . $comment->id,
            $article->image_url
        );

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function recordShare(Article $article)
    {
        $article->increment('share_count');
        $article->refresh();

        return response()->json(['share_count' => $article->share_count]);
    }

    public function destroy(Request $request, Article $article)
    {
        $user = Auth::user();
        if (!$user || ($user->id !== $article->user_id && !$user->isAdmin())) {
            abort(403);
        }

        if ($article->image_path && !str_starts_with($article->image_path, '/')) {
            try {
                Storage::disk('public')->delete($article->image_path);
            } catch (\Throwable $e) {
            }
        }

        $article->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('articles.index')->with('success', 'Artikel berhasil dihapus.');
    }
}
