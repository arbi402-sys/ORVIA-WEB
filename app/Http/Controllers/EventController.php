<?php

namespace App\Http\Controllers;

use App\Models\Collaboration;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status');

        $events = Event::with(['organizer', 'acceptedCollaborators.collaborator'])
            ->withCount('registrations')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('title', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%")
                        ->orWhere('location_name', 'like', "%{$q}%")
                        ->orWhere('event_type', 'like', "%{$q}%");
                });
            })
            ->when($status === 'open', fn ($query) => $query->where('registration_status', 'open'))
            ->orderBy('event_date', 'asc')
            ->paginate(10)
            ->withQueryString();

        $registeredEventIds = Auth::check()
            ? Auth::user()->eventRegistrations()->pluck('event_id')->all()
            : [];

        return view('events.index', compact('events', 'q', 'registeredEventIds'));
    }

    public function show(Event $event, Request $request)
    {
        $tab = $request->get('tab', 'all'); // all, about, gallery, reviews
        $event->load([
            'organizer',
            'acceptedCollaborators.collaborator',
            'comments' => fn ($q) => $q->whereNull('parent_id')->with(['user', 'replies.user']),
        ]);
        $event->loadCount(['registrations', 'comments']);

        $isRegistered = Auth::check()
            ? $event->registrations()->where('user_id', Auth::id())->exists()
            : false;

        $verifiedInstitutions = Auth::check() && Auth::user()->canCollaborate()
            ? User::where('role', User::ROLE_INSTITUTION)
                ->where('is_verified', true)
                ->where('id', '!=', Auth::id())
                ->get()
            : collect();

        return view('events.show', compact('event', 'tab', 'isRegistered', 'verifiedInstitutions'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->canCreateEvents()) {
            abort(403, 'Hanya Lembaga Terverifikasi atau Administrator yang dapat membuat Agenda Kegiatan.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:200',
            'event_type' => 'required|string|max:60',
            'description' => 'required|string',
            'location_name' => 'nullable|string|max:180',
            'city' => 'required|string|max:120',
            'event_date' => 'required|date',
            'registration_deadline' => 'nullable|date',
            'registration_status' => 'nullable|in:open,closed',
            'external_registration_url' => 'nullable|url|max:255',
            'poster' => 'nullable|image|max:10240',
            'banner' => 'nullable|image|max:10240',
            'schedule_titles' => 'nullable|array',
            'schedule_dates' => 'nullable|array',
            'collaborator_ids' => 'nullable|array',
            'collaborator_ids.*' => 'exists:users,id',
        ]);

        $posterPath = $request->hasFile('poster') ? $request->file('poster')->store('events/posters', 'public') : null;
        $bannerPath = $request->hasFile('banner') ? $request->file('banner')->store('events/banners', 'public') : null;

        $schedules = [];
        $titles = $data['schedule_titles'] ?? [];
        $dates = $data['schedule_dates'] ?? [];
        foreach ($titles as $idx => $sTitle) {
            if (trim((string) $sTitle) !== '') {
                $schedules[] = [
                    'title' => trim($sTitle),
                    'date' => trim((string) ($dates[$idx] ?? '')),
                    'active' => $idx === 0,
                ];
            }
        }
        if (empty($schedules)) {
            $schedules = [
                ['title' => 'Pendaftaran Dibuka', 'date' => now()->translatedFormat('d F Y'), 'active' => true],
                ['title' => 'Pelaksanaan Acara', 'date' => \Carbon\Carbon::parse($data['event_date'])->translatedFormat('d F Y'), 'active' => false],
            ];
        }

        $event = Event::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'event_type' => $data['event_type'],
            'description' => $data['description'],
            'location_name' => $data['location_name'] ?? null,
            'city' => $data['city'],
            'event_date' => $data['event_date'],
            'registration_deadline' => $data['registration_deadline'] ?? null,
            'registration_status' => $data['registration_status'] ?? 'open',
            'external_registration_url' => $data['external_registration_url'] ?? null,
            'poster_path' => $posterPath,
            'banner_path' => $bannerPath,
            'schedules' => $schedules,
        ]);

        if (!empty($data['collaborator_ids'])) {
            $targets = User::whereIn('id', $data['collaborator_ids'])
                ->where('role', User::ROLE_INSTITUTION)
                ->where('is_verified', true)
                ->where('id', '!=', $user->id)
                ->get();

            foreach ($targets as $target) {
                Collaboration::firstOrCreate(
                    [
                        'collaboratable_type' => Event::class,
                        'collaboratable_id' => $event->id,
                        'collaborator_id' => $target->id,
                    ],
                    [
                        'inviter_id' => $user->id,
                        'status' => 'pending',
                        'role_label' => 'Co-Organizer',
                    ]
                );

                UserNotification::notifyUser(
                    $target->id,
                    $user->id,
                    'collaboration_invite',
                    'Mengundang lembaga Anda berkolaborasi menyelenggarakan agenda "' . Str::limit($event->title, 45) . '"',
                    route('dashboard', ['tab' => 'collaborations'], false),
                    $event->poster_url
                );
            }
        }

        return redirect()->route('events.show', $event)->with('success', 'Agenda kegiatan berhasil diterbitkan!');
    }

    public function register(Request $request, Event $event)
    {
        $user = Auth::user();
        if (!$event->isOpenForRegistration()) {
            return back()->with('error', 'Pendaftaran untuk acara ini sudah ditutup.');
        }

        $data = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        EventRegistration::firstOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            ['notes' => $data['notes'] ?? null, 'status' => 'registered']
        );

        UserNotification::notifyUser(
            $event->user_id,
            $user->id,
            'event_registration',
            'Pendaftar baru di Acara "' . Str::limit($event->title, 50) . '"',
            route('events.show', $event, false),
            $event->poster_url
        );

        return back()->with('success', 'Pendaftaran acara berhasil! Sampai jumpa di lokasi.');
    }

    public function storeComment(Request $request, Event $event)
    {
        $data = $request->validate([
            'body' => 'required|string|max:1500',
        ]);

        $event->comments()->create([
            'user_id' => Auth::id(),
            'body' => $data['body'],
        ]);

        UserNotification::notifyUser(
            $event->user_id,
            Auth::id(),
            'comment',
            'Memberikan ulasan pada Acara "' . Str::limit($event->title, 50) . '"',
            route('events.show', [$event, 'tab' => 'reviews'], false),
            $event->poster_url
        );

        return redirect()->route('events.show', [$event, 'tab' => 'reviews'])
            ->with('success', 'Ulasan berhasil dikirim.');
    }

    public function recordShare(Event $event)
    {
        $event->increment('share_count');
        $event->refresh();

        return response()->json(['share_count' => $event->share_count]);
    }

    public function destroy(Event $event)
    {
        $user = Auth::user();
        if (!$user || ($user->id !== $event->user_id && !$user->isAdmin())) {
            abort(403);
        }

        if ($event->poster_path && !str_starts_with($event->poster_path, '/')) {
            Storage::disk('public')->delete($event->poster_path);
        }
        if ($event->banner_path && !str_starts_with($event->banner_path, '/')) {
            Storage::disk('public')->delete($event->banner_path);
        }

        $event->delete();

        return redirect()->route('events.index')->with('success', 'Agenda kegiatan berhasil dihapus.');
    }
}
