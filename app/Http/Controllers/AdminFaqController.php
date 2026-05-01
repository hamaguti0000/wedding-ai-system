<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class AdminFaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::orderBy('display_order')->orderBy('id')->get();
        return view('admin.faq', compact('faqs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string|max:2000',
        ]);
        $max = Faq::max('display_order') ?? 0;
        Faq::create([
            'question'      => $request->question,
            'answer'        => $request->answer,
            'display_order' => $max + 1,
            'is_active'     => true,
        ]);
        return back()->with('success', 'Q&Aを追加しました');
    }

    public function update(Request $request, int $id)
    {
        $faq = Faq::findOrFail($id);
        $request->validate([
            'question'  => 'required|string|max:255',
            'answer'    => 'required|string|max:2000',
            'is_active' => 'boolean',
        ]);
        $faq->update([
            'question'  => $request->question,
            'answer'    => $request->answer,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return back()->with('success', '更新しました');
    }

    public function moveUp(int $id)
    {
        $faq  = Faq::findOrFail($id);
        $prev = Faq::where('display_order', '<', $faq->display_order)
            ->orderByDesc('display_order')->first();
        if ($prev) {
            [$faq->display_order, $prev->display_order] = [$prev->display_order, $faq->display_order];
            $faq->save(); $prev->save();
        }
        return back();
    }

    public function moveDown(int $id)
    {
        $faq  = Faq::findOrFail($id);
        $next = Faq::where('display_order', '>', $faq->display_order)
            ->orderBy('display_order')->first();
        if ($next) {
            [$faq->display_order, $next->display_order] = [$next->display_order, $faq->display_order];
            $faq->save(); $next->save();
        }
        return back();
    }

    public function destroy(int $id)
    {
        Faq::findOrFail($id)->delete();
        return back()->with('success', '削除しました');
    }
}
