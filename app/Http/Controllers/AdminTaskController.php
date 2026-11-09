<?php

namespace App\Http\Controllers;

use App\Models\TaskProgramItem;
use App\Models\WeddingTask;
use Illuminate\Http\Request;

class AdminTaskController extends Controller
{
    public function index()
    {
        $tasks = WeddingTask::orderBy('sort_order')->orderBy('id')
            ->with(['assignments.user', 'programItems'])
            ->get();

        return view('admin.tasks', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ], ['name.required' => '役割名は必須です']);

        WeddingTask::create([
            'name'            => $request->name,
            'description'     => $request->description ?: null,
            'display_on_home' => $request->boolean('display_on_home', true),
            'sort_order'      => (WeddingTask::max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('admin.tasks')->with('success', '役割を追加しました');
    }

    public function update(Request $request, int $id)
    {
        $task = WeddingTask::findOrFail($id);
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
        ]);

        $task->update([
            'name'            => $request->name,
            'description'     => $request->description ?: null,
            'display_on_home' => $request->boolean('display_on_home', true),
        ]);

        return redirect()->route('admin.tasks')->with('success', '役割を更新しました');
    }

    public function destroy(int $id)
    {
        WeddingTask::findOrFail($id)->delete();
        return redirect()->route('admin.tasks')->with('success', '役割を削除しました');
    }

    public function moveUp(int $id)
    {
        $task = WeddingTask::findOrFail($id);
        $prev = WeddingTask::where('sort_order', '<', $task->sort_order)
            ->orderByDesc('sort_order')->first();
        if ($prev) {
            [$task->sort_order, $prev->sort_order] = [$prev->sort_order, $task->sort_order];
            $task->save();
            $prev->save();
        }
        return back();
    }

    public function moveDown(int $id)
    {
        $task = WeddingTask::findOrFail($id);
        $next = WeddingTask::where('sort_order', '>', $task->sort_order)
            ->orderBy('sort_order')->first();
        if ($next) {
            [$task->sort_order, $next->sort_order] = [$next->sort_order, $task->sort_order];
            $task->save();
            $next->save();
        }
        return back();
    }

    /* ── タスク別プログラム項目 ── */

    public function storeProgram(Request $request, int $id)
    {
        $task = WeddingTask::findOrFail($id);
        $request->validate([
            'title'      => 'required|string|max:200',
            'start_time' => 'nullable|string|max:20',
            'description'=> 'nullable|string|max:500',
        ]);

        TaskProgramItem::create([
            'wedding_task_id' => $task->id,
            'start_time'      => $request->start_time ?: null,
            'title'           => $request->title,
            'description'     => $request->description ?: null,
            'sort_order'      => (TaskProgramItem::where('wedding_task_id', $id)->max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('admin.tasks')->with('success', 'プログラム項目を追加しました');
    }

    public function updateProgram(Request $request, int $id, int $pid)
    {
        $item = TaskProgramItem::where('wedding_task_id', $id)->findOrFail($pid);
        $request->validate([
            'title'      => 'required|string|max:200',
            'start_time' => 'nullable|string|max:20',
            'description'=> 'nullable|string|max:500',
        ]);

        $item->update([
            'start_time'  => $request->start_time ?: null,
            'title'       => $request->title,
            'description' => $request->description ?: null,
        ]);

        return redirect()->route('admin.tasks')->with('success', 'プログラム項目を更新しました');
    }

    public function destroyProgram(int $id, int $pid)
    {
        TaskProgramItem::where('wedding_task_id', $id)->findOrFail($pid)->delete();
        return redirect()->route('admin.tasks')->with('success', 'プログラム項目を削除しました');
    }

    public function moveProgramUp(int $id, int $pid)
    {
        $item = TaskProgramItem::where('wedding_task_id', $id)->findOrFail($pid);
        $prev = TaskProgramItem::where('wedding_task_id', $id)
            ->where('sort_order', '<', $item->sort_order)->orderByDesc('sort_order')->first();
        if ($prev) {
            [$item->sort_order, $prev->sort_order] = [$prev->sort_order, $item->sort_order];
            $item->save(); $prev->save();
        }
        return back();
    }

    public function moveProgramDown(int $id, int $pid)
    {
        $item = TaskProgramItem::where('wedding_task_id', $id)->findOrFail($pid);
        $next = TaskProgramItem::where('wedding_task_id', $id)
            ->where('sort_order', '>', $item->sort_order)->orderBy('sort_order')->first();
        if ($next) {
            [$item->sort_order, $next->sort_order] = [$next->sort_order, $item->sort_order];
            $item->save(); $next->save();
        }
        return back();
    }
}
