<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\CollaborationController;
use App\Http\Controllers\CreateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\InstitutionAuthController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Accessible by Guest, User, Institution, Admin)
|--------------------------------------------------------------------------
*/
Route::get('/', [FeedController::class, 'index'])->name('beranda');
Route::get('/beranda', [FeedController::class, 'index']);
Route::get('/explore', [FeedController::class, 'index'])->name('explore');
Route::get('/api/explore/load-more', [ExploreController::class, 'loadMore']);

// Topics (Reddit-style O/...)
Route::get('/topics', [TopicController::class, 'index'])->name('topics.index');
Route::get('/o/{topic:slug}', [TopicController::class, 'show'])->name('topics.show');

// Posts (Reddit-style discussions)
Route::get('/posts/{post}', [FeedController::class, 'show'])->name('posts.show');
Route::post('/posts/{post}/share', [FeedController::class, 'recordShare'])->name('posts.share');

// Articles (Substack-style long-form)
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/community', [ArticleController::class, 'index'])->name('community.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::post('/articles/{article}/share', [ArticleController::class, 'recordShare'])->name('articles.share');

// Agendas / Events (FilmFreeway-style directory & details)
Route::get('/agendas', [EventController::class, 'index'])->name('events.index');
Route::get('/agendas/{event:slug}', [EventController::class, 'show'])->name('events.show');
Route::post('/agendas/{event}/share', [EventController::class, 'recordShare'])->name('events.share');

// Public Profiles
Route::get('/users/{identifier}', [ProfileController::class, 'show'])->name('profile.show');

// Legacy public pages & share endpoint
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/category/{category}', [SearchController::class, 'category'])->name('category');
Route::post('/artworks/{art}/share', [ArtworkController::class, 'recordShare'])->name('artworks.share');
Route::get('/about', fn () => view('about'));
Route::get('/not-found', fn () => response()->view('errors.404', [], 404))->name('not-found');

/*
|--------------------------------------------------------------------------
| Authentication & Institution Verification Routes
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Regular User Registration
Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// Institution Separate Registration & Access Code Activation
Route::get('/register/institution', [InstitutionAuthController::class, 'showRegisterForm'])->name('institution.register.form');
Route::post('/register/institution', [InstitutionAuthController::class, 'register'])->name('institution.register');
Route::get('/institution/activate', [InstitutionAuthController::class, 'showActivateForm'])->name('institution.activate.form');
Route::post('/institution/activate', [InstitutionAuthController::class, 'activate'])->name('institution.activate');

/*
|--------------------------------------------------------------------------
| Authenticated Routes (User, Verified Institution, Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'institution.active'])->group(function () {
    // Dashboard & Drafts
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/drafts/post/{post}/publish', [DashboardController::class, 'publishDraftPost'])->name('drafts.post.publish');
    Route::post('/dashboard/drafts/article/{article}/publish', [DashboardController::class, 'publishDraftArticle'])->name('drafts.article.publish');

    // Create Center (Post & Article for all logged-in; Event & Topic enforced for Institution/Admin)
    Route::get('/create', [CreateController::class, 'index'])->name('create.index');

    // Posts (User, Institution, Admin)
    Route::post('/posts', [FeedController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [FeedController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [FeedController::class, 'toggleLike'])->name('posts.like');
    Route::post('/posts/{post}/comments', [FeedController::class, 'storeComment'])->name('posts.comments.store');

    // Articles (User, Institution, Admin)
    Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');
    Route::post('/articles/{article}/like', [ArticleController::class, 'toggleLike'])->name('articles.like');
    Route::post('/articles/{article}/comments', [ArticleController::class, 'storeComment'])->name('articles.comments.store');

    // Topic Membership & Event Registration / Reviews (User, Institution, Admin)
    Route::post('/o/{topic:slug}/join', [TopicController::class, 'toggleJoin'])->name('topics.join');
    Route::post('/agendas/{event:slug}/register', [EventController::class, 'register'])->name('events.register');
    Route::post('/agendas/{event:slug}/reviews', [EventController::class, 'storeComment'])->name('events.comments.store');

    // Direct Messaging / Inbox (User <-> User / Institution, Institution <-> Institution / User)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'index'])->name('messages.show');
    Route::post('/messages/start/{user}', [MessageController::class, 'startWithUser'])->name('messages.start');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->name('messages.store');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // Settings & Profile
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::put('/settings/security', [SettingsController::class, 'updateSecurity'])->name('settings.security');
    Route::delete('/settings/account', [SettingsController::class, 'destroyAccount'])->name('settings.account.destroy');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/users/{username}/follow', [ProfileController::class, 'follow'])->name('profile.follow');
    Route::post('/users/{username}/unfollow', [ProfileController::class, 'unfollow'])->name('profile.unfollow');

    // Legacy artwork & upload endpoints preserved
    Route::post('/profile/artworks', [ProfileController::class, 'storeArtwork'])->name('profile.artworks.store');
    Route::delete('/profile/artworks/{art}', [ProfileController::class, 'destroyArtwork'])->name('profile.artworks.destroy');
    Route::post('/upload/artwork', [UploadController::class, 'storeArtwork'])->name('upload.artwork');
    Route::post('/upload/article', [UploadController::class, 'storeArticle'])->name('upload.article');
    Route::post('/artworks/{art}/like', [ArtworkController::class, 'toggleLike'])->name('artworks.like');
    Route::post('/artworks/{art}/save', [ArtworkController::class, 'toggleSave'])->name('artworks.save');

    /*
    |----------------------------------------------------------------------
    | Verified Institution & Admin Only Routes
    |----------------------------------------------------------------------
    */
    Route::middleware(['role:institution,admin'])->group(function () {
        Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
        Route::post('/agendas', [EventController::class, 'store'])->name('events.store');
        Route::delete('/agendas/{event}', [EventController::class, 'destroy'])->name('events.destroy');

        // Inter-Institution Collaboration
        Route::post('/collaborations/invite', [CollaborationController::class, 'invite'])->name('collaborations.invite');
        Route::post('/collaborations/{collaboration}/respond', [CollaborationController::class, 'respond'])->name('collaborations.respond');
    });

    /*
    |----------------------------------------------------------------------
    | Administrator Panel Routes
    |----------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/institutions/{user}/approve', [AdminController::class, 'approveInstitution'])->name('institutions.approve');
        Route::post('/institutions/{user}/regenerate-code', [AdminController::class, 'regenerateAccessCode'])->name('institutions.regenerate');
        Route::post('/institutions/{user}/reject', [AdminController::class, 'rejectInstitution'])->name('institutions.reject');
        Route::delete('/content/{type}/{id}', [AdminController::class, 'deleteContent'])->name('content.delete');
    });
});

// Debug routes preserved in local env
if (file_exists(base_path('routes/web_debug_notify.php'))) {
    require_once base_path('routes/web_debug_notify.php');
}
if (file_exists(base_path('routes/web_debug_samples.php'))) {
    require_once base_path('routes/web_debug_samples.php');
}

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
