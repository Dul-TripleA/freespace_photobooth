<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\BoothSession;
use App\Models\Frame;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SessionController extends Controller
{
    // Menampilkan halaman awal Kiosk (Pilih Metode Pembayaran)
    public function index()
    {
        return Inertia::render('Kiosk/Home');
    }

    // Memproses saat pengunjung mengklik tombol "Mulai"
    public function start(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:cashless,voucher,online',
            'voucher_code' => 'required_if:payment_method,voucher|string|nullable'
        ]);

        // Jika menggunakan voucher, validasi kodenya
        if ($validated['payment_method'] === 'voucher') {
            $voucher = Voucher::where('code', $validated['voucher_code'])
                ->where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->whereRaw('used_count < max_uses')
                ->first();

            if (!$voucher) {
                return back()->withErrors(['voucher_code' => 'Kode voucher tidak valid atau sudah habis.']);
            }

            // Tambah hitungan penggunaan voucher
            $voucher->increment('used_count');
        }

        // Buat sesi photobooth baru di database
        $session = BoothSession::create([
            'session_code' => 'SS-' . strtoupper(Str::random(8)),
            'gallery_token' => hash('sha256', Str::random(40) . time()),
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_method'] === 'online' ? 'pending' : 'paid',
        ]);

        // Arahkan ke halaman pemilihan frame
        return redirect()->route('kiosk.frame.index', ['session' => $session->id]);
    }

    // Menampilkan halaman pilih frame
    public function frameIndex(BoothSession $session)
    {
        // Ambil semua frame yang aktif untuk ditampilkan ke pengunjung
        $frames = Frame::where('is_active', true)->get();
        
        return Inertia::render('Kiosk/SelectFrame', [
            'session' => $session,
            'frames' => $frames
        ]);
    }

    // Memproses saat pengunjung memilih satu frame
    public function selectFrame(Request $request, BoothSession $session)
    {
        $validated = $request->validate([
            'frame_id' => 'required|exists:frames,id'
        ]);

        $session->update([
            'frame_id' => $validated['frame_id']
        ]);

        // Lanjut ke halaman kamera (Capture)
        return redirect()->route('kiosk.capture', ['session' => $session->id]);
    }

    public function result(BoothSession $session)
    {
        // Muat relasi frame dan media sekaligus
        $session->load(['frame', 'media']);

        $composite = $session->media->firstWhere('type', 'composite_photo');
        $livePhotos = $session->media->where('type', 'pose_live')->sortBy('pose_number')->values();

        return Inertia::render('Kiosk/Result', [
            'session' => $session,
            'compositePhoto' => $composite ? $composite->file_path : null,
            'livePhotos' => $livePhotos,
        ]);
    }
}
