<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sport;

class SportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sports = [
            ['name' => 'Cricket', 'display_name' => 'Cricket', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Cricket', 'color' => '#FF4500', 'description' => 'Cricket is the most popular sport in Pakistan, played with passion across the country.'],
            ['name' => 'Football', 'display_name' => 'Football', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Football', 'color' => '#2E8B57', 'description' => 'Football (soccer) is gaining popularity in Pakistan with growing youth participation.'],
            ['name' => 'Hockey', 'display_name' => 'Field Hockey', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Field-Hockey', 'color' => '#4169E1', 'description' => 'Field hockey is Pakistan\'s national sport with a rich history of international success.'],
            ['name' => 'Squash', 'display_name' => 'Squash', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Squash', 'color' => '#32CD32', 'description' => 'Pakistan has produced world-class squash players and has strong squash traditions.'],
            ['name' => 'Badminton', 'display_name' => 'Badminton', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Badminton', 'color' => '#9370DB', 'description' => 'Badminton is a popular indoor sport in Pakistan\'s sports clubs.'],
            ['name' => 'Tennis', 'display_name' => 'Tennis', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Tennis', 'color' => '#FFD700', 'description' => 'Tennis is played in clubs and academies across major Pakistani cities.'],
            ['name' => 'Table Tennis', 'display_name' => 'Table Tennis', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Tabble-Tennis', 'color' => '#DC143C', 'description' => 'Table tennis is a popular indoor sport in Pakistani sports clubs.'],
            ['name' => 'Swimming', 'display_name' => 'Swimming', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Swimming', 'color' => '#20B2AA', 'description' => 'Swimming facilities are available in many sports clubs across Pakistan.'],
            ['name' => 'Volleyball', 'display_name' => 'Volleyball', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Volleyball', 'color' => '#FF6347', 'description' => 'Volleyball is played both indoors and on beaches in Pakistan.'],
            ['name' => 'Basketball', 'display_name' => 'Basketball', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Basketball', 'color' => '#FF8C00', 'description' => 'Basketball is growing in popularity, especially among youth in Pakistan.'],
            ['name' => 'Boxing', 'display_name' => 'Boxing', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Boxing', 'color' => '#B22222', 'description' => 'Boxing has a strong tradition in Pakistan with many international champions.'],
            ['name' => 'Weightlifting', 'display_name' => 'Weightlifting', 'icon' => 'https://placehold.co/128x128/orange/white/png?text=Weight-Lifting', 'color' => '#696969', 'description' => 'Weightlifting and strength training facilities in sports clubs.'],
        ];

        foreach ($sports as $sport) {
            Sport::updateOrCreate(
                ['name' => $sport['name']],
                array_merge($sport, ['is_active' => true])
            );
        }
    }
}
