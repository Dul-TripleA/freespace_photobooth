<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Kiosk\SessionController;
use App\Http\Controllers\Kiosk\MediaUploadController;
use App\Models\BoothSession;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Admin\VoucherController;

use App\Models\Frame;
use App\Models\Voucher;
use Inertia\Inertia;

Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [SessionController::class, 'index'])->name('home');
    Route::post('/session/start', [SessionController::class, 'start'])->name('session.start');

    Route::get('/session/{session}/frame', [SessionController::class, 'frameIndex'])->name('frame.index');
    Route::post('/session/{session}/frame', [SessionController::class, 'selectFrame'])->name('frame.select');

    Route::get('/session/{session}/capture', function (BoothSession $session) {
        $frame = App\Models\Frame::find($session->frame_id);
        return Inertia::render('Kiosk/Capture', ['session' => $session, 'frame' => $frame]);
    })->name('capture');

    // Endpoint Upload Media
    Route::post('/session/{session}/upload', [MediaUploadController::class, 'upload'])->name('session.upload');

    // Halaman Hasil (QR Code)
    Route::get('/session/{session}/result', function(BoothSession $session) {
    $session->load(['frame', 'media']);

    $composite = $session->media->firstWhere('type', 'composite_photo');
    $livePhotos = $session->media->where('type', 'pose_live')->sortBy('pose_number')->values();

    // Pastikan menggunakan kolom token yang ada di tabel booth_sessions (misal gallery_token, session_code, atau id)
    $tokenValue = $session->gallery_token ?? $session->session_code ?? $session->id;

    return Inertia::render('Kiosk/Result', [
        'session' => $session,
        'compositePhoto' => $composite ? $composite->file_path : null,
        'livePhotos' => $livePhotos,
        'galleryUrl' => url('/gallery/' . $tokenValue),
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
Route::get('/gallery/{token?}', [GalleryController::class, 'show'])->name('gallery.show');

Route::get('/', function () {
    return redirect()->route('kiosk.home');
});

// Tambahkan rute ini di dalam group middleware admin Anda
Route::get('/admin', function () {
    return Inertia::render('Admin/Index', [
        'stats' => [
            'frames_count' => Frame::count(),
            'vouchers_count' => Voucher::count(),
            // Nanti bisa ditambah session_count, dll
        ]
    ]);
})->name('admin.dashboard');

// Route Kelola Frame untuk Admin
Route::get('/admin/frames', [\App\Http\Controllers\Admin\FrameController::class, 'index'])->name('admin.frames.index');
Route::post('/admin/frames', [\App\Http\Controllers\Admin\FrameController::class, 'store'])->name('admin.frames.store');
Route::delete('/admin/frames/{id}', [\App\Http\Controllers\Admin\FrameController::class, 'destroy'])->name('admin.frames.destroy');

Route::get('/admin/vouchers', [VoucherController::class, 'index'])->name('admin.vouchers.index');
Route::post('/admin/vouchers', [VoucherController::class, 'store'])->name('admin.vouchers.store');
Route::delete('/admin/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('admin.vouchers.destroy');
Route::patch('/admin/vouchers/{voucher}/toggle', [VoucherController::class, 'toggleActive'])->name('admin.vouchers.toggle');
