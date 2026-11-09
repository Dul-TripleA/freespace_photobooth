<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BoothSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GalleryController extends Controller
{
    public function show(Request $request, $token = null)
    {
        $token = (string) ($token ?? $request->query('token'));

        $session = BoothSession::with('media')
            ->where(function ($q) use ($token) {
                $q->where('gallery_token', $token)
                    ->orWhere('session_code', $token);

                if (\Illuminate\Support\Str::isUuid($token)) {
                    $q->orWhere('id', $token);
                }
            })
            ->firstOrFail();

        $formatMedia = function ($item) {
            if (!$item) return null;

            $data = $item->toArray();
            $rawPath = $data['file_path'] ?? $data['url'] ?? $data['path'] ?? '';

            $url = '';
            if ($rawPath) {
                if (str_starts_with($rawPath, 'http') || str_starts_with($rawPath, '/storage/')) {
                    $url = $rawPath;
                } else {
                    $url = Storage::url($rawPath);
                }
            }

            return [
                'id' => $item->id,
                'type' => $data['type'] ?? '',
                'pose_number' => $data['pose_number'] ?? null,
                'url' => $url,
            ];
        };

        $media = $session->media ?? collect();

        $compositeItem = $media->first(fn($m) => in_array($m->type, ['composite_photo', 'composite', 'strip']));
        $gifItem = $media->first(fn($m) => in_array($m->type, ['gif', 'animation']));
        $rawItems = $media->filter(fn($m) => in_array($m->type, ['raw_photo', 'raw', 'pose']))->sortBy('pose_number');
        $liveItems = $media->filter(fn($m) => in_array($m->type, ['pose_live', 'live_photo', 'video']))->sortBy('pose_number');

        return Inertia::render('Gallery', [
            'session' => $session,
            'compositePhoto' => $formatMedia($compositeItem),
            'gifMedia' => $formatMedia($gifItem),
            'rawPhotos' => $rawItems->map($formatMedia)->values(),
            'livePhotos' => $liveItems->map($formatMedia)->values(),
        ]);
    }
}
