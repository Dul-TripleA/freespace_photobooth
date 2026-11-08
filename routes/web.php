<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Kiosk\SessionController;
use App\Http\Controllers\Kiosk\MediaUploadController;
use App\Models\BoothSession;
use App\Http\Controllers\Public\GalleryController;

Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [SessionController::class, 'index'])->name('home');
    Route::post('/session/start', [SessionController::class, 'start'])->name('session.start');
    
    Route::get('/session/{session}/frame', [SessionController::class, 'frameIndex'])->name('frame.index');
    Route::post('/session/{session}/frame', [SessionController::class, 'selectFrame'])->name('frame.select');
    
    Route::get('/session/{session}/capture', function(BoothSession $session) {
        $frame = App\Models\Frame::find($session->frame_id);
        return Inertia\Inertia::render('Kiosk/Capture', ['session' => $session, 'frame' => $frame]);
    })->name('capture');

    // Endpoint Upload Media
    Route::post('/session/{session}/upload', [MediaUploadController::class, 'upload'])->name('session.upload');

    // Halaman Hasil (QR Code)
    Route::get('/session/{session}/result', function(BoothSession $session) {
        $compositePhoto = $session->media()->where('type', 'composite_photo')->first();
        return Inertia\Inertia::render('Kiosk/Result', [
            'session' => $session,
            'compositePhoto' => $compositePhoto
        ]);
    })->name('result');
    // Route::get('/session/{session}/result', function(BoothSession $session) {
    //     // Load relasi media agar Vue bisa membaca GIF yang baru selesai di-generate
    //     $session->load('media'); 
        
    //     $compositePhoto =$session->media->where('type', 'composite_photo')->first();
        
    //     return Inertia\Inertia::render('Kiosk/Result', [
    //         'session' => $session,
    //         'compositePhoto' => $compositePhoto
    //     ]);
    // })->name('result');
});

// Placeholder Halaman Galeri Publik (Diakses dari HP saat scan QR Code)
// Halaman Galeri Publik (Diakses dari HP saat scan QR Code)
Route::get('/gallery', [\App\Http\Controllers\Public\GalleryController::class, 'show'])->name('gallery.show');

Route::get('/', function () {
    return redirect()->route('kiosk.home');
});