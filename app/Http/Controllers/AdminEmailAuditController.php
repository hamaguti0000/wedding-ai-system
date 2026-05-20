<?php

namespace App\Http\Controllers;

use App\Models\EmailAuditLog;
use Illuminate\Http\Request;

class AdminEmailAuditController extends Controller
{
    public function index(Request $request)
    {
        $summary = [
            'total' => EmailAuditLog::count(),
            'set' => EmailAuditLog::whereIn('action', ['admin_set', 'user_set'])->count(),
            'update' => EmailAuditLog::whereIn('action', ['admin_update', 'user_update'])->count(),
            'delete' => EmailAuditLog::where('action', 'admin_delete')->count(),
        ];

        $logs = EmailAuditLog::with(['user', 'actor'])
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('admin.email-audit', compact('logs', 'summary'));
    }
}
