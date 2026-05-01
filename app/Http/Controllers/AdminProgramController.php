<?php

namespace App\Http\Controllers;

use App\Models\ProgramItem;
use App\Models\TaskProgramItem;
use App\Models\WeddingTask;
use Illuminate\Http\Request;

class AdminProgramController extends Controller
{
    public function index(Request $request)
    {
        $roles      = WeddingTask::orderBy('sort_order')->orderBy('id')
            ->withCount('programItems')->get();
        $roleId     = $request->integer('role_id', 0);

        if ($roleId > 0 && $roles->contains('id', $roleId)) {
            $selectedRole = $roles->find($roleId);
            $items        = TaskProgramItem::where('wedding_task_id', $roleId)
                ->orderBy('sort_order')->orderBy('id')->get();
        } else {
            $selectedRole = null;
            $roleId       = 0;
            $items        = ProgramItem::orderBy('display_order')->orderBy('id')->get();
        }

        return view('admin.program', compact('items', 'roles', 'selectedRole', 'roleId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_time'  => 'nullable|string|max:10',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:60',
        ]);

        $max = ProgramItem::max('display_order') ?? 0;
        ProgramItem::create([
            'start_time'    => $request->start_time ?: null,
            'title'         => $request->title,
            'description'   => $request->description,
            'icon'          => $request->icon ?: 'fa-circle',
            'display_order' => $max + 1,
        ]);

        return back()->with('success', '項目を追加しました');
    }

    public function update(Request $request, int $id)
    {
        $item = ProgramItem::findOrFail($id);
        $request->validate([
            'start_time'  => 'nullable|string|max:10',
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon'        => 'nullable|string|max:60',
        ]);
        $item->update([
            'start_time'  => $request->start_time ?: null,
            'title'       => $request->title,
            'description' => $request->description,
            'icon'        => $request->icon ?: 'fa-circle',
        ]);
        return back()->with('success', '更新しました');
    }

    public function moveUp(int $id)
    {
        $item = ProgramItem::findOrFail($id);
        $prev = ProgramItem::where('display_order', '<', $item->display_order)
            ->orderByDesc('display_order')->first();
        if ($prev) {
            [$item->display_order, $prev->display_order] = [$prev->display_order, $item->display_order];
            $item->save(); $prev->save();
        }
        return back();
    }

    public function moveDown(int $id)
    {
        $item = ProgramItem::findOrFail($id);
        $next = ProgramItem::where('display_order', '>', $item->display_order)
            ->orderBy('display_order')->first();
        if ($next) {
            [$item->display_order, $next->display_order] = [$next->display_order, $item->display_order];
            $item->save(); $next->save();
        }
        return back();
    }

    public function destroy(int $id)
    {
        ProgramItem::findOrFail($id)->delete();
        return back()->with('success', '削除しました');
    }
}
