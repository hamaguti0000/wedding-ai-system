<?php

namespace App\Http\Controllers;

use App\Models\ProfileBookPage;
use App\Models\WeddingSetting;

class ProfileBookController extends Controller
{
    public function index()
    {
        $setting = WeddingSetting::first();

        // 未公開の場合はホームへリダイレクト（席次表の is_seating_public と同じ考え方）
        if ($setting !== null && ! $setting->is_profile_book_public) {
            return redirect()->route('dashboard');
        }

        $pages = ProfileBookPage::orderBy('page_number')->get();

        return view('profile-book', compact('pages'));
    }
}
