<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'overview');

        $postsCount = $user->posts()->where('status', 'published')->count();
        $articlesCount = $user->articles()->where('status', 'published')->count();
        $eventsCount = $user->events()->count();
        $followersCount = $user->followers()->count();

        $totalViews = (int) $user->posts()->sum('views_count') + (int) $user->articles()->sum('views_count');
        $totalPostLikes = $user->posts()->withCount('likedBy')->get()->sum('liked_by_count');
        $totalArticleLikes = $user->articles()->withCount('likedBy')->get()->sum('liked_by_count');
        $totalLikes = $totalPostLikes + $totalArticleLikes;
        $totalComments = $user->posts()->withCount('comments')->get()->sum('comments_count')
            + $user->articles()->withCount('comments')->get()->sum('comments_count');

        $draftPosts = $user->posts()->where('status', 'draft')->latest()->limit(6)->get();
        $draftArticles = $user->articles()->where('status', 'draft')->latest()->limit(6)->get();

        $recentPosts = $user->posts()->withCount(['likedBy', 'comments'])->latest()->limit(5)->get();
        $recentArticles = $user->articles()->withCount(['likedBy', 'comments'])->latest()->limit(5)->get();

        $incomingCollaborations = $user->isVerifiedInstitution()
            ? $user->incomingCollaborations()->with(['inviter', 'collaboratable'])->latest()->get()
            : collect();

        $outgoingCollaborations = $user->isVerifiedInstitution()
            ? $user->outgoingCollaborations()->with(['collaborator', 'collaboratable'])->latest()->get()
            : collect();

        return view('dashboard.index', compact(
            'user',
            'tab',
            'postsCount',
            'articlesCount',
            'eventsCount',
            'followersCount',
            'totalViews',
            'totalLikes',
            'totalComments',
            'draftPosts',
            'draftArticles',
            'recentPosts',
            'recentArticles',
            'incomingCollaborations',
            'outgoingCollaborations'
        ));
    }

    public function publishDraftPost(Post $post)
    {
        abort_unless($post->user_id === Auth::id(), 403);
        $post->update(['status' => 'published']);
        return redirect()->route('posts.show', $post)->with('success', 'Draf postingan berhasil diterbitkan!');
    }

    public function publishDraftArticle(Article $article)
    {
        abort_unless($article->user_id === Auth::id(), 403);
        $article->update(['status' => 'published']);
        return redirect()->route('articles.show', $article)->with('success', 'Draf artikel berhasil diterbitkan!');
    }
}
