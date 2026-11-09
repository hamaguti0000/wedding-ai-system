<?php

namespace App\Http\Controllers;

use App\Models\ProgramItem;
use App\Models\WeddingSetting;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    public function index()
    {
        $setting = WeddingSetting::first();
        $user    = Auth::user()->load('taskAssignments.task.programItems');

        // 割り当て役割の中にプログラム項目があればそれを使う
        $roleItems = $user->taskAssignments
            ->filter(fn($a) => $a->task !== null)
            ->flatMap(fn($a) => $a->task->programItems)
            ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
            ->values();

        if ($roleItems->isNotEmpty()) {
            $items         = $roleItems;
            $isRoleProgram = true;
            $roleNames     = $user->taskAssignments
                ->map(fn($a) => $a->task?->name)->filter()->values();
        } else {
            $items         = ProgramItem::orderBy('display_order')->orderBy('id')->get();
            $isRoleProgram = false;
            $roleNames     = collect();
        }

        return view('program', compact('items', 'setting', 'isRoleProgram', 'roleNames'));
    }
}
