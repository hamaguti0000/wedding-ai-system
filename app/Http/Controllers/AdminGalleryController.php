<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminGalleryController extends Controller
{
    public function index()
    {
        $photos = GalleryPhoto::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.gallery', compact('photos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'photos'          => 'required|array|max:20',
            'photos.*'        => 'required|image|mimes:jpeg,png,webp,gif|max:10240',
            'captions'        => 'nullable|array',
            'captions.*'      => 'nullable|string|max:200',
        ], [
            'photos.required'   => '画像を選択してください',
            'photos.*.image'    => '画像ファイルを選択してください',
            'photos.*.max'      => '1枚10MB以内にしてください',
        ]);

        $maxOrder = GalleryPhoto::max('sort_order') ?? 0;
        $count = 0;

        foreach ($request->file('photos') as $i => $file) {
            $path = $file->store('gallery', 'public');
            GalleryPhoto::create([
                'file_path'  => $path,
                'caption'    => $request->captions[$i] ?? null,
                'sort_order' => $maxOrder + $count + 1,
                'is_active'  => true,
            ]);
            $count++;
        }

        return back()->with('success', "{$count}枚の写真を追加しました");
    }

    public function update(Request $request, int $id)
    {
        $photo = GalleryPhoto::findOrFail($id);
        $request->validate(['caption' => 'nullable|string|max:200']);

        $photo->update([
            'caption'   => $request->caption ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', '更新しました');
    }

    public function destroy(int $id)
    {
        $photo = GalleryPhoto::findOrFail($id);
        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();
        return back()->with('success', '削除しました');
    }

    public function moveUp(int $id)
    {
        $photo = GalleryPhoto::findOrFail($id);
        $prev  = GalleryPhoto::where('sort_order', '<', $photo->sort_order)
            ->orderByDesc('sort_order')->first();
        if ($prev) {
            [$photo->sort_order, $prev->sort_order] = [$prev->sort_order, $photo->sort_order];
            $photo->save(); $prev->save();
        }
        return back();
    }

    public function moveDown(int $id)
    {
        $photo = GalleryPhoto::findOrFail($id);
        $next  = GalleryPhoto::where('sort_order', '>', $photo->sort_order)
            ->orderBy('sort_order')->first();
        if ($next) {
            [$photo->sort_order, $next->sort_order] = [$next->sort_order, $photo->sort_order];
            $photo->save(); $next->save();
        }
        return back();
    }
}
