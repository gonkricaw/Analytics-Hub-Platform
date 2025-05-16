<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Create default avatar directory and default avatar if they don't exist
        $this->ensureDefaultAvatarExists();
    }

    /**
     * Ensure that the default avatar exists in the public storage
     */
    protected function ensureDefaultAvatarExists(): void
    {
        try {
            $defaultAvatarDir = public_path('images');
            $defaultAvatarPath = $defaultAvatarDir . '/default-avatar.png';

            // Create directory if it doesn't exist
            if (!File::exists($defaultAvatarDir)) {
                File::makeDirectory($defaultAvatarDir, 0755, true);
            }

            // If default avatar doesn't exist, create it
            if (!File::exists($defaultAvatarPath)) {
                // If you have a default avatar in your project, you can copy it
                // For now, we'll create a simple placeholder
                $this->generateDefaultAvatar($defaultAvatarPath);
            }
        } catch (\Exception $e) {
            // Log the error but don't prevent the application from starting
            \Log::error('Failed to create default avatar: ' . $e->getMessage());
        }
    }

    /**
     * Generate a simple default avatar image
     *
     * @param string $path
     */
    protected function generateDefaultAvatar(string $path): void
    {
        // Create a 200x200 image
        $image = imagecreatetruecolor(200, 200);

        // Set background color to light gray
        $backgroundColor = imagecolorallocate($image, 240, 240, 240);
        imagefill($image, 0, 0, $backgroundColor);

        // Set user silhouette color to dark gray
        $avatarColor = imagecolorallocate($image, 140, 140, 140);

        // Draw a simple user silhouette (circle for head, body shape)
        // Head (circle)
        imagefilledellipse($image, 100, 70, 80, 80, $avatarColor);

        // Body (trapezoid)
        $body = [
            100 - 50, 110,  // top left
            100 + 50, 110,  // top right
            100 + 70, 200,  // bottom right
            100 - 70, 200   // bottom left
        ];
        imagefilledpolygon($image, $body, 4, $avatarColor);

        // Save as PNG
        imagepng($image, $path);
        imagedestroy($image);
    }
}
