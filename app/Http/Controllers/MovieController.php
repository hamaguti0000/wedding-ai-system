<?php

namespace App\Http\Controllers;

use App\Models\WeddingSetting;

class EndingMovieController extends Controller
{
    public function show()
    {
        $setting = WeddingSetting::first();

        return view('ending', compact('setting'));
    }
}
