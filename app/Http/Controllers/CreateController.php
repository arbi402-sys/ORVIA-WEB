<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $requestedTab = $request->get('tab', 'post');

        // Enforce role restrictions on tabs
        $allowedTabs = ['post', 'article'];
        if ($user->canCreateEvents()) {
            $allowedTabs[] = 'event';
        }
        if ($user->canCreateTopics()) {
            $allowedTabs[] = 'topic';
        }

        if (!in_array($requestedTab, $allowedTabs, true)) {
            if (in_array($requestedTab, ['event', 'topic'], true)) {
                abort(403, 'Fitur membuat Agenda Kegiatan dan Topik hanya tersedia untuk Lembaga Terverifikasi.');
            }
            $requestedTab = 'post';
        }

        $topics = Topic::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $verifiedInstitutions = $user->canCollaborate()
            ? User::where('role', User::ROLE_INSTITUTION)
                ->where('is_verified', true)
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get()
            : collect();

        return view('create.index', [
            'tab' => $requestedTab,
            'topics' => $topics,
            'categories' => $categories,
            'verifiedInstitutions' => $verifiedInstitutions,
        ]);
    }
}
