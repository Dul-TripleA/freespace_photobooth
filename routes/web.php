<?php

use App\Http\Controllers\Admin\FrameController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\Kiosk\MediaUploadController;
use App\Http\Controllers\Kiosk\PaymentController;
use App\Http\Controllers\Kiosk\SessionController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Admin\CashlessCodeController;
use App\Models\BoothSession;
use App\Models\Frame;
use App\Models\Voucher;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. Halaman Awal (Animasi Photobooth & Tutorial)
Route::get('/', function () {
    return Inertia::render('Kiosk/Welcome', [
        'accessUrl' => route('kiosk.access'),
    ]);
})->name('kiosk.welcome');

// 2. Grup Route Kiosk
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    // Arahkan '/kiosk' ke '/' agar selalu melewati halaman Welcome terlebih dahulu
    Route::redirect('/', '/')->name('home');

    // Halaman Menu Utama (Access: Cashless / Voucher / Online Pay)
    Route::get('/access', [SessionController::class, 'index'])->name('access');
    Route::post('/session/start', [SessionController::class, 'start'])->name('session.start');

    // Pembayaran QRIS Dinamis
    Route::get('/payment/{session}', [PaymentController::class, 'show'])->name('payment.show');
    Route::get('/payment/{session}/status', [PaymentController::class, 'status'])->name('payment.status');

    // Pemilihan Frame
    Route::get('/session/{session}/frame', [SessionController::class, 'frameIndex'])->name('frame.index');
    Route::post('/session/{session}/frame', [SessionController::class, 'selectFrame'])->name('frame.select');

    // Halaman Capture Kamera
    Route::get('/session/{session}/capture', function (BoothSession $session) {
        $frame = Frame::find($session->frame_id);

        return Inertia::render('Kiosk/Capture', [
            'session' => $session,
            'frame' => $frame,
        ]);
    })->name('capture');

    // Endpoint Upload Media
    Route::post('/session/{session}/upload', [MediaUploadController::class, 'upload'])->name('session.upload');

    // Halaman Hasil (QR Code & Print)
    Route::get('/session/{session}/result', function (BoothSession $session) {
        $session->load(['frame', 'media']);

        $composite = $session->media->firstWhere('type', 'composite_photo');
        $livePhotos = $session->media->where('type', 'pose_live')->sortBy('pose_number')->values();
        $tokenValue = $session->gallery_token ?? $session->session_code ?? $session->id;

        return Inertia::render('Kiosk/Result', [
            'session' => $session,
            'compositePhoto' => $composite ? $composite->file_path : null,
            'livePhotos' => $livePhotos,
            'galleryUrl' => url('/gallery/' . $tokenValue),
        ]);
    })->name('result');
});

// 3. Webhook Pembayaran QRIS
Route::post('/webhooks/qris-static', [PaymentController::class, 'webhook'])->name('webhooks.qris.static');

// 4. Halaman Galeri Publik (Diakses dari HP saat scan QR Code)
Route::get('/gallery/{token?}', [GalleryController::class, 'show'])->name('gallery.show');

// 5. Route Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return Inertia::render('Admin/Index', [
            'stats' => [
                'frames_count' => Frame::count(),
                'vouchers_count' => Voucher::count(),
            ],
        ]);
    })->name('dashboard');

    // Kelola Frame
    Route::get('/frames', [FrameController::class, 'index'])->name('frames.index');
    Route::post('/frames', [FrameController::class, 'store'])->name('frames.store');
    Route::delete('/frames/{id}', [FrameController::class, 'destroy'])->name('frames.destroy');

    // Kelola Voucher
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
    Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');
    Route::patch('/vouchers/{voucher}/toggle', [VoucherController::class, 'toggleActive'])->name('vouchers.toggle');

    Route::get('/cashless-codes', [CashlessCodeController::class, 'index'])->name('cashless-codes.index');
    Route::post('/cashless-codes', [CashlessCodeController::class, 'store'])->name('cashless-codes.store');
    Route::delete('/cashless-codes/{cashlessCode}', [CashlessCodeController::class, 'destroy'])->name('cashless-codes.destroy');
});
