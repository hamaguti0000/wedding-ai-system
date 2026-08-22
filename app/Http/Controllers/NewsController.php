<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;

class NewsController extends Controller
{
    public function index()
    {
        $news = NewsItem::where('is_active', true)
            ->publicOrder()
            ->get();

        return view('news', compact('news'));
    }

    public function show(int $id)
    {
        $item = NewsItem::where('is_active', true)->findOrFail($id);

        $prev = NewsItem::where('is_active', true)
            ->where(function ($query) use ($item) {
                $query->where('published_date', '>', $item->published_date)
                    ->orWhere(function ($sameDate) use ($item) {
                        $sameDate->whereDate('published_date', $item->published_date)
                            ->where('id', '>', $item->id);
                    });
            })
            ->orderBy('published_date')
            ->orderBy('id')
            ->first();

        $next = NewsItem::where('is_active', true)
            ->where(function ($query) use ($item) {
                $query->where('published_date', '<', $item->published_date)
                    ->orWhere(function ($sameDate) use ($item) {
                        $sameDate->whereDate('published_date', $item->published_date)
                            ->where('id', '<', $item->id);
                    });
            })
            ->publicOrder()
            ->first();

        return view('news-show', compact('item', 'prev', 'next'));
    }
}
