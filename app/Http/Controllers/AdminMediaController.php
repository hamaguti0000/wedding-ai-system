<?php

namespace App\Http\Controllers;

use App\Models\SiteImage;
use App\Models\WeddingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AdminMediaController extends Controller
{
    public function index()
    {
        $counts = SiteImage::selectRaw('location, count(*) as total, sum(is_active) as active_count')
            ->groupBy('location')
            ->get()
            ->keyBy('location');

        $setting = WeddingSetting::first();

        return view('admin.media', [
            'locations' => SiteImage::LOCATIONS,
            'counts'    => $counts,
            'setting'   => $setting,
        ]);
    }

    public function show(string $location)
    {
        abort_if(!array_key_exists($location, SiteImage::LOCATIONS), 404);

        $images  = SiteImage::forLocation($location)->ordered()->get();
        $setting = WeddingSetting::first();
        $modes   = $setting?->image_display_modes ?? [];

        return view('admin.media-location', [
            'location'    => $location,
            'label'       => SiteImage::LOCATIONS[$location],
            'images'      => $images,
            'setting'     => $setting,
            'currentMode' => $modes[$location] ?? 'random',
        ]);
    }

    public function store(Request $request, string $location)
    {
        abort_if(!array_key_exists($location, SiteImage::LOCATIONS), 404);

        $request->validate(['image' => 'required|image|max:10240']);

        $path     = $request->file('image')->store('site-media/' . $location, 'public');
        $maxOrder = SiteImage::forLocation($location)->max('display_order') ?? -1;

        SiteImage::create([
            'location'      => $location,
            'image_path'    => $path,
            'caption'       => $request->caption,
            'display_order' => $maxOrder + 1,
            'is_active'     => true,
        ]);

        return back()->with('success', '画像をアップロードしました');
    }

    public function destroy(int $id)
    {
        $image = SiteImage::findOrFail($id);
        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', '削除しました');
    }

    public function toggle(int $id)
    {
        $image = SiteImage::findOrFail($id);
        $image->update(['is_active' => !$image->is_active]);

        return back()->with('success', '表示設定を変更しました');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->order as $i => $id) {
            SiteImage::where('id', $id)->update(['display_order' => $i]);
        }

        return response()->json(['ok' => true]);
    }

    public function setMode(Request $request, string $location)
    {
        abort_if(!array_key_exists($location, SiteImage::LOCATIONS), 404);
        $request->validate(['mode' => 'required|in:fixed,random']);

        $setting = WeddingSetting::firstOrNew([]);
        $modes   = $setting->image_display_modes ?? [];
        $modes[$location] = $request->mode;
        $setting->image_display_modes = $modes;
        $setting->save();
        Cache::forget('site_image_modes');

        return back()->with('success', '表示モードを変更しました');
    }

    public function setHeroType(Request $request)
    {
        $request->validate([
            'hero_type'     => 'required|in:slideshow,video',
            'hero_interval' => 'nullable|integer|min:2000|max:15000',
        ]);

        WeddingSetting::updateOrCreate([], [
            'hero_type'     => $request->hero_type,
            'hero_interval' => $request->hero_interval ?? 5000,
        ]);

        return back()->with('success', 'ヒーローの設定を変更しました');
    }

    public function uploadVideo(Request $request)
    {
        $request->validate(['video' => 'required|mimes:mp4,webm|max:204800']); // 200MB

        $setting = WeddingSetting::firstOrNew([]);

        if ($setting->hero_video_path) {
            Storage::disk('public')->delete($setting->hero_video_path);
        }

        $path = $request->file('video')->store('site-media/hero-video', 'public');
        $setting->hero_video_path = $path;
        $setting->save();

        return back()->with('success', '動画をアップロードしました');
    }

    public function deleteVideo()
    {
        $setting = WeddingSetting::first();
        if ($setting?->hero_video_path) {
            Storage::disk('public')->delete($setting->hero_video_path);
            $setting->update(['hero_video_path' => null]);
        }

        return back()->with('success', '動画を削除しました');
    }

    public function uploadEndingMovie(Request $request)
    {
        $request->validate(['video' => 'required|mimes:mp4,webm|max:307200']); // 300MB

        $setting = WeddingSetting::firstOrNew([]);

        if ($setting->ending_movie_path) {
            Storage::disk('public')->delete($setting->ending_movie_path);
        }

        $path = $request->file('video')->store('site-media/ending-movie', 'public');
        $setting->ending_movie_path = $path;
        $setting->save();

        return back()->with('success', 'エンディングムービーをアップロードしました');
    }

    public function deleteEndingMovie()
    {
        $setting = WeddingSetting::first();
        if ($setting?->ending_movie_path) {
            Storage::disk('public')->delete($setting->ending_movie_path);
            $setting->update(['ending_movie_path' => null]);
        }

        return back()->with('success', 'エンディングムービーを削除しました');
    }
}
