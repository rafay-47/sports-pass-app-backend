<?php

namespace Database\Seeders;

use App\Models\ClubImage;
use App\Models\Club;
use Illuminate\Database\Seeder;

class ClubImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clubs = Club::all();

        if ($clubs->isEmpty()) {
            $this->command->info('No clubs found. Skipping club images.');
            return;
        }

        $sports = ['Basketball', 'Swimming', 'Tennis', 'Football', 'Volleyball', 'Gym', 'Yoga', 'Boxing'];
        $colors = ['orange', 'blue', 'green', 'red', 'purple', 'teal', 'maroon', 'navy'];

        foreach ($clubs as $club) {
            // Create 3-6 images per club
            $numImages = rand(3, 6);

            for ($i = 0; $i < $numImages; $i++) {
                $sport = $sports[array_rand($sports)];
                $color = $colors[array_rand($colors)];
                $isPrimary = ($i === 0); // First image is primary

                ClubImage::create([
                    'club_id' => $club->id,
                    'image_url' => "https://placehold.co/800x600/{$color}/white/png?text=" . urlencode($club->name . ' - ' . $sport),
                    'alt_text' => "{$club->name} - {$sport} facility",
                    'is_primary' => $isPrimary,
                    'display_order' => $i + 1,
                ]);
            }
        }

        $this->command->info('Club images seeded successfully!');
    }
}
