<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Collaboration;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CollaborationController extends Controller
{
    public function invite(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->canCollaborate()) {
            abort(403, 'Fitur kolaborasi hanya tersedia untuk Lembaga Terverifikasi.');
        }

        $data = $request->validate([
            'type' => 'required|in:post,article,event',
            'id' => 'required|integer',
            'collaborator_id' => 'required|exists:users,id',
            'role_label' => 'nullable|string|max:60',
        ]);

        $modelClass = match ($data['type']) {
            'post' => Post::class,
            'article' => Article::class,
            'event' => Event::class,
        };

        $targetModel = $modelClass::findOrFail($data['id']);
        if ($targetModel->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Anda hanya dapat mengundang kolaborator pada konten milik lembaga Anda sendiri.');
        }

        $collaborator = User::findOrFail($data['collaborator_id']);
        if (!$collaborator->isVerifiedInstitution() || $collaborator->id === $user->id) {
            return back()->with('error', 'Kolaborator harus merupakan akun Lembaga Terverifikasi lain.');
        }

        Collaboration::updateOrCreate(
            [
                'collaboratable_type' => $modelClass,
                'collaboratable_id' => $targetModel->id,
                'collaborator_id' => $collaborator->id,
            ],
            [
                'inviter_id' => $user->id,
                'status' => 'pending',
                'role_label' => $data['role_label'] ?? 'Kolaborator',
            ]
        );

        UserNotification::notifyUser(
            $collaborator->id,
            $user->id,
            'collaboration_invite',
            'Mengundang lembaga Anda berkolaborasi pada "' . Str::limit($targetModel->title, 45) . '"',
            route('dashboard', ['tab' => 'collaborations'], false)
        );

        return back()->with('success', 'Undangan kolaborasi dikirim ke ' . $collaborator->name . '.');
    }

    public function respond(Request $request, Collaboration $collaboration)
    {
        $user = Auth::user();
        if (!$user || $collaboration->collaborator_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'decision' => 'required|in:accepted,declined',
        ]);

        $collaboration->update(['status' => $data['decision']]);

        $statusText = $data['decision'] === 'accepted' ? 'menerima' : 'menolak';
        $title = optional($collaboration->collaboratable)->title ?? 'konten';

        UserNotification::notifyUser(
            $collaboration->inviter_id,
            $user->id,
            'collaboration_response',
            ucfirst($statusText) . ' undangan kolaborasi pada "' . Str::limit($title, 45) . '"',
            route('dashboard', ['tab' => 'collaborations'], false)
        );

        return back()->with('success', 'Undangan kolaborasi berhasil ' . ($data['decision'] === 'accepted' ? 'diterima.' : 'ditolak.'));
    }
}
