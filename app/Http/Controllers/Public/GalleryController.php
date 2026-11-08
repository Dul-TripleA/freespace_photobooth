<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BoothSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function show(Request $request, $token = null)
    {
        $token = $token ?? $request->query('token');

        $columns = Schema::getColumnListing('booth_sessions');

        $session = BoothSession::with('media')
            ->where(function ($q) use ($columns, $token) {
                foreach (['gallery_token', 'token', 'qr_token', 'access_token', 'session_code', 'uuid'] as $col) {
                    if (in_array($col, $columns)) {
                        $q->orWhere($col, $token);
                    }
                }
            })
            ->firstOrFail();

        $formatMedia = function ($item) {
            if (!$item) return null;

            $data = $item->toArray();
            $rawPath = $data['url']
                ?? $data['file_url']
                ?? $data['public_url']
                ?? $data['file_path']
                ?? $data['storage_path']
                ?? $data['path']
                ?? $data['filename']
                ?? null;

            $url = null;
            if ($rawPath) {
                if (str_starts_with($rawPath, 'http://') || str_starts_with($rawPath, 'https://') || str_starts_with($rawPath, '/storage/')) {
                    $url = $rawPath;
                } else {
                    $disk = $data['disk'] ?? config('filesystems.default');
                    try {
                        $url = Storage::disk($disk)->url($rawPath);
                    } catch (\Throwable $e) {
                        $url = Storage::url($rawPath);
                    }
                }
            }

            return [
                'id' => $item->id,
                'type' => $data['type'] ?? $data['media_type'] ?? $data['category'] ?? null,
                'url' => $url,
            ];
        };

        $media = $session->media ?? collect();

        // Deteksi fleksibel untuk Composite/Strip, GIF, dan Raw Photos
        $compositeItem = $media->first(function ($m) {
            $t = strtolower($m->type ?? $m->media_type ?? $m->category ?? '');
            return str_contains($t, 'comp') || str_contains($t, 'strip') || str_contains($t, 'frame') || str_contains($t, 'final') || str_contains($t, 'print');
        });

        $gifItem = $media->first(function ($m) {
            $t = strtolower($m->type ?? $m->media_type ?? $m->category ?? '');
            $p = strtolower($m->file_path ?? $m->path ?? $m->url ?? '');
            return str_contains($t, 'gif') || str_contains($t, 'anim') || str_ends_with($p, '.gif');
        });

        $rawItems = $media->filter(function ($m) use ($compositeItem, $gifItem) {
            if ($compositeItem && $m->id === $compositeItem->id) return false;
            if ($gifItem && $m->id === $gifItem->id) return false;
            $t = strtolower($m->type ?? $m->media_type ?? $m->category ?? '');
            return str_contains($t, 'raw') || str_contains($t, 'orig') || str_contains($t, 'pose') || str_contains($t, 'photo') || str_contains($t, 'single') || $t === '';
        });

        // Fallback jika composite disimpan langsung di kolom tabel booth_sessions
        $sessionData = $session->toArray();
        $sessionCompositeUrl = $sessionData['composite_url']
            ?? $sessionData['strip_url']
            ?? (isset($sessionData['composite_path']) ? Storage::url($sessionData['composite_path']) : null)
            ?? (isset($sessionData['strip_path']) ? Storage::url($sessionData['strip_path']) : null);

        $sessionGifUrl = $sessionData['gif_url']
            ?? (isset($sessionData['gif_path']) ? Storage::url($sessionData['gif_path']) : null);

        return Inertia::render('Gallery', [
            'session' => $session,
            'compositePhoto' => $compositeItem ? $formatMedia($compositeItem) : ($sessionCompositeUrl ? ['url' => $sessionCompositeUrl] : null),
            'gifMedia' => $gifItem ? $formatMedia($gifItem) : ($sessionGifUrl ? ['url' => $sessionGifUrl] : null),
            'rawPhotos' => $rawItems->map($formatMedia)->values(),
        ]);
    }
}