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

    public function start(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'voucher_code' => 'required_if:payment_method,voucher|nullable|string'
        ]);

        $voucherId = null;

        if ($request->payment_method === 'voucher') {
            $voucher = Voucher::where('code', strtoupper($request->voucher_code))
                ->where('is_active', true)
                ->first();

            if (!$voucher) return back()->withErrors(['voucher_code' => 'Kode voucher tidak valid.']);
            if ($voucher->expires_at && now()->greaterThan($voucher->expires_at)) return back()->withErrors(['voucher_code' => 'Voucher kedaluwarsa.']);
            if ($voucher->quota !== null && $voucher->used_count >= $voucher->quota) return back()->withErrors(['voucher_code' => 'Kuota voucher habis.']);

            $voucherId = $voucher->id;
        }
        $request->validate([
            'payment_method' => 'required|string|in:cashless,online,online_pay,voucher',
            'voucher_code' => 'required_if:payment_method,voucher|nullable|string',
        ]);

        $isOnlinePay = in_array($request->payment_method, ['online', 'online_pay']);
        $voucherId = null;
        $amount = 0;

        if ($isOnlinePay) {
            $basePrice = config('services.static_qris.price', 25000);

            $activeAmounts = BoothSession::where('payment_status', 'unpaid')
                ->where('created_at', '>=', now()->subMinutes(20))
                ->pluck('amount')
                ->toArray();

            do {
                $uniqueCode = random_int(1, 999);
                $amount = $basePrice + $uniqueCode;
            } while (in_array($amount, $activeAmounts));
        } elseif ($request->payment_method === 'voucher') {
            $voucher = Voucher::where('code', strtoupper($request->voucher_code))
                ->where('is_active', true)
                ->first();

            if (!$voucher) return back()->withErrors(['voucher_code' => 'Kode voucher tidak valid.']);
            if ($voucher->expires_at && now()->greaterThan($voucher->expires_at)) return back()->withErrors(['voucher_code' => 'Voucher kedaluwarsa.']);
            if ($voucher->quota !== null && $voucher->used_count >= $voucher->quota) return back()->withErrors(['voucher_code' => 'Kuota voucher habis.']);

            $voucherId = $voucher->id;
        }

        $session = BoothSession::create([
            'session_code' => strtoupper(Str::random(8)),
            'invoice_number' => $isOnlinePay ? 'QR-' . strtoupper(Str::random(8)) : null,
            'gallery_token' => Str::random(12),
            'payment_method' => $request->payment_method,
            'payment_status' => $isOnlinePay ? 'pending' : 'paid',
            'amount' => $amount,
            'voucher_id' => $voucherId,
            'paid_at' => $isOnlinePay ? null : now(),
        ]);

        if ($isOnlinePay) {
            return redirect()->route('kiosk.payment.show', ['session' => $session->id]);
        }

        return redirect()->route('kiosk.frame.index', ['session' => $session->id]);
    }

    // Menampilkan halaman pilih frame
    public function frameIndex(BoothSession $session)
    {
        // Load relasi voucher beserta frame yang ditautkan kepadanya
        $session->load('voucher.frames');

        // Cek apakah user menggunakan Voucher dan vouchernya ada
        if ($session->payment_method === 'voucher' && $session->voucher) {

            // JIKA VOUCHER PUNYA FRAME KHUSUS = VOUCHER EKSKLUSIF
            if ($session->voucher->frames->count() > 0) {
                // HANYA tampilkan frame yang ditautkan ke voucher ini (Frame publik disembunyikan)
                $frames = $session->voucher->frames;
            }
            // JIKA VOUCHER TIDAK PUNYA FRAME KHUSUS = VOUCHER BIASA / DISKON REGULER
            else {
                // HANYA tampilkan frame publik (Frame eksklusif disembunyikan)
                $frames = Frame::where('is_public', true)->get();
            }
        }
        // JIKA MENGGUNAKAN CASHLESS / ONLINE PAY
        else {
            // HANYA tampilkan frame publik
            $frames = Frame::where('is_public', true)->get();
        }

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
