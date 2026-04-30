<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;

class AdminLoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = LoginHistory::with('user')->latest('created_at');

        if ($filter === 'success') {
            $query->where('status', 'success');
        } elseif ($filter === 'failed') {
            $query->where('status', 'failed');
        }

        $histories = $query->paginate(50)->withQueryString();

        $totalSuccess = LoginHistory::where('status', 'success')->count();
        $totalFailed  = LoginHistory::where('status', 'failed')->count();

        return view('admin.login-history', compact(
            'histories', 'filter', 'totalSuccess', 'totalFailed'
        ));
    }
}
