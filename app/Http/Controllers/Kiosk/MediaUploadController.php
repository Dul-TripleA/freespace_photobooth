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
        // 1. Tambahkan validasi untuk menerima array live_photos berformat video
        $request->validate([
            'composite_image' => 'required|string',
            'raw_photos' => 'required|array',
            'filter' => 'nullable|string',
            'live_photos' => 'nullable|array',
            'live_photos.*' => 'nullable|file|mimetypes:video/webm,video/mp4' 
        ]);

        // Simpan filter yang dipilih ke tabel sesi
        $session->update(['selected_filter' => $request->filter ?? 'normal']);

        // Buat folder khusus untuk sesi ini
        $sessionFolder = 'sessions/' . $session->id;

        // 2. Simpan Foto Komposit Akhir (Sudah digabung dengan frame)
        $compositePath = $sessionFolder . '/composite.jpg';
        $this->saveBase64Image($request->composite_image, $compositePath);
        
        SessionMedia::create([
            'booth_session_id' => $session->id,
            'type' => 'composite_photo',
            'file_path' => '/storage/' . $compositePath,
        ]);

        // 3. Simpan Foto Mentah Per Pose & Video Live Photo
        foreach ($request->raw_photos as $index => $base64) {
            $poseNum = $index + 1;
            
            // A. Simpan Foto Statis (Raw)
            $rawPath = $sessionFolder . '/raw_pose_' . $poseNum . '.jpg';
            $this->saveBase64Image($base64, $rawPath);
            
            SessionMedia::create([
                'booth_session_id' => $session->id,
                'type' => 'raw_photo',
                'pose_number' => $poseNum,
                'file_path' => '/storage/' . $rawPath,
            ]);

            // B. Simpan Video Live Photo (Jika Kiosk mengirimkannya)
            if ($request->hasFile("live_photos.{$index}")) {
                $videoFile = $request->file("live_photos.{$index}");
                $videoName = 'live_pose_' . $poseNum . '_' . time() . '.webm';
                
                // Simpan file ke storage/app/public/sessions/{id}/
                $videoFile->storeAs($sessionFolder, $videoName, 'public');

                SessionMedia::create([
                    'booth_session_id' => $session->id,
                    'type' => 'pose_live', // Tipe khusus untuk Live Photo
                    'pose_number' => $poseNum,
                    'file_path' => '/storage/' . $sessionFolder . '/' . $videoName,
                ]);
            }
        }

        // 4. Picu pembuatan GIF di latar belakang (sebagai alternatif/opsi tambahan)
        GenerateSessionGif::dispatchAfterResponse($session);

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