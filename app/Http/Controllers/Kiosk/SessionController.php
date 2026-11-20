<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\BoothSession;
use App\Models\Frame;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;
use App\Models\CashlessCode;
use Illuminate\Support\Facades\DB;

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
            'payment_method' => 'required|string|in:cashless,online,online_pay,voucher',
            'voucher_code' => 'required_if:payment_method,voucher|nullable|string',
            'cashless_code' => 'required_if:payment_method,cashless|nullable|string',
        ], [
            'cashless_code.required_if' => 'Masukkan kode unik dari operator terlebih dahulu.',
            'voucher_code.required_if' => 'Masukkan kode voucher terlebih dahulu.',
        ]);

        $method = $request->input('payment_method');

        // 1. JALUR CASHLESS (Kode Unik Sekali Pakai)
        if ($method === 'cashless') {
            $inputCode = strtoupper(trim($request->input('cashless_code')));

            $session = DB::transaction(function () use ($inputCode) {
                $cashlessCode = CashlessCode::where('code', $inputCode)
                    ->lockForUpdate()
                    ->first();

                if (!$cashlessCode) {
                    throw ValidationException::withMessages([
                        'cashless_code' => 'Kode tidak ditemukan. Periksa kembali kode Anda.',
                    ]);
                }

                if ($cashlessCode->is_used) {
                    throw ValidationException::withMessages([
                        'cashless_code' => 'Kode ini sudah pernah digunakan.',
                    ]);
                }

                $session = BoothSession::create([
                    'session_code' => 'CSH-' . strtoupper(Str::random(8)),
                    'invoice_number' => null,
                    'gallery_token' => Str::random(12),
                    'payment_method' => 'cashless',
                    'payment_status' => 'paid',
                    'amount' => 0,
                    'voucher_id' => null,
                    'paid_at' => now(),
                ]);

                $cashlessCode->update([
                    'is_used' => true,
                    'used_at' => now(),
                    'booth_session_id' => $session->id,
                ]);

                return $session;
            });

            return redirect()->route('kiosk.frame.index', ['session' => $session->id]);
        }

        // 2. JALUR ONLINE PAY (QRIS Dinamis)
        if (in_array($method, ['online', 'online_pay'])) {
            $basePrice = config('services.static_qris.price', 25000);

            $activeAmounts = BoothSession::whereIn('payment_status', ['pending', 'unpaid'])
                ->where('created_at', '>=', now()->subMinutes(20))
                ->pluck('amount')
                ->toArray();

            do {
                $uniqueCode = random_int(1, 999);
                $amount = $basePrice + $uniqueCode;
            } while (in_array($amount, $activeAmounts));

            $session = BoothSession::create([
                'session_code' => strtoupper(Str::random(8)),
                'invoice_number' => 'QR-' . strtoupper(Str::random(8)),
                'gallery_token' => Str::random(12),
                'payment_method' => $method,
                'payment_status' => 'pending',
                'amount' => $amount,
                'voucher_id' => null,
                'paid_at' => null,
            ]);

            return redirect()->route('kiosk.payment.show', ['session' => $session->id]);
        }

        // 3. JALUR VOUCHER
        $voucherCode = strtoupper(trim($request->input('voucher_code')));
        $voucher = Voucher::where('code', $voucherCode)
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return back()->withErrors(['voucher_code' => 'Kode voucher tidak valid.']);
        }

        if ($voucher->expires_at && now()->greaterThan($voucher->expires_at)) {
            return back()->withErrors(['voucher_code' => 'Voucher kedaluwarsa.']);
        }

        if ($voucher->quota !== null && $voucher->used_count >= $voucher->quota) {
            return back()->withErrors(['voucher_code' => 'Kuota voucher habis.']);
        }

        $session = BoothSession::create([
            'session_code' => strtoupper(Str::random(8)),
            'invoice_number' => null,
            'gallery_token' => Str::random(12),
            'payment_method' => 'voucher',
            'payment_status' => 'paid',
            'amount' => 0,
            'voucher_id' => $voucher->id,
            'paid_at' => now(),
        ]);

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
