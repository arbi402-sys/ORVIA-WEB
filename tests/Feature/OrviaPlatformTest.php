<?php

use App\Models\Article;
use App\Models\Collaboration;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeUser(array $attrs = []): User
{
    return User::create(array_merge([
        'name' => 'Regular User',
        'first_name' => 'Regular',
        'last_name' => 'User',
        'username' => 'user-' . uniqid(),
        'email' => 'user-' . uniqid() . '@orvia.test',
        'password' => Hash::make('password123'),
        'role' => User::ROLE_USER,
        'verification_status' => 'none',
        'is_verified' => false,
    ], $attrs));
}

function makeVerifiedInstitution(array $attrs = []): User
{
    return User::create(array_merge([
        'name' => 'HIMDKV Nusa Putra',
        'first_name' => 'HIMDKV',
        'last_name' => 'Nusa Putra',
        'username' => 'inst-' . uniqid(),
        'email' => 'inst-' . uniqid() . '@orvia.test',
        'password' => Hash::make('password123'),
        'role' => User::ROLE_INSTITUTION,
        'verification_status' => 'approved',
        'is_verified' => true,
        'institution_access_code' => 'ORV-' . strtoupper(substr(uniqid(), -4)) . '-2026',
        'access_code_used_at' => now(),
        'verified_at' => now(),
    ], $attrs));
}

function makeAdmin(): User
{
    return User::create([
        'name' => 'Admin ORVIA',
        'first_name' => 'Admin',
        'last_name' => 'ORVIA',
        'username' => 'admin-' . uniqid(),
        'email' => 'admin-' . uniqid() . '@orvia.test',
        'password' => Hash::make('password123'),
        'role' => User::ROLE_ADMIN,
        'verification_status' => 'approved',
        'is_verified' => true,
    ]);
}

test('guest can view public pages and share content, but is redirected on write and messaging routes', function () {
    $inst = makeVerifiedInstitution(['name' => 'ADGI Indonesia']);
    $topic = Topic::create(['creator_id' => $inst->id, 'name' => 'Illustration', 'description' => 'Desc']);
    $post = Post::create(['user_id' => $inst->id, 'topic_id' => $topic->id, 'title' => 'Diskusi Warna', 'body' => 'Isi diskusi']);
    $article = Article::create(['user_id' => $inst->id, 'title' => 'Artikel Desain', 'body' => '<p>Konten panjang</p>']);
    $event = Event::create([
        'user_id' => $inst->id,
        'title' => 'KMDGI 2026',
        'event_type' => 'Pameran',
        'description' => 'Acara nasional',
        'city' => 'Bandung',
        'event_date' => '2026-11-09',
    ]);

    $this->get('/')->assertOk()->assertSee('Diskusi Warna');
    $this->get('/articles')->assertOk()->assertSee('Artikel Desain');
    $this->get('/agendas')->assertOk()->assertSee('KMDGI 2026');
    $this->get(route('topics.show', $topic->slug))->assertOk();

    // Guest CAN share
    $this->postJson(route('posts.share', $post))->assertOk()->assertJson(['share_count' => 1]);
    $this->postJson(route('articles.share', $article))->assertOk()->assertJson(['share_count' => 1]);
    $this->postJson(route('events.share', $event))->assertOk()->assertJson(['share_count' => 1]);

    // Guest CANNOT create, like, comment, or access messages
    $this->get('/create')->assertRedirect('/login');
    $this->get('/messages')->assertRedirect('/login');
    $this->post(route('posts.store'), ['title' => 'Blocked'])->assertRedirect('/login');
    $this->post(route('posts.like', $post))->assertRedirect('/login');
    $this->post(route('posts.comments.store', $post), ['body' => 'Hi'])->assertRedirect('/login');
});

test('regular user can create posts, articles, like, comment, and send DMs, but is forbidden from creating events or topics', function () {
    $user = makeUser();
    $inst = makeVerifiedInstitution();

    // Can create Post
    $this->actingAs($user)
        ->post(route('posts.store'), ['title' => 'Postingan User Biasa', 'body' => 'Halo komunitas'])
        ->assertRedirect();
    $this->assertDatabaseHas('posts', ['title' => 'Postingan User Biasa', 'user_id' => $user->id]);

    // Can create Article
    $this->actingAs($user)
        ->post(route('articles.store'), ['title' => 'Artikel User Biasa', 'body' => '<p>Isi artikel</p>'])
        ->assertRedirect();
    $this->assertDatabaseHas('articles', ['title' => 'Artikel User Biasa', 'user_id' => $user->id]);

    // Can Like & Comment
    $post = Post::first();
    $this->actingAs($user)->postJson(route('posts.like', $post))->assertOk()->assertJson(['liked' => true, 'like_count' => 1]);
    $this->actingAs($user)->post(route('posts.comments.store', $post), ['body' => 'Komentar bagus!'])->assertRedirect();
    $this->assertDatabaseHas('comments', ['body' => 'Komentar bagus!', 'user_id' => $user->id]);

    // FORBIDDEN from creating Event or Topic (403)
    $this->actingAs($user)->get(route('create.index', ['tab' => 'event']))->assertForbidden();
    $this->actingAs($user)->get(route('create.index', ['tab' => 'topic']))->assertForbidden();
    $this->actingAs($user)->post(route('topics.store'), ['name' => 'UnauthorizedTopic'])->assertForbidden();
    $this->actingAs($user)->post(route('events.store'), [
        'title' => 'Unauthorized Event',
        'event_type' => 'Pameran',
        'description' => 'Test',
        'city' => 'Jakarta',
        'event_date' => '2026-12-01',
    ])->assertForbidden();

    // FORBIDDEN from Admin panel
    $this->actingAs($user)->get(route('admin.index'))->assertForbidden();

    // CAN send Direct Messages to institution
    $startRes = $this->actingAs($user)->post(route('messages.start', $inst->id));
    $startRes->assertRedirect();
    $conv = Conversation::first();
    expect($conv)->not->toBeNull();

    $this->actingAs($user)->post(route('messages.store', $conv), ['body' => 'Halo lembaga!'])->assertRedirect();
    $this->assertDatabaseHas('messages', ['conversation_id' => $conv->id, 'sender_id' => $user->id, 'body' => 'Halo lembaga!']);
    expect($inst->unreadMessagesCount())->toBe(1);

    // Institution opens conversation -> unread count clears to 0
    $this->actingAs($inst)->get(route('messages.show', $conv))->assertOk()->assertSee('Halo lembaga!');
    expect($inst->unreadMessagesCount())->toBe(0);
});

test('full institution registration, admin approval with access code, activation, yellow badge, event/topic creation, and inter-institution collaboration', function () {
    Storage::fake('public');

    $admin = makeAdmin();
    $partnerInst = makeVerifiedInstitution(['name' => 'ADGI Pusat']);

    // 1. Institution registers via separate form
    $proof = UploadedFile::fake()->create('surat_lembaga.pdf', 120, 'application/pdf');
    $this->post(route('institution.register'), [
        'institution_name' => 'HIMDKV Nusantara',
        'username' => 'himdkv-nusantara',
        'email' => 'himdkv@nusantara.ac.id',
        'location' => 'Sukabumi, Jawa Barat',
        'bio' => 'Himpunan mahasiswa DKV resmi.',
        'proof_image' => $proof,
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
        'terms' => '1',
    ])->assertRedirect();

    $inst = User::where('email', 'himdkv@nusantara.ac.id')->firstOrFail();
    expect($inst->role)->toBe(User::ROLE_INSTITUTION);
    expect($inst->verification_status)->toBe('pending');
    expect($inst->is_verified)->toBeFalse();

    // 2. Pending institution CANNOT log in yet
    $this->post('/login', [
        'email' => 'himdkv@nusantara.ac.id',
        'password' => 'secret1234',
    ])->assertSessionHasErrors('email');
    $this->assertGuest();

    // 3. Admin approves and generates Institution Access Code
    $this->actingAs($admin)
        ->post(route('admin.institutions.approve', $inst))
        ->assertRedirect();

    $inst->refresh();
    expect($inst->verification_status)->toBe('approved');
    expect($inst->institution_access_code)->not->toBeNull();
    expect($inst->is_verified)->toBeFalse(); // Must activate using access code first

    // 4. Institution activates using the access code
    $this->post('/logout');
    $this->post(route('institution.activate'), [
        'email' => 'himdkv@nusantara.ac.id',
        'access_code' => $inst->institution_access_code,
        'password' => 'secret1234',
    ])->assertRedirect(route('beranda'));

    $inst->refresh();
    expect($inst->is_verified)->toBeTrue();
    expect($inst->isVerifiedInstitution())->toBeTrue();

    // Yellow verified badge appears on institution profile
    $this->actingAs($inst)
        ->get(route('profile.show', $inst->username))
        ->assertOk()
        ->assertSee('data-verified-badge="yellow"', false);

    // 5. Verified Institution CAN create Topics & Events with Collaborator
    $this->actingAs($inst)->post(route('topics.store'), [
        'name' => 'TipografiNusantara',
        'description' => 'Komunitas tipografi',
    ])->assertRedirect();
    $this->assertDatabaseHas('topics', ['name' => 'TipografiNusantara', 'creator_id' => $inst->id]);

    $this->actingAs($inst)->post(route('events.store'), [
        'title' => 'Festival Desain Kolaborasi 2026',
        'event_type' => 'Pameran',
        'description' => 'Pameran bersama lintas lembaga.',
        'city' => 'Bandung',
        'event_date' => '2026-11-25',
        'collaborator_ids' => [$partnerInst->id],
    ])->assertRedirect();

    $event = Event::where('title', 'Festival Desain Kolaborasi 2026')->firstOrFail();
    $collab = Collaboration::where('collaboratable_id', $event->id)
        ->where('collaborator_id', $partnerInst->id)
        ->firstOrFail();
    expect($collab->status)->toBe('pending');

    // 6. Partner Institution accepts collaboration -> both institutions credited on Event page!
    $this->actingAs($partnerInst)
        ->post(route('collaborations.respond', $collab), ['decision' => 'accepted'])
        ->assertRedirect();

    $collab->refresh();
    expect($collab->status)->toBe('accepted');

    $this->get(route('events.show', $event->slug))
        ->assertOk()
        ->assertSee('HIMDKV Nusantara')
        ->assertSee('ADGI Pusat')
        ->assertSee('data-verified-badge="yellow"', false);
});
