<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminNewsController extends Controller
{
    private const LAYOUTS = ['text', 'top', 'left', 'right', 'hero', 'card'];

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
            'title'          => 'nullable|string|max:100',
            'body'           => 'required|string|max:1000',
            'image'          => 'nullable|image|mimes:jpeg,png,webp,gif|max:5120',
            'layout'         => 'nullable|in:' . implode(',', self::LAYOUTS),
        ], [
            'published_date.required' => '日付は必須です',
            'body.required'           => '内容は必須です',
            'image.image'             => '画像ファイルを選択してください',
            'image.max'               => '画像は5MB以内にしてください',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('news', 'public');
        }

        NewsItem::create([
            'published_date' => $request->published_date,
            'tag'            => $request->tag ?: null,
            'title'          => $request->title ?: null,
            'body'           => $request->body,
            'image_path'     => $imagePath,
            'layout'         => $request->layout ?? 'text',
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
            'title'          => 'nullable|string|max:100',
            'body'           => 'required|string|max:1000',
            'image'          => 'nullable|image|mimes:jpeg,png,webp,gif|max:5120',
            'layout'         => 'nullable|in:' . implode(',', self::LAYOUTS),
            'remove_image'   => 'nullable|boolean',
        ]);

        $imagePath = $item->image_path;

        if ($request->boolean('remove_image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('news', 'public');
        }

        $item->update([
            'published_date' => $request->published_date,
            'tag'            => $request->tag ?: null,
            'title'          => $request->title ?: null,
            'body'           => $request->body,
            'image_path'     => $imagePath,
            'layout'         => $request->layout ?? $item->layout,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.news')->with('success', 'お知らせを更新しました');
    }

    public function destroy(int $id)
    {
        $item = NewsItem::findOrFail($id);
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $item->delete();
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
