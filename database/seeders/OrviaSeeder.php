<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Collaboration;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\Message;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrviaSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $catIlustrasi = Category::where('slug', 'illustration')->first();
        $catGraphic = Category::where('slug', 'graphic')->first();

        // 1. Administrator
        $admin = User::updateOrCreate(
            ['email' => 'admin@orvia.id'],
            [
                'name' => 'Admin ORVIA',
                'first_name' => 'Admin',
                'last_name' => 'ORVIA',
                'username' => 'admin-orvia',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN,
                'verification_status' => 'approved',
                'is_verified' => true,
                'bio' => 'Administrator platform komunitas kreatif ORVIA.',
                'location' => 'Jakarta, Indonesia',
            ]
        );

        // 2. Verified Institutions (Yellow Checkmark)
        $himdkv = User::updateOrCreate(
            ['email' => 'himdkv@orvia.id'],
            [
                'name' => 'HIMDKV Nusa Putra',
                'first_name' => 'HIMDKV',
                'last_name' => 'Nusa Putra',
                'username' => 'himdkv-nusaputra',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_INSTITUTION,
                'verification_status' => 'approved',
                'is_verified' => true,
                'institution_access_code' => 'ORV-HIMDKV-2026',
                'access_code_used_at' => now(),
                'verified_at' => now(),
                'verified_by' => $admin->id,
                'bio' => 'Forum komunitas desainer mahasiswa DKV Nusa Putra untuk berbagi ilmu, acara, dan peluang kreatif.',
                'institution' => 'Universitas Nusa Putra',
                'location' => 'Sukabumi, Jawa Barat',
                'city' => 'Sukabumi',
                'website' => 'https://nusaputra.ac.id',
                'cover_image' => '/images/BANNER.jpg',
                'avatar' => '/images/Group 15.png',
                'social_instagram' => 'https://instagram.com/himdkv.nusaputra',
            ]
        );

        $adgi = User::updateOrCreate(
            ['email' => 'adgi@orvia.id'],
            [
                'name' => 'ADGI (Asosiasi Desainer Grafis Indonesia)',
                'first_name' => 'ADGI',
                'last_name' => 'Indonesia',
                'username' => 'adgi-indonesia',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_INSTITUTION,
                'verification_status' => 'approved',
                'is_verified' => true,
                'institution_access_code' => 'ORV-ADGI-2026',
                'access_code_used_at' => now(),
                'verified_at' => now(),
                'verified_by' => $admin->id,
                'bio' => 'Asosiasi profesi desainer grafis Indonesia yang mendorong ekosistem desain nasional.',
                'institution' => 'ADGI Pusat',
                'location' => 'Jakarta, Indonesia',
                'city' => 'Jakarta',
                'website' => 'https://adgi.or.id',
                'cover_image' => '/images/hero.jpg',
                'avatar' => '/images/Group 20.png',
            ]
        );

        $ippdig = User::updateOrCreate(
            ['email' => 'ippdig@orvia.id'],
            [
                'name' => 'IPPDIG (Ikatan Pemuda Pemudi Desain Grafis)',
                'first_name' => 'IPPDIG',
                'last_name' => 'Indonesia',
                'username' => 'ippdig',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_INSTITUTION,
                'verification_status' => 'approved',
                'is_verified' => true,
                'institution_access_code' => 'ORV-IPPDIG-2026',
                'access_code_used_at' => now(),
                'verified_at' => now(),
                'verified_by' => $admin->id,
                'bio' => 'Jaringan komunitas pemuda pemudi desain grafis dan seni rupa nusantara.',
                'location' => 'Bandung, Jawa Barat',
                'city' => 'Bandung',
                'avatar' => '/images/Group 22.png',
            ]
        );

        // 3. Pending Institution (for Admin verification testing)
        User::updateOrCreate(
            ['email' => 'pending-studio@orvia.id'],
            [
                'name' => 'Komunitas Kolektif Visual Bandung',
                'first_name' => 'Kolektif',
                'last_name' => 'Visual',
                'username' => 'kolektif-visual-bdg',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_INSTITUTION,
                'verification_status' => 'pending',
                'is_verified' => false,
                'bio' => 'Kolektif seni visual independen Bandung yang mengajukan verifikasi lembaga.',
                'location' => 'Bandung, Jawa Barat',
            ]
        );

        // 4. Regular Users
        $aziz = User::updateOrCreate(
            ['email' => 'user@orvia.id'],
            [
                'name' => 'Aziz Harter',
                'first_name' => 'Aziz',
                'last_name' => 'Harter',
                'username' => 'azizharter',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_USER,
                'bio' => 'Ilustrator & desainer komunikasi visual.',
                'location' => 'Bandung, Jawa Barat',
                'avatar' => '/images/user.jpg',
            ]
        );

        $james = User::updateOrCreate(
            ['email' => 'james@orvia.id'],
            [
                'name' => 'James Pratama',
                'first_name' => 'James',
                'last_name' => 'Pratama',
                'username' => 'jamespratama',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_USER,
                'bio' => 'Animator & penggemar tipografi eksperimental.',
                'location' => 'Surabaya, Jawa Timur',
                'avatar' => '/images/team-1.jpg',
            ]
        );

        // 5. Topics (O/...) created by Verified Institutions
        $topicIllustration = Topic::firstOrCreate(
            ['slug' => 'illustration'],
            [
                'creator_id' => $himdkv->id,
                'name' => 'Illustration',
                'description' => 'Ruang diskusi, referensi gaya, dan pamer karya ilustrasi digital maupun manual.',
                'avatar' => '/images/ILUSTRASI.png',
            ]
        );

        $topicAnimasi = Topic::firstOrCreate(
            ['slug' => 'animasi'],
            [
                'creator_id' => $adgi->id,
                'name' => 'Animasi',
                'description' => 'Komunitas motion graphics, 2D/3D animation, dan storytelling bergerak.',
                'avatar' => '/images/graphic.png',
            ]
        );

        $topicTipografi = Topic::firstOrCreate(
            ['slug' => 'tipografi'],
            [
                'creator_id' => $ippdig->id,
                'name' => 'Tipografi',
                'description' => 'Eksplorasi huruf, type design lokal, dan tata letak editorial.',
                'avatar' => '/images/UI.png',
            ]
        );

        $topicIllustration->members()->syncWithoutDetaching([$himdkv->id, $adgi->id, $aziz->id, $james->id]);
        $topicAnimasi->members()->syncWithoutDetaching([$adgi->id, $james->id]);

        // 6. Posts (Reddit-style feed)
        $post1 = Post::firstOrCreate(
            ['title' => 'Eksplorasi Pencahayaan Hangat dalam Ilustrasi Karakter Anak'],
            [
                'user_id' => $himdkv->id,
                'topic_id' => $topicIllustration->id,
                'category_id' => $catIlustrasi?->id,
                'body' => 'Bagaimana cara menjaga kontras antara warna kuning hangat dan bayangan biru malam tanpa membuat komposisi terlalu berat? Yuk diskusi teknik lighting dan color script favorit kalian di sini!',
                'media_path' => '/images/putri.jpeg',
                'media_type' => 'image',
                'status' => 'published',
                'views_count' => 10240,
                'share_count' => 312,
            ]
        );

        $post2 = Post::firstOrCreate(
            ['title' => 'ADGI Design Week 2026: Kolaborasi Ekosistem Desain Nusantara'],
            [
                'user_id' => $adgi->id,
                'topic_id' => $topicTipografi->id,
                'category_id' => $catGraphic?->id,
                'body' => 'Tahun ini ADGI bersama HIMDKV Nusa Putra membuka ruang kurasi untuk karya desain grafis mahasiswa dan praktisi muda. Bagikan portofolio terbaikmu di kolom komentar!',
                'media_path' => '/images/BANNER.jpg',
                'media_type' => 'image',
                'status' => 'published',
                'views_count' => 7400,
                'share_count' => 185,
            ]
        );

        // Accepted collaboration on Post 2 (ADGI x HIMDKV)
        Collaboration::firstOrCreate(
            [
                'collaboratable_type' => Post::class,
                'collaboratable_id' => $post2->id,
                'collaborator_id' => $himdkv->id,
            ],
            [
                'inviter_id' => $adgi->id,
                'status' => 'accepted',
                'role_label' => 'Co-Publisher',
            ]
        );

        // 7. Articles (Substack-style long-form)
        $article1 = Article::firstOrCreate(
            ['title' => 'Membangun Narasi Visual yang Kuat di Era Media Interaktif'],
            [
                'user_id' => $himdkv->id,
                'category_id' => $catIlustrasi?->id,
                'topic_id' => $topicIllustration->id,
                'subtitle' => 'Panduan praktis memadukan ritme tipografi, warna, dan storytelling untuk proyek kreatif kolaboratif.',
                'body' => "<p>Di tengah arus informasi visual yang bergerak sangat cepat, kemampuan membangun narasi yang utuh menjadi pembeda utama seorang desainer. Tidak cukup hanya estetis secara permukaan — setiap elemen harus berbicara dalam satu suara.</p><h2>1. Mulai dari Resonansi Emosional</h2><p>Sebelum memilih palet warna atau grid, tanyakan pesan apa yang ingin diingat pembaca lima menit setelah menutup halaman. Ruang kosong (whitespace), kontras gelap-terang, dan hierarki huruf bekerja bersama mengarahkan fokus.</p><blockquote>\"Desain yang baik tidak berteriak paling keras, melainkan meninggalkan gema paling lama di benak audiens.\"</blockquote><h2>2. Kolaborasi Lintas Disiplin</h2><p>Kolaborasi antara lembaga kampus, asosiasi profesi, dan kreator independen membuka perspektif baru yang tidak lahir dari ruang kerja soliter. Melalui ORVIA, tulisan panjang dan diskusi komunitas dapat saling melengkapi secara organik.</p>",
                'image_path' => '/images/blog-1.jpg',
                'status' => 'published',
                'views_count' => 5200,
                'share_count' => 144,
            ]
        );

        $article2 = Article::firstOrCreate(
            ['title' => 'Arsip Tipografi Jalanan dan Pengaruhnya pada Identitas Modern'],
            [
                'user_id' => $ippdig->id,
                'category_id' => $catGraphic?->id,
                'topic_id' => $topicTipografi->id,
                'subtitle' => 'Menelusuri kembali karakter huruf lukis tangan dari Bandung hingga Surabaya.',
                'body' => "<p>Dari papan nama warung hingga spanduk layar tancap, tipografi vernakular Indonesia menyimpan kekayaan bentuk yang spontan sekaligus fungsional.</p><p>Generasi desainer muda kini mulai mendigitalisasi karakter-karakter tersebut menjadi sistem identitas modern yang segar dan berakar pada budaya lokal.</p>",
                'image_path' => '/images/blog-2.jpg',
                'status' => 'published',
                'views_count' => 9040,
                'share_count' => 230,
            ]
        );

        // Collaboration on Article 1 (HIMDKV x ADGI)
        Collaboration::firstOrCreate(
            [
                'collaboratable_type' => Article::class,
                'collaboratable_id' => $article1->id,
                'collaborator_id' => $adgi->id,
            ],
            [
                'inviter_id' => $himdkv->id,
                'status' => 'accepted',
                'role_label' => 'Mitra Editorial',
            ]
        );

        // 8. Events / Agenda (FilmFreeway-style, created by Verified Institutions)
        $event1 = Event::firstOrCreate(
            ['slug' => 'kmdgi-2026-bandung'],
            [
                'user_id' => $himdkv->id,
                'title' => 'Kriyasana Mahasiswa Desain Grafis Indonesia (KMDGI 2026)',
                'event_type' => 'Pameran',
                'description' => "Kriyasana Mahasiswa Desain Grafis Indonesia (KMDGI) adalah forum silaturahmi, pameran karya, seminar nasional, dan lokakarya lintas kampus desain komunikasi visual se-Indonesia.\n\nMelalui tema tahun ini, peserta diajak mengeksplorasi kolaborasi lintas daerah dan menghadirkan solusi desain yang berdampak nyata bagi masyarakat.",
                'poster_path' => '/images/DENI (1).jpg',
                'banner_path' => '/images/BANNER.jpg',
                'location_name' => 'Universitas Pendidikan Indonesia',
                'city' => 'Bandung, Jawa Barat',
                'event_date' => '2026-11-09',
                'registration_deadline' => '2026-11-01',
                'registration_status' => 'open',
                'schedules' => [
                    ['title' => 'Pendaftaran KMDGI', 'date' => '19 September 2026', 'active' => true],
                    ['title' => 'Penutupan Pendaftaran & Kurasi', 'date' => '01 November 2026', 'active' => true],
                    ['title' => 'Puncak Acara & Pameran', 'date' => '09 November 2026', 'active' => false],
                ],
                'gallery' => [
                    '/images/DENI (2).jpg',
                    '/images/DENI (3).jpg',
                    '/images/DENI (4).jpg',
                    '/images/DENI (5).jpg',
                ],
            ]
        );

        Event::firstOrCreate(
            ['slug' => 'idsa-6-bali-2026'],
            [
                'user_id' => $adgi->id,
                'title' => 'Indonesia Design Student Award 6 (IDSA 6)',
                'event_type' => 'Kompetisi & Pameran',
                'description' => 'Designing with Evidence, Innovating with Insight. Ajang penganugerahan, pameran nasional, dan talkshow mahasiswa desain di ISI Bali.',
                'poster_path' => '/images/DENI (3).jpg',
                'banner_path' => '/images/hero.jpg',
                'location_name' => 'ISI Denpasar',
                'city' => 'Bali',
                'event_date' => '2026-12-12',
                'registration_deadline' => '2026-12-01',
                'registration_status' => 'open',
                'schedules' => [
                    ['title' => 'Open Submission Karya', 'date' => '01 Oktober 2026', 'active' => true],
                    ['title' => 'Batas Akhir Pengumpulan', 'date' => '01 Desember 2026', 'active' => true],
                    ['title' => 'Awarding & National Exhibition', 'date' => '12 Desember 2026', 'active' => false],
                ],
            ]
        );

        Event::firstOrCreate(
            ['slug' => 'ideafest-sub-2027'],
            [
                'user_id' => $ippdig->id,
                'title' => 'IDEAFEST SUB 2027: The Next Leap',
                'event_type' => 'Festival & Seminar',
                'description' => 'Perancangan identitas visual dan festival ide kreatif terbesar di Jawa Timur bersama praktisi desain dan komunitas lokal.',
                'poster_path' => '/images/DENI (5).jpg',
                'banner_path' => '/images/about.jpg',
                'location_name' => 'Balai Pemuda Surabaya',
                'city' => 'Surabaya, Jawa Timur',
                'event_date' => '2027-01-07',
                'registration_deadline' => '2027-01-02',
                'registration_status' => 'open',
                'schedules' => [
                    ['title' => 'Early Bird Registration', 'date' => '01 Desember 2026', 'active' => true],
                    ['title' => 'Penutupan Pendaftaran', 'date' => '02 Januari 2027', 'active' => true],
                    ['title' => 'Festival Day', 'date' => '07 Januari 2027', 'active' => false],
                ],
            ]
        );

        // Collaboration on Event 1 (HIMDKV x IPPDIG)
        Collaboration::firstOrCreate(
            [
                'collaboratable_type' => Event::class,
                'collaboratable_id' => $event1->id,
                'collaborator_id' => $ippdig->id,
            ],
            [
                'inviter_id' => $himdkv->id,
                'status' => 'accepted',
                'role_label' => 'Co-Organizer',
            ]
        );

        // 9. Comments
        Comment::firstOrCreate(
            [
                'user_id' => $aziz->id,
                'commentable_type' => Post::class,
                'commentable_id' => $post1->id,
                'body' => 'Keren banget! Biasanya saya pakai layer overlay warm amber di 20% opacity lalu rim light biru dingin di tepi karakter.',
            ]
        );

        Comment::firstOrCreate(
            [
                'user_id' => $james->id,
                'commentable_type' => Article::class,
                'commentable_id' => $article1->id,
                'body' => 'Artikelnya sangat terstruktur dan nyaman dibaca. Setuju sekali soal pentingnya kolaborasi lintas lembaga!',
            ]
        );

        // 10. Direct Messaging sample (HIMDKV <-> ADGI & HIMDKV <-> Aziz)
        $conv1 = Conversation::findOrCreateDirect($himdkv, $adgi);
        if ($conv1->messages()->count() === 0) {
            Message::create([
                'conversation_id' => $conv1->id,
                'sender_id' => $adgi->id,
                'body' => 'Halo tim HIMDKV Nusa Putra! Kami sudah menyetujui kolaborasi untuk agenda KMDGI 2026.',
                'shared_event_id' => $event1->id,
                'read_at' => null,
            ]);
            Message::create([
                'conversation_id' => $conv1->id,
                'sender_id' => $himdkv->id,
                'body' => 'Terima kasih banyak ADGI! Mari kita koordinasikan jadwal kurasi minggu ini.',
                'read_at' => now(),
            ]);
            $conv1->update(['last_message_at' => now()]);
        }

        $conv2 = Conversation::findOrCreateDirect($aziz, $himdkv);
        if ($conv2->messages()->count() === 0) {
            Message::create([
                'conversation_id' => $conv2->id,
                'sender_id' => $aziz->id,
                'body' => 'Halo kak, apakah pendaftaran KMDGI 2026 terbuka untuk delegasi umum?',
                'read_at' => null,
            ]);
            $conv2->update(['last_message_at' => now()->subMinutes(15)]);
        }

        // 11. Sample Notifications
        UserNotification::firstOrCreate(
            [
                'user_id' => $himdkv->id,
                'actor_id' => $aziz->id,
                'type' => 'comment',
                'message' => 'Mengomentari postingan Anda "Eksplorasi Pencahayaan Hangat"',
            ],
            [
                'action_url' => route('posts.show', $post1->id, false),
                'thumbnail' => '/images/putri.jpeg',
            ]
        );

        UserNotification::firstOrCreate(
            [
                'user_id' => $himdkv->id,
                'actor_id' => $james->id,
                'type' => 'like',
                'message' => 'Menyukai artikel Anda "Membangun Narasi Visual yang Kuat"',
            ],
            [
                'action_url' => route('articles.show', $article1->id, false),
                'thumbnail' => '/images/blog-1.jpg',
            ]
        );
    }
}
