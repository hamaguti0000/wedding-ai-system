<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    public function index()
    {
        $photos = GalleryPhoto::where('is_active', true)
            ->where('status', 'approved')
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('gallery', compact('photos'));
    }

    public function uploadForm()
    {
        return view('gallery-upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'photos'     => 'required|array|max:10',
            'photos.*'   => 'required|image|mimes:jpeg,png,webp|max:10240',
            'message'    => 'nullable|string|max:500',
        ], [
            'photos.required'  => '写真を選択してください',
            'photos.*.image'   => '画像ファイルを選択してください',
            'photos.*.max'     => '1枚10MB以内にしてください',
            'photos.max'       => '一度に最大10枚までアップロードできます',
        ]);

        $maxOrder = GalleryPhoto::max('sort_order') ?? 0;
        $count    = 0;

        foreach ($request->file('photos') as $i => $file) {
            $path = $file->store('gallery/guest', 'public');
            GalleryPhoto::create([
                'file_path'           => $path,
                'caption'             => $request->message ?: null,
                'sort_order'          => $maxOrder + $count + 1,
                'is_active'           => false,
                'uploaded_by_user_id' => Auth::id(),
                'status'              => 'pending',
                'is_guest_upload'     => true,
            ]);
            $count++;
        }

        return back()->with('success', "{$count}枚の写真を投稿しました！管理者の確認後に公開されます🎉");
    }
}
