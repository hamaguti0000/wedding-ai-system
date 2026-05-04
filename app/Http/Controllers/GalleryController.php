<?php

namespace App\Http\Controllers;

use App\Models\GalleryPhoto;

class GalleryController extends Controller
{
    public function index()
    {
        $photos = GalleryPhoto::where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')->get();

        return view('gallery', compact('photos'));
    }
}
