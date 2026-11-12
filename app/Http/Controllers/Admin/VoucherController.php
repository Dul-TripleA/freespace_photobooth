<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\Frame;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VoucherController extends Controller
{
    public function index()
    {
        // Ambil semua voucher beserta frame yang berelasi
        $vouchers = Voucher::with('frames')->latest()->get();
        // Ambil semua frame untuk ditampilkan di pilihan checkbox
        $frames = Frame::latest()->get();

        return Inertia::render('Admin/Vouchers/Index', [
            'vouchers' => $vouchers,
            'frames' => $frames
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:vouchers,code|max:50',
            'event_name' => 'nullable|string|max:255',
            'quota' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'frame_ids' => 'nullable|array', // Array ID frame yang dipilih
        ]);

        $voucher = Voucher::create([
            'code' => strtoupper(str_replace(' ', '', $request->code)), // Pastikan kode uppercase & tanpa spasi
            'event_name' => $request->event_name,
            'quota' => $request->quota,
            'expires_at' => $request->expires_at,
            'is_active' => true,
        ]);

        // Simpan relasi ke tabel pivot frame_voucher
        if ($request->has('frame_ids') && is_array($request->frame_ids)) {
            $voucher->frames()->sync($request->frame_ids);
        }

        return back();
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return back();
    }

    public function toggleActive(Voucher $voucher)
    {
        $voucher->update(['is_active' => !$voucher->is_active]);
        return back();
    }
}