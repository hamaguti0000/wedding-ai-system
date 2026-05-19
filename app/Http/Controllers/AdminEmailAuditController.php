<?php

namespace App\Http\Controllers;

use App\Models\EmailAuditLog;
use Illuminate\Http\Request;

class AdminEmailAuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = EmailAuditLog::with(['user', 'actor'])
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('admin.email-audit', compact('logs'));
    }
}
