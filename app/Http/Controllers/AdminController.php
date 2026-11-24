<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'institutions'); // institutions, moderation, users

        $pendingInstitutions = User::where('role', User::ROLE_INSTITUTION)
            ->where('verification_status', 'pending')
            ->latest()
            ->get();

        $approvedInstitutions = User::where('role', User::ROLE_INSTITUTION)
            ->where('verification_status', 'approved')
            ->latest('verified_at')
            ->get();

        $rejectedInstitutions = User::where('role', User::ROLE_INSTITUTION)
            ->where('verification_status', 'rejected')
            ->latest('updated_at')
            ->get();

        $stats = [
            'users' => User::count(),
            'verified_institutions' => User::where('role', User::ROLE_INSTITUTION)->where('is_verified', true)->count(),
            'pending_institutions' => $pendingInstitutions->count(),
            'posts' => Post::count(),
            'articles' => Article::count(),
            'events' => Event::count(),
            'topics' => Topic::count(),
        ];

        $recentPosts = Post::with(['user', 'topic'])->latest()->limit(15)->get();
        $recentArticles = Article::with('user')->latest()->limit(15)->get();
        $recentEvents = Event::with('organizer')->latest()->limit(15)->get();
        $recentTopics = Topic::with('creator')->withCount('members')->latest()->limit(15)->get();
        $allUsers = User::latest()->paginate(20)->withQueryString();

        return view('admin.index', compact(
            'tab',
            'stats',
            'pendingInstitutions',
            'approvedInstitutions',
            'rejectedInstitutions',
            'recentPosts',
            'recentArticles',
            'recentEvents',
            'recentTopics',
            'allUsers'
        ));
    }

    public function approveInstitution(User $user)
    {
        abort_unless($user->isInstitution(), 404);

        // Generate unique Institution Access Code: ORV-XXXX-XXXX
        do {
            $code = 'ORV-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (User::where('institution_access_code', $code)->exists());

        $user->update([
            'verification_status' => 'approved',
            'institution_access_code' => $code,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        UserNotification::notifyUser(
            $user->id,
            Auth::id(),
            'institution_approved',
            'Pengajuan Lembaga Anda disetujui! Gunakan Kode Akses: ' . $code,
            route('institution.activate.form', ['email' => $user->email], false)
        );

        return back()->with('success', "Lembaga {$user->name} disetujui! Kode Akses Masuk Lembaga: {$code}");
    }

    public function regenerateAccessCode(User $user)
    {
        abort_unless($user->isInstitution(), 404);

        do {
            $code = 'ORV-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        } while (User::where('institution_access_code', $code)->exists());

        $user->update([
            'institution_access_code' => $code,
            'verification_status' => 'approved',
            'verified_by' => Auth::id(),
        ]);

        return back()->with('success', "Kode Akses baru untuk {$user->name}: {$code}");
    }

    public function rejectInstitution(Request $request, User $user)
    {
        abort_unless($user->isInstitution(), 404);

        $data = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $user->update([
            'verification_status' => 'rejected',
            'is_verified' => false,
            'institution_access_code' => null,
            'rejection_reason' => $data['rejection_reason'] ?? 'Dokumen verifikasi belum memenuhi persyaratan.',
        ]);

        return back()->with('success', "Pengajuan lembaga {$user->name} telah ditolak.");
    }

    public function deleteContent(Request $request, string $type, int $id)
    {
        $model = match ($type) {
            'post' => Post::findOrFail($id),
            'article' => Article::findOrFail($id),
            'event' => Event::findOrFail($id),
            'topic' => Topic::findOrFail($id),
            default => abort(404),
        };

        $model->delete();

        return back()->with('success', 'Konten berhasil dihapus oleh Administrator.');
    }
}
