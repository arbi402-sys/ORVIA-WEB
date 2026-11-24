<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $topics = Topic::with('creator')
            ->withCount(['members', 'posts'])
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"))
            ->orderByDesc('members_count')
            ->paginate(12)
            ->withQueryString();

        $joinedIds = Auth::check() ? Auth::user()->joinedTopics()->pluck('topics.id')->all() : [];

        return view('topics.index', compact('topics', 'q', 'joinedIds'));
    }

    public function show(Topic $topic)
    {
        $topic->load('creator')->loadCount(['members', 'posts']);

        $posts = $topic->posts()
            ->with(['user', 'topic', 'acceptedCollaborators.collaborator'])
            ->withCount(['likedBy', 'comments'])
            ->where('status', 'published')
            ->latest()
            ->paginate(10);

        $isMember = Auth::check()
            ? Auth::user()->joinedTopics()->where('topic_id', $topic->id)->exists()
            : false;

        $userLikedPostIds = Auth::check()
            ? Auth::user()->likedPosts()->pluck('posts.id')->all()
            : [];

        return view('topics.show', compact('topic', 'posts', 'isMember', 'userLikedPostIds'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->canCreateTopics()) {
            abort(403, 'Hanya Lembaga Terverifikasi atau Administrator yang dapat membuat Topik baru.');
        }

        $data = $request->validate([
            'name' => 'required|string|min:2|max:60|unique:topics,name',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:4096',
            'banner' => 'nullable|image|max:6144',
        ]);

        $avatarPath = $request->hasFile('avatar') ? $request->file('avatar')->store('topics/avatars', 'public') : null;
        $bannerPath = $request->hasFile('banner') ? $request->file('banner')->store('topics/banners', 'public') : null;

        $topic = Topic::create([
            'creator_id' => $user->id,
            'name' => trim($data['name']),
            'description' => $data['description'] ?? null,
            'avatar' => $avatarPath,
            'banner' => $bannerPath,
        ]);

        $topic->members()->syncWithoutDetaching([$user->id]);

        return redirect()->route('topics.show', $topic->slug)
            ->with('success', 'Topik O/' . $topic->name . ' berhasil dibuat!');
    }

    public function toggleJoin(Topic $topic)
    {
        $user = Auth::user();
        if ($user->joinedTopics()->where('topic_id', $topic->id)->exists()) {
            $user->joinedTopics()->detach($topic->id);
            $joined = false;
            $msg = 'Anda keluar dari topik O/' . $topic->name;
        } else {
            $user->joinedTopics()->attach($topic->id);
            $joined = true;
            $msg = 'Berhasil bergabung ke topik O/' . $topic->name;
        }

        if (request()->expectsJson()) {
            return response()->json([
                'joined' => $joined,
                'members_count' => $topic->members()->count(),
            ]);
        }

        return back()->with('success', $msg);
    }
}
