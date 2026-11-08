<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\BoothSession;
use App\Models\SessionMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateSessionGif;

class MediaUploadController extends Controller
{
    public function upload(Request $request, BoothSession $session)
    {
        $request->validate([
            'composite_image' => 'required|string',
            'raw_photos' => 'required|array',
            'filter' => 'nullable|string'
        ]);

        // Simpan filter yang dipilih ke tabel sesi
        $session->update(['selected_filter' => $request->filter ?? 'normal']);

        // Buat folder khusus untuk sesi ini
        $sessionFolder = 'sessions/' . $session->id;

        // 1. Simpan Foto Komposit Akhir (Sudah digabung dengan frame)
        $compositePath = $sessionFolder . '/composite.jpg';
        $this->saveBase64Image($request->composite_image, $compositePath);
        
        SessionMedia::create([
            'booth_session_id' => $session->id,
            'type' => 'composite_photo',
            'file_path' => '/storage/' . $compositePath,
        ]);

        // 2. Simpan Foto Mentah Per Pose
        foreach ($request->raw_photos as $index => $base64) {
            $poseNum = $index + 1;
            $rawPath = $sessionFolder . '/raw_pose_' . $poseNum . '.jpg';
            $this->saveBase64Image($base64, $rawPath);
            
            SessionMedia::create([
                'booth_session_id' => $session->id,
                'type' => 'raw_photo',
                'pose_number' => $poseNum,
                'file_path' => '/storage/' . $rawPath,
            ]);
        }

        // 3. Picu pembuatan GIF di latar belakang
        GenerateSessionGif::dispatch($session);

        // Arahkan ke halaman Hasil (QR Code)
        return redirect()->route('kiosk.result', ['session' => $session->id]);
    }

    // Helper untuk mengubah string Base64 dari Vue menjadi file fisik
    private function saveBase64Image($base64String, $path)
    {
        @list($type, $fileData) = explode(';', $base64String);
        @list(, $fileData)      = explode(',', $fileData);
        
        Storage::disk('public')->put($path, base64_decode($fileData));
    }
}