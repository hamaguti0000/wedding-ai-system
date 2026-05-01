<?php

namespace App\Http\Controllers;

use App\Models\ProgramItem;
use App\Models\WeddingSetting;

class ProgramController extends Controller
{
    public function index()
    {
        $items   = ProgramItem::orderBy('display_order')->orderBy('id')->get();
        $setting = WeddingSetting::first();
        return view('program', compact('items', 'setting'));
    }
}
