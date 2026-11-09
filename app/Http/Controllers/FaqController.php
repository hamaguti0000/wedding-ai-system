<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\WeddingSetting;

class FaqController extends Controller
{
    public function index()
    {
        $faqs    = Faq::where('is_active', true)->orderBy('display_order')->orderBy('id')->get();
        $setting = WeddingSetting::first();
        return view('faq', compact('faqs', 'setting'));
    }
}
