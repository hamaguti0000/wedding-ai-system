<?php

namespace App\Http\Controllers;

use App\Models\WeddingSetting;

class AccessController extends Controller
{
    public function index()
    {
        $setting = WeddingSetting::first();
        return view('access', compact('setting'));
    }
}
