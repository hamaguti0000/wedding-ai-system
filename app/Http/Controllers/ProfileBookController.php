<?php

namespace App\Http\Controllers;

use App\Models\ProfileBookPage;
use App\Models\WeddingSetting;
use Illuminate\Support\Facades\Auth;

class ProfileBookController extends Controller
{
    public function index()
    {
        $setting = WeddingSetting::first();
        $isAdmin = Auth::user()?->isAdmin() ?? false;

        // 未公開の場合はホームへリダイレクト（席次表の is_seating_public と同じ考え方）。
        // ただし管理者は公開前でも実際の見え方をプレビューできる必要があるため対象外。
        if (! $isAdmin && $setting !== null && ! $setting->is_profile_book_public) {
            return redirect()->route('dashboard');
        }

        $pages = ProfileBookPage::orderBy('page_number')->get();

        return view('profile-book', compact('pages'));
    }
}
