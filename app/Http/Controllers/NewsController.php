<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;

class NewsController extends Controller
{
    public function index()
    {
        $news = NewsItem::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('published_date')
            ->get();

        return view('news', compact('news'));
    }
}
