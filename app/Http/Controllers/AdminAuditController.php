<?php

namespace App\Http\Controllers;

use App\Models\CheckInAuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditController extends Controller
{
    public function index(Request $request): View
    {
        $action = (string) $request->string('action', 'all');
        if ($action === '') {
            $action = 'all';
        }

        $query = CheckInAuditLog::with(['guestProfile.user', 'actor'])->latest();

        if ($action === 'update') {
            $query->whereNotIn('action', ['check_in', 'cancel']);
        } elseif ($action !== 'all') {
            $query->where('action', $action);
        }

        $logs = $query->paginate(50)->withQueryString();

        $summary = [
            'total' => CheckInAuditLog::count(),
            'check_in' => CheckInAuditLog::where('action', 'check_in')->count(),
            'cancel' => CheckInAuditLog::where('action', 'cancel')->count(),
            'update' => CheckInAuditLog::whereNotIn('action', ['check_in', 'cancel'])->count(),
        ];

        return view('admin.audit', compact('logs', 'summary', 'action'));
    }
}
