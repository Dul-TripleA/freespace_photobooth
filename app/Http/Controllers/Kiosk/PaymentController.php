<?php

namespace App\Http\Controllers\Kiosk;

use App\Http\Controllers\Controller;
use App\Models\BoothSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\Log;

use App\Services\QrisConverter;

class PaymentController extends Controller
{
    public function show(BoothSession $session)
    {
        if ($session->payment_status === 'paid') {
            return redirect()->route('kiosk.frame.index', ['session' => $session->id]);
        }

        $staticQris = config('services.static_qris.raw_string');
        $dynamicQrisString = $staticQris
            ? QrisConverter::makeDynamic($staticQris, $session->amount)
            : null;

        return Inertia::render('Kiosk/Payment', [
            'session' => $session,
            'dynamicQrisString' => $dynamicQrisString,
        ]);
    }

    public function status(BoothSession $session)
    {
        return response()->json([
            'payment_status' => $session->payment_status,
            'redirect_url' => $session->payment_status === 'paid'
                ? route('kiosk.frame.index', ['session' => $session->id])
                : null,
        ]);
    }

    // Menerima HTTP POST dari pengirim eksternal
    public function webhook(Request $request)
    {

        Log::info('Webhook QRIS Masuk:', [
            'all' => $request->all(),
            'raw' => $request->getContent(),
            'token_header' => $request->header('X-Webhook-Token'),
        ]);

        $token = $request->header('X-Webhook-Token') ?: $request->query('token') ?: $request->input('token');
        $expected = config('services.static_qris.webhook_token');

        Log::info('Cek Token:', [
            'masuk' => $token,
            'server' => $expected,
        ]);

        if (empty($expected) || $token !== $expected) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Ambil nominal dari field 'amount' atau ekstrak dari teks 'message'
        $amount = $request->input('amount');

        if (!$amount && $request->filled('message')) {
            $cleanMessage = preg_replace('/[.,]00\b/', '', $request->input('message'));
            if (preg_match('/(?:Rp\.?\s?)?(\d{1,3}(?:[.,]\d{3})+|\d{2,})/i', $cleanMessage, $matches)) {
                $amount = (int) preg_replace('/[^\d]/', '', $matches[1]);
            }
        } else {
            $amount = (int) preg_replace('/[^\d]/', '', (string) $amount);
        }

        if ($amount <= 0) {
            return response()->json(['message' => 'Nominal tidak valid'], 422);
        }

        // 3. Cocokkan dengan sesi unpaid terbaru dalam 20 menit terakhir
        $session = BoothSession::where('payment_status', 'pending')
            ->where('amount', $amount)
            ->where('created_at', '>=', now()->subMinutes(20))
            ->latest()
            ->first();

        if (!$session) {
            return response()->json([
                'message' => 'Tidak ada sesi aktif yang cocok dengan nominal tersebut',
                'received_amount' => $amount,
            ], 404);
        }

        $cleanMessage = preg_replace('/[.,]00\b/', '', $request->input('message'));

        // 4. Tandai Lunas
        $session->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);



        return response()->json([
            'message' => 'Pembayaran terkonfirmasi',
            'session_id' => $session->id,
            'invoice_number' => $session->invoice_number,
            'amount' => $amount,
        ]);
    }
}
