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

    public function show(int $id)
    {
        $item = NewsItem::where('is_active', true)->findOrFail($id);

        $prev = NewsItem::where('is_active', true)
            ->where('id', '<', $id)
            ->orderByDesc('id')->first();

        $next = NewsItem::where('is_active', true)
            ->where('id', '>', $id)
            ->orderBy('id')->first();

        return view('news-show', compact('item', 'prev', 'next'));
    }
}
