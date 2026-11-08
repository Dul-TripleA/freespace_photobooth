<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BoothSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function show(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            abort(404, 'Token galeri tidak valid.');
        }

        // Cari sesi berdasarkan token beserta relasi medianya
        $session = BoothSession::with('media')
            ->where('gallery_token', $token)
            ->firstOrFail();

        // Pisahkan foto komposit dan foto mentah untuk ditampilkan di frontend
        $compositePhoto = $session->media->where('type', 'composite_photo')->first();
        $rawPhotos = $session->media->where('type', 'raw_photo')->values();

        return Inertia::render('Public/Gallery', [
            'session' => $session,
            'compositePhoto' => $compositePhoto,
            'rawPhotos' => $rawPhotos
        ]);
    }
}
