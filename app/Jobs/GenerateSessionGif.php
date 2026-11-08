<?php

namespace App\Jobs;

use App\Models\BoothSession;
use App\Models\SessionMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class GenerateSessionGif implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public BoothSession $session) {}

    public function handle(): void
    {
        $sessionId = $this->session->id;
        $storagePath = storage_path('app/public/sessions/' . $sessionId);

        // Pastikan folder sesi ada
        if (!is_dir($storagePath)) return;

        // Path input berurutan (raw_pose_1.jpg, raw_pose_2.jpg, dst)
        $inputPattern = $storagePath . '/raw_pose_%d.jpg';
        $outputPath = $storagePath . '/animation.gif';

        // Perintah FFmpeg
        // -framerate 2 : Mengatur kecepatan 2 frame/gambar per detik
        // -vf scale=600:-1, ... : Memperkecil resolusi dan membuat palet warna khusus agar GIF tidak buram
        $command = [
            'ffmpeg', '-y', 
            '-framerate', '2',
            '-i', $inputPattern,
            '-vf', 'scale=600:-1,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse',
            '-loop', '0',
            $outputPath
        ];

        // Eksekusi proses
        $result = Process::run($command);

        if ($result->successful()) {
            // Simpan ke database jika berhasil
            SessionMedia::create([
                'booth_session_id' => $sessionId,
                'type' => 'gif_animation',
                'file_path' => '/storage/sessions/' . $sessionId . '/animation.gif',
            ]);
        } else {
            Log::error("FFmpeg Gagal: " . $result->errorOutput());
        }
    }
}
