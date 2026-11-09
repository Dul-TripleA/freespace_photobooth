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

class GenerateSessionGif implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $session;
    public $timeout = 120;

    public function __construct(BoothSession $session)
    {
        $this->session = $session;
    }

    public function handle(): void
    {
        // Ambil semua foto mentah berdasarkan urutan pose
        $rawPhotos = SessionMedia::where('booth_session_id', $this->session->id)
            ->whereIn('type', ['raw_photo', 'raw'])
            ->orderBy('pose_number')
            ->get();

        if ($rawPhotos->isEmpty()) {
            return;
        }

        $sessionFolder = storage_path('app/public/sessions/' . $this->session->id);
        if (!file_exists($sessionFolder)) {
            mkdir($sessionFolder, 0755, true);
        }

        $listFile = $sessionFolder . '/ffmpeg_list.txt';
        $listContent = '';
        $lastPhotoPath = null;

        foreach ($rawPhotos as $photo) {
            $relativePath = ltrim(str_replace('/storage/', '', $photo->file_path), '/');
            $fullPath = storage_path('app/public/' . $relativePath);

            if (file_exists($fullPath)) {
                $listContent .= "file '" . $fullPath . "'\n";
                $listContent .= "duration 0.45\n"; // Durasi tiap pose 0.45 detik
                $lastPhotoPath = $fullPath;
            }
        }

        if (!$lastPhotoPath) {
            return;
        }

        // FFmpeg concat demuxer membutuhkan file terakhir ditulis sekali lagi tanpa duration
        $listContent .= "file '" . $lastPhotoPath . "'\n";
        file_put_contents($listFile, $listContent);

        $gifFilename = 'animation_' . time() . '.gif';
        $gifFullPath = $sessionFolder . '/' . $gifFilename;

        // Render GIF berkualitas tinggi dengan palettegen
        $result = Process::run([
            'ffmpeg', '-y', '-f', 'concat', '-safe', '0', '-i', $listFile,
            '-vf', 'scale=640:-1:flags=lanczos,split[s0][s1];[s0]palettegen[p];[s1][p]paletteuse',
            '-loop', '0', $gifFullPath,
        ]);

        if (file_exists($listFile)) {
            @unlink($listFile);
        }

        if ($result->successful() && file_exists($gifFullPath)) {
            SessionMedia::updateOrCreate(
                [
                    'booth_session_id' => $this->session->id,
                    'type' => 'gif',
                ],
                [
                    'file_path' => '/storage/sessions/' . $this->session->id . '/' . $gifFilename,
                ]
            );
        }
    }
}