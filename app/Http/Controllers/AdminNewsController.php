<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use Illuminate\Http\Request;

class AdminNewsController extends Controller
{
    public function index()
    {
        $news = NewsItem::orderBy('sort_order')->orderByDesc('published_date')->get();
        return view('admin.news', compact('news'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'published_date' => 'required|date',
            'tag'            => 'nullable|string|max:30',
            'body'           => 'required|string|max:500',
        ], [
            'published_date.required' => '日付は必須です',
            'body.required'           => '内容は必須です',
        ]);

        NewsItem::create([
            'published_date' => $request->published_date,
            'tag'            => $request->tag ?: null,
            'body'           => $request->body,
            'is_active'      => true,
            'sort_order'     => (NewsItem::max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('admin.news')->with('success', 'お知らせを追加しました');
    }

    public function update(Request $request, int $id)
    {
        $item = NewsItem::findOrFail($id);
        $request->validate([
            'published_date' => 'required|date',
            'tag'            => 'nullable|string|max:30',
            'body'           => 'required|string|max:500',
        ]);

        $item->update([
            'published_date' => $request->published_date,
            'tag'            => $request->tag ?: null,
            'body'           => $request->body,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.news')->with('success', 'お知らせを更新しました');
    }

    public function destroy(int $id)
    {
        NewsItem::findOrFail($id)->delete();
        return redirect()->route('admin.news')->with('success', 'お知らせを削除しました');
    }

    public function moveUp(int $id)
    {
        $item = NewsItem::findOrFail($id);
        $prev = NewsItem::where('sort_order', '<', $item->sort_order)
            ->orderByDesc('sort_order')->first();
        if ($prev) {
            [$item->sort_order, $prev->sort_order] = [$prev->sort_order, $item->sort_order];
            $item->save(); $prev->save();
        }
        return back();
    }

    public function moveDown(int $id)
    {
        $item = NewsItem::findOrFail($id);
        $next = NewsItem::where('sort_order', '>', $item->sort_order)
            ->orderBy('sort_order')->first();
        if ($next) {
            [$item->sort_order, $next->sort_order] = [$next->sort_order, $item->sort_order];
            $item->save(); $next->save();
        }
        return back();
    }
}
