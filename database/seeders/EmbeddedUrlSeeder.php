<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmbeddedUrl;
use App\Models\User;

class EmbeddedUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get an admin user to be the creator
        $adminUser = User::whereHas('roles', function($query) {
            $query->where('name', 'Admin');
        })->first();

        if (!$adminUser) {
            $adminUser = User::first();
        }

        if (!$adminUser) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        // Sample embedded URLs
        $embeddedUrls = [
            [
                'target_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.3766342310496!2d106.83247237573137!3d-6.213958660866425!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f500222830cd%3A0x32dca68b14420ac5!2sEDGE%202%20Data%20Center!5e0!3m2!1sid!2sid!4v1747459692724!5m2!1sid!2sid',
                'description' => 'Google Maps embed for Indonet Data Center',
                'created_by' => $adminUser->id,
                'is_active' => true,
            ],
            [
                'target_url' => 'https://www.youtube.com/embed/bwVUxy9Od_4?si=t8ncpmNsra481JGN',
                'description' => 'YouTube video embed for demonstration',
                'created_by' => $adminUser->id,
                'is_active' => true,
            ],
            [
                'target_url' => 'https://www.slideshare.net/slideshow/embed_code/key/NpsfNWGlRzdPoj',
                'description' => 'SlideShare presentation embed about data analytics',
                'created_by' => $adminUser->id,
                'is_active' => true,
            ],
            [
                'target_url' => 'https://analytics.indonet.co.id/external-dashboard',
                'description' => 'Indonet Analytics External Dashboard',
                'created_by' => $adminUser->id,
                'is_active' => true,
            ],
            [
                'target_url' => 'https://public.tableau.com/views/TheAirWeBreatheIronVizFinal/TheAirWeBreatheIronVizFinal?:language=en-US&:sid=&:redirect=auth&:display_count=n&:origin=viz_share_link',
                'description' => 'Tableau dashboard embed example',
                'created_by' => $adminUser->id,
                'is_active' => true,
            ]
        ];

        foreach ($embeddedUrls as $urlData) {
            EmbeddedUrl::create($urlData);
        }

        $this->command->info('Sample embedded URLs created successfully.');
    }
}
