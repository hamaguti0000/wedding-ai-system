<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;

class AdminLoginHistoryController extends Controller
{
    public function index(Request $request)
    {
        $q      = trim((string)($request->get('q') ?: ''));
        $from   = (string)($request->get('from') ?: '');
        $to     = (string)($request->get('to')   ?: '');
        $status = (string)($request->get('status') ?: 'all');
        $side   = (string)($request->get('side')   ?: 'all');
        $role   = (string)($request->get('role')   ?: 'all');

        if ($status === '') $status = 'all';
        if ($side   === '') $side   = 'all';
        if ($role   === '') $role   = 'all';

        $query = LoginHistory::with('user.guestProfile')->latest('created_at');

        if ($q !== '') {
            $query->where(function ($historyQuery) use ($q) {
                $historyQuery->where('username', 'like', '%' . $q . '%')
                    ->orWhereHas('user.guestProfile', function ($profileQuery) use ($q) {
                        $profileQuery->where('last_name', 'like', '%' . $q . '%')
                            ->orWhere('first_name', 'like', '%' . $q . '%')
                            ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ['%' . $q . '%'])
                            ->orWhereRaw("CONCAT(last_name, first_name) LIKE ?", ['%' . $q . '%']);
                    })
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%' . $q . '%'));
            });
        }
        if ($from !== '' && strtotime($from) !== false) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to !== '' && strtotime($to) !== false) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($side !== 'all') {
            $sideValue = $side;
            $query->whereHas('user', function ($uq) use ($sideValue) {
                $uq->whereHas('guestProfile', fn($pq) => $pq->where('guest_side', $sideValue));
            });
        }
        if ($role !== 'all') {
            $roleValue = $role;
            $query->whereHas('user', fn($uq) => $uq->where('role', $roleValue));
        }

        $histories = $query->paginate(50)->withQueryString();

        $totalSuccess = LoginHistory::where('status', 'success')->count();
        $totalFailed  = LoginHistory::where('status', 'failed')->count();

        $hasFilter = $q !== '' || $from !== '' || $to !== ''
                     || $status !== 'all' || $side !== 'all' || $role !== 'all';

        return view('admin.login-history', compact(
            'histories', 'q', 'from', 'to', 'status', 'side', 'role',
            'totalSuccess', 'totalFailed', 'hasFilter'
        ));
    }
}
