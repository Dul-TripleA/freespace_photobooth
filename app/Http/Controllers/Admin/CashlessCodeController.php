<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashlessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CashlessCodeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/CashlessCodes/Index', [
            'codes' => CashlessCode::latest()->take(50)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $qty = min((int) $request->input('quantity', 1), 20); // Maksimal 20 kode sekali generate

        for ($i = 0; $i < $qty; $i++) {
            do {
                // Format 6 karakter huruf besar & angka (tanpa huruf/angka rancu seperti O/0, I/1)
                $code = strtoupper(Str::random(6));
            } while (CashlessCode::where('code', $code)->exists());

            CashlessCode::create(['code' => $code]);
        }

        return back()->with('success', "{$qty} kode unik cashless berhasil dibuat.");
    }

    public function destroy(CashlessCode $cashlessCode)
    {
        $cashlessCode->delete();
        return back();
    }
}