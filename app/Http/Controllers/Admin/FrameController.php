<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FrameController extends Controller
{
    public function index()
    {
        $frames = DB::table('frames')->orderByDesc('created_at')->get()->map(function ($frame) {
            $path = $frame->overlay_path ?? null;
            $frame->preview_url = $path
                ? (str_starts_with($path, 'http') || str_starts_with($path, '/storage/') ? $path : Storage::url($path))
                : null;
            return $frame;
        });

        return Inertia::render('Admin/Frames/Index', [
            'frames' => $frames,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'total_captures' => 'required|integer|min:2|max:6',
            'image' => 'required|image|mimes:png|max:15360',
            'canvas_width' => 'required|integer|min:100',
            'canvas_height' => 'required|integer|min:100',
            'slots' => 'required|json',
        ]);

        $filename = Str::slug($request->name) . '-' . time() . '.png';
        $storedPath = $request->file('image')->storeAs('frames', $filename, 'public');

        $rawSlots = json_decode($request->slots, true) ?: [];
        $normalizedSlots = array_map(function ($slot, $index) {
            return [
                'x' => (int) ($slot['x'] ?? 0),
                'y' => (int) ($slot['y'] ?? 0),
                'pose' => (int) ($slot['pose'] ?? 1),
                'width' => (int) ($slot['width'] ?? 400),
                'height' => (int) ($slot['height'] ?? 300),
            ];
        }, $rawSlots, array_keys($rawSlots));

        DB::table('frames')->insert([
            'id' => (string) Str::uuid(),
            'name' => $request->name,
            'category' => strtolower($request->category),
            'overlay_path' => '/storage/' . $storedPath,
            'total_poses' => (int) $request->total_captures, // Menyimpan jumlah capture kamera
            'canvas_width' => (int) $request->canvas_width,
            'canvas_height' => (int) $request->canvas_height,
            'slots_config' => json_encode($normalizedSlots),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Frame baru dan konfigurasi capture berhasil disimpan!');
    }

    public function destroy($id)
    {
        $frame = DB::table('frames')->where('id', $id)->first();
        if ($frame) {
            $path = str_replace('/storage/', '', $frame->overlay_path ?? '');
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            DB::table('frames')->where('id', $id)->delete();
        }

        return redirect()->back()->with('success', 'Frame berhasil dihapus!');
    }
}
