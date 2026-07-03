<?php

namespace App\Http\Controllers;

use App\Models\ProfileBookPage;

class ProfileBookController extends Controller
{
    public function index()
    {
        $pages = ProfileBookPage::orderBy('page_number')->get();

        return view('profile-book', compact('pages'));
    }
}
