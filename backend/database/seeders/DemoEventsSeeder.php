<?php

namespace Database\Seeders;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoEventsSeeder extends Seeder
{
    private const MARKER_PREFIX = '[DEMO:campusevent-demo-v1:';

    public function run(): void
    {
        $organization = Organization::query()
            ->where('status', Organization::STATUS_ACTIVE)
            ->orderBy('id')
            ->first();

        if ($organization === null) {
            throw new RuntimeException(
                'An active organization is required to seed demo events. No organization was created.',
            );
        }

        $events = $this->events();
        $created = 0;
        $alreadyPresent = 0;

        DB::transaction(function () use ($events, $organization, &$created, &$alreadyPresent): void {
            $registrationStart = now()->startOfDay();

            foreach ($events as $demoEvent) {
                $marker = self::MARKER_PREFIX.$demoEvent['key'].']';
                $markedEvents = Event::query()
                    ->where('description', 'like', '%'.$marker)
                    ->get();

                if ($markedEvents->count() > 1) {
                    throw new RuntimeException(
                        "Multiple events use the demo marker for {$demoEvent['title']}. No events were seeded.",
                    );
                }

                $existingDemo = $markedEvents->first();
                $titleCollision = Event::query()
                    ->where('title', $demoEvent['title'])
                    ->when(
                        $existingDemo !== null,
                        fn ($query) => $query->where('id', '!=', $existingDemo->id),
                    )
                    ->exists();

                if ($titleCollision) {
                    throw new RuntimeException(
                        "An unmarked event already uses the title '{$demoEvent['title']}'. No events were seeded.",
                    );
                }

                if ($existingDemo !== null) {
                    $alreadyPresent++;
                    continue;
                }

                $eventStart = now()
                    ->startOfDay()
                    ->addDays($demoEvent['days_until_event'])
                    ->setTime($demoEvent['start_hour'], 0);

                Event::query()->create([
                    'organization_id' => $organization->id,
                    'title' => $demoEvent['title'],
                    'description' => $demoEvent['description']."\n\n".self::MARKER_PREFIX.$demoEvent['key'].']',
                    'location' => $demoEvent['location'],
                    'category' => $demoEvent['category'],
                    'registration_start' => $registrationStart,
                    'registration_end' => $eventStart->copy()->subDay(),
                    'event_start' => $eventStart,
                    'event_end' => $eventStart->copy()->addHours($demoEvent['duration_hours']),
                    'capacity' => $demoEvent['capacity'],
                    'reserved_count' => 0,
                    'sold_count' => 0,
                    'ticket_price' => $demoEvent['ticket_price'],
                    'status' => EventStatus::PUBLISHED->value,
                ]);

                $created++;
            }

            $markedCount = Event::query()
                ->where('description', 'like', '%'.self::MARKER_PREFIX.'%')
                ->count();

            if ($markedCount !== count($events)) {
                throw new RuntimeException(
                    "Expected exactly ".count($events)." marked demo events, found {$markedCount}. No events were seeded.",
                );
            }
        });

        $this->command?->info(
            "Demo events complete: {$created} created, {$alreadyPresent} already present; 20 total.",
        );
    }

    /**
     * @return list<array{
     *     key: string,
     *     title: string,
     *     description: string,
     *     category: string,
     *     location: string,
     *     ticket_price: int,
     *     capacity: int,
     *     days_until_event: int,
     *     start_hour: int,
     *     duration_hours: int
     * }>
     */
    private function events(): array
    {
        return [
            [
                'key' => 'techtalk-ai-pendidikan',
                'title' => 'TechTalk: AI di Dunia Pendidikan',
                'description' => 'Diskusi pemanfaatan AI dalam pembelajaran, riset, dan dunia akademik.',
                'category' => 'Seminar / Technology',
                'location' => 'Auditorium Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 180,
                'days_until_event' => 7,
                'start_hour' => 9,
                'duration_hours' => 2,
            ],
            [
                'key' => 'web-development-starter',
                'title' => 'Web Development Starter Workshop',
                'description' => 'Pengenalan dasar HTML, CSS, dan JavaScript untuk mahasiswa pemula.',
                'category' => 'Workshop / IT',
                'location' => 'Laboratorium Komputer Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 100,
                'days_until_event' => 9,
                'start_hour' => 9,
                'duration_hours' => 3,
            ],
            [
                'key' => 'speak-up-public-speaking',
                'title' => 'Speak Up! Public Speaking Class',
                'description' => 'Belajar menyampaikan ide, mengatasi gugup, dan membangun kepercayaan diri.',
                'category' => 'Workshop / Personal Development',
                'location' => 'Ruang Seminar Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 80,
                'days_until_event' => 12,
                'start_hour' => 13,
                'duration_hours' => 2,
            ],
            [
                'key' => 'cyber-security-awareness',
                'title' => 'Cyber Security Awareness',
                'description' => 'Mengenal phishing, keamanan akun, privasi digital, dan cyber hygiene.',
                'category' => 'Seminar / Technology',
                'location' => 'Auditorium Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 180,
                'days_until_event' => 15,
                'start_hour' => 10,
                'duration_hours' => 2,
            ],
            [
                'key' => 'campuspreneur-talk',
                'title' => 'Campuspreneur Talk',
                'description' => 'Sharing pengalaman membangun usaha dari ide sederhana di lingkungan kampus.',
                'category' => 'Seminar / Entrepreneurship',
                'location' => 'Ruang Inovasi Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 120,
                'days_until_event' => 18,
                'start_hour' => 13,
                'duration_hours' => 2,
            ],
            [
                'key' => 'career-preparation-day',
                'title' => 'Career Preparation Day',
                'description' => 'Persiapan CV, LinkedIn, portofolio, dan menghadapi interview kerja.',
                'category' => 'Seminar / Career',
                'location' => 'Gedung Serbaguna Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 220,
                'days_until_event' => 21,
                'start_hour' => 9,
                'duration_hours' => 3,
            ],
            [
                'key' => 'student-research-forum',
                'title' => 'Student Research Forum',
                'description' => 'Forum presentasi dan diskusi ide penelitian mahasiswa lintas jurusan.',
                'category' => 'Forum / Academic',
                'location' => 'Ruang Diskusi Perpustakaan Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 100,
                'days_until_event' => 24,
                'start_hour' => 13,
                'duration_hours' => 2,
            ],
            [
                'key' => 'lens-campus-photography',
                'title' => 'Lens of Campus Photography Exhibition',
                'description' => 'Pameran karya fotografi mahasiswa dengan tema kehidupan kampus.',
                'category' => 'Exhibition / Art',
                'location' => 'Galeri Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 250,
                'days_until_event' => 27,
                'start_hour' => 10,
                'duration_hours' => 6,
            ],
            [
                'key' => 'green-campus-action',
                'title' => 'Green Campus Action',
                'description' => 'Aksi kolaboratif untuk menjaga kebersihan dan lingkungan kampus.',
                'category' => 'Volunteer / Environment',
                'location' => 'Taman Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 150,
                'days_until_event' => 30,
                'start_hour' => 7,
                'duration_hours' => 3,
            ],
            [
                'key' => 'acoustic-night-student',
                'title' => 'Acoustic Night: Student Edition',
                'description' => 'Malam musik akustik dan penampilan kreatif mahasiswa.',
                'category' => 'Entertainment / Music',
                'location' => 'Halaman Terbuka Kampus, Samarinda',
                'ticket_price' => 0,
                'capacity' => 240,
                'days_until_event' => 33,
                'start_hour' => 18,
                'duration_hours' => 3,
            ],
            [
                'key' => 'ui-ux-design-bootcamp',
                'title' => 'UI/UX Design Bootcamp',
                'description' => 'Workshop praktis membuat wireframe dan prototype aplikasi menggunakan Figma.',
                'category' => 'Workshop / Design',
                'location' => 'Studio Desain Kampus, Samarinda',
                'ticket_price' => 50000,
                'capacity' => 60,
                'days_until_event' => 8,
                'start_hour' => 9,
                'duration_hours' => 4,
            ],
            [
                'key' => 'codefest-coding-challenge',
                'title' => 'CodeFest: Coding Challenge',
                'description' => 'Kompetisi pemrograman untuk menguji logika dan problem-solving peserta.',
                'category' => 'Competition / IT',
                'location' => 'Laboratorium Komputer Kampus, Samarinda',
                'ticket_price' => 35000,
                'capacity' => 100,
                'days_until_event' => 11,
                'start_hour' => 9,
                'duration_hours' => 4,
            ],
            [
                'key' => 'digital-marketing-masterclass',
                'title' => 'Digital Marketing Masterclass',
                'description' => 'Strategi social media, branding, content planning, dan pemasaran digital.',
                'category' => 'Workshop / Marketing',
                'location' => 'Ruang Seminar Kampus, Samarinda',
                'ticket_price' => 75000,
                'capacity' => 90,
                'days_until_event' => 14,
                'start_hour' => 13,
                'duration_hours' => 3,
            ],
            [
                'key' => 'photography-fundamentals',
                'title' => 'Photography Fundamentals',
                'description' => 'Belajar komposisi, pencahayaan, dan teknik fotografi dasar.',
                'category' => 'Workshop / Photography',
                'location' => 'Studio Kreatif Kampus, Samarinda',
                'ticket_price' => 40000,
                'capacity' => 70,
                'days_until_event' => 17,
                'start_hour' => 9,
                'duration_hours' => 3,
            ],
            [
                'key' => 'startup-idea-competition',
                'title' => 'Startup Idea Competition',
                'description' => 'Kompetisi ide startup dengan presentasi business model dan pitching.',
                'category' => 'Competition / Business',
                'location' => 'Auditorium Kampus, Samarinda',
                'ticket_price' => 25000,
                'capacity' => 140,
                'days_until_event' => 20,
                'start_hour' => 9,
                'duration_hours' => 4,
            ],
            [
                'key' => 'creative-writing-workshop',
                'title' => 'Creative Writing Workshop',
                'description' => 'Eksplorasi teknik menulis cerita, karakter, dialog, dan narasi.',
                'category' => 'Workshop / Literature',
                'location' => 'Ruang Baca Kampus, Samarinda',
                'ticket_price' => 30000,
                'capacity' => 70,
                'days_until_event' => 23,
                'start_hour' => 13,
                'duration_hours' => 3,
            ],
            [
                'key' => 'data-analytics-excel',
                'title' => 'Data Analytics with Excel',
                'description' => 'Praktik pengolahan data, visualisasi, dan analisis menggunakan Excel.',
                'category' => 'Workshop / Data',
                'location' => 'Laboratorium Komputer Kampus, Samarinda',
                'ticket_price' => 60000,
                'capacity' => 80,
                'days_until_event' => 26,
                'start_hour' => 9,
                'duration_hours' => 3,
            ],
            [
                'key' => 'student-leadership-camp',
                'title' => 'Student Leadership Camp',
                'description' => 'Pelatihan kepemimpinan, teamwork, komunikasi, dan manajemen organisasi.',
                'category' => 'Training / Leadership',
                'location' => 'Gedung Serbaguna Kampus, Samarinda',
                'ticket_price' => 100000,
                'capacity' => 100,
                'days_until_event' => 29,
                'start_hour' => 8,
                'duration_hours' => 6,
            ],
            [
                'key' => 'robotics-iot-workshop',
                'title' => 'Robotics & IoT Workshop',
                'description' => 'Pengenalan konsep IoT, sensor, dan prototipe perangkat sederhana.',
                'category' => 'Workshop / Technology',
                'location' => 'Laboratorium Elektronika Kampus, Samarinda',
                'ticket_price' => 85000,
                'capacity' => 60,
                'days_until_event' => 32,
                'start_hour' => 9,
                'duration_hours' => 4,
            ],
            [
                'key' => 'campus-music-fest',
                'title' => 'Campus Music Fest',
                'description' => 'Festival musik dengan penampilan band, musisi lokal, dan komunitas kampus.',
                'category' => 'Festival / Music',
                'location' => 'Lapangan Utama Kampus, Samarinda',
                'ticket_price' => 125000,
                'capacity' => 300,
                'days_until_event' => 35,
                'start_hour' => 17,
                'duration_hours' => 5,
            ],
        ];
    }
}
