<?php

namespace App\Http\Controllers;

use App\Models\WeddingSetting;

class MovieController extends Controller
{
    public function show()
    {
        $setting = WeddingSetting::first();

        return view('movies', compact('setting'));
    }
}
