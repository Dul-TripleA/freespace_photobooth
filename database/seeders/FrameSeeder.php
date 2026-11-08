<?php

namespace Database\Seeders;

use App\Models\Frame;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FrameSeeder extends Seeder
{
    public function run(): void
    {
        $frames = [
            [
                'id' => Str::uuid(),
                'name' => 'Vintage Cinema 3-Pose',
                'category' => 'formal',
                'overlay_path' => '/storage/frames/cinema-ticket.png',
                'total_poses' => 3,
                // Ukuran kanvas utama 1200x1800 (Rasio 4R)
                'canvas_width' => 1200, 
                'canvas_height' => 1800,
                
                // Koordinat ini disesuaikan untuk desain gambar di atas
                // Ukuran tiap foto: 440 x 340 pixel
                'slots_config' => json_encode([
                    // --- STRIP KIRI ---
                    ['pose' => 1, 'x' => 80, 'y' => 360, 'width' => 440, 'height' => 340],
                    ['pose' => 2, 'x' => 80, 'y' => 750, 'width' => 440, 'height' => 340],
                    ['pose' => 3, 'x' => 80, 'y' => 1120, 'width' => 440, 'height' => 340],
                    
                    // --- STRIP KANAN (Copy dari foto yang sama) ---
                    ['pose' => 1, 'x' => 680, 'y' => 360, 'width' => 440, 'height' => 340],
                    ['pose' => 2, 'x' => 680, 'y' => 750, 'width' => 440, 'height' => 340],
                    ['pose' => 3, 'x' => 680, 'y' => 1120, 'width' => 440, 'height' => 340],
                ]),
                'is_active' => true,
            ],
            [
                'id' => Str::uuid(),
                'name' => 'Pink Love',
                'category' => 'pesta',
                'overlay_path' => '/storage/frames/pink-love.png',
                'total_poses' => 4,
                'slots_config' => json_encode([
                    ['pose' => 1, 'x' => 100, 'y' => 150, 'width' => 450, 'height' => 600],
                    ['pose' => 2, 'x' => 650, 'y' => 150, 'width' => 450, 'height' => 600],
                    ['pose' => 3, 'x' => 100, 'y' => 800, 'width' => 450, 'height' => 600],
                    ['pose' => 4, 'x' => 650, 'y' => 800, 'width' => 450, 'height' => 600],
                ]),
                'is_active' => true,
            ]
        ];

        foreach ($frames as $frame) {
            Frame::create($frame);
        }
    }
}
