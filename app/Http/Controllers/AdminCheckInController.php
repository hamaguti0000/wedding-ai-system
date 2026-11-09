<?php

namespace App\Http\Controllers;

use App\Models\GuestProfile;
use App\Models\CheckInAuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AdminCheckInController extends Controller
{
    public function index(): View
    {
        return view('admin.checkin', $this->pageData());
    }

    public function guests(Request $request): View
    {
        return view('admin.checkin-guests', $this->guestListData($request));
    }

    public function show(string $token): View
    {
        $profile = $this->findProfile($token);

        return view('admin.checkin', $this->pageData($profile));
    }

    public function store(Request $request, ?string $token = null): JsonResponse|RedirectResponse
    {
        $rawToken = $token ?: (string) $request->input('token', '');
        $token = $this->normalizeToken($rawToken);

        if ($token === '') {
            return $this->respond($request, false, 'QRコードの値が見つかりませんでした');
        }

        $profile = $this->findProfile($token);

        if (! $profile) {
            return $this->respond($request, false, 'このQRコードに対応するゲストが見つかりませんでした');
        }

        if ($profile->isCheckedIn()) {
            $profile->load(['user.seatAssignment.seat.seatingTable', 'checkedInBy']);

            return $this->respond($request, true, 'すでに受付済みです', $profile);
        }

        $profile->forceFill([
            'checked_in_at' => now(),
            'checked_in_by_user_id' => auth()->id(),
        ])->save();

        $this->logCheckInAction($profile, 'check_in', 'qr', 'QRから受付済みにしました', [
            'token' => $token,
        ]);

        $profile->load(['user.seatAssignment.seat.seatingTable', 'checkedInBy']);

        return $this->respond($request, true, '受付済みにしました', $profile);
    }

    public function checkInGuest(Request $request, GuestProfile $guestProfile): JsonResponse|RedirectResponse
    {
        $guestProfile->loadMissing(['user.seatAssignment.seat.seatingTable', 'checkedInBy']);

        if ($guestProfile->isCheckedIn()) {
            return $this->respond($request, true, 'すでに受付済みです', $guestProfile);
        }

        $guestProfile->forceFill([
            'checked_in_at' => now(),
            'checked_in_by_user_id' => auth()->id(),
        ])->save();

        $this->logCheckInAction($guestProfile, 'check_in', 'manual', '受付一覧から受付済みにしました');

        $guestProfile->load(['user.seatAssignment.seat.seatingTable', 'checkedInBy']);

        return $this->respond($request, true, '受付済みにしました', $guestProfile);
    }

    public function cancelGuestCheckIn(Request $request, GuestProfile $guestProfile): JsonResponse|RedirectResponse
    {
        $guestProfile->forceFill([
            'checked_in_at' => null,
            'checked_in_by_user_id' => null,
        ])->save();

        $this->logCheckInAction($guestProfile, 'cancel', 'manual', '受付を取り消しました');

        $guestProfile->load(['user.seatAssignment.seat.seatingTable', 'checkedInBy']);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '受付を取り消しました',
                'profile' => $this->profilePayload($guestProfile),
            ]);
        }

        return redirect()
            ->route('admin.checkin.guests')
            ->with('success', '受付を取り消しました');
    }

    private function pageData(?GuestProfile $focusProfile = null): array
    {
        $guests = User::where('role', 'guest')
            ->with(['guestProfile', 'seatAssignment.seat.seatingTable'])
            ->orderBy('created_at')
            ->get();

        $checkedInCount = GuestProfile::whereNotNull('checked_in_at')->count();
        $checkedInProfiles = GuestProfile::with(['user.seatAssignment.seat.seatingTable', 'checkedInBy'])
            ->whereNotNull('checked_in_at')
            ->orderByDesc('checked_in_at')
            ->limit(20)
            ->get();

        $summary = [
            'total'             => $guests->count(),
            'checked_in'        => $checkedInCount,
            'attending'         => $guests->filter(fn ($u) => $u->guestProfile?->participation === 'attending')->count(),
            'remaining'         => $guests->filter(fn ($u) => $u->guestProfile?->participation === 'attending')
                ->filter(fn ($u) => ! $u->guestProfile?->isCheckedIn())
                ->count(),
        ];

        return compact('summary', 'checkedInProfiles', 'focusProfile');
    }

    private function logCheckInAction(GuestProfile $profile, string $action, ?string $source, string $message, array $meta = []): void
    {
        CheckInAuditLog::record($profile, auth()->user(), $action, $source, $message, $meta);
    }

    private function guestListData(Request $request): array
    {
        $status = (string) $request->string('status', 'checked_in');
        $query = trim((string) $request->string('q', ''));

        $base = GuestProfile::with(['user.seatAssignment.seat.seatingTable', 'checkedInBy'])
            ->whereHas('user', fn ($q) => $q->where('role', 'guest'));

        if ($query !== '') {
            $base->where(function ($q) use ($query) {
                $q->where('last_name', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('furigana_sei', 'like', "%{$query}%")
                    ->orWhere('furigana_mei', 'like', "%{$query}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('username', 'like', "%{$query}%"));
            });
        }

        $filtered = match ($status) {
            'all' => clone $base,
            'pending' => (clone $base)->whereNull('checked_in_at'),
            'attending' => (clone $base)->where('participation', 'attending'),
            'declining' => (clone $base)->where('participation', 'declining'),
            default => (clone $base)->whereNotNull('checked_in_at'),
        };

        $guests = $filtered
            ->orderByRaw("CASE WHEN checked_in_at IS NULL THEN 1 ELSE 0 END")
            ->orderBy('checked_in_at', 'desc')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $summary = [
            'total' => GuestProfile::count(),
            'checked_in' => GuestProfile::whereNotNull('checked_in_at')->count(),
            'pending' => GuestProfile::whereNull('checked_in_at')->count(),
            'attending' => GuestProfile::where('participation', 'attending')->count(),
        ];

        return [
            'summary' => $summary,
            'guests' => $guests,
            'status' => $status,
            'q' => $query,
            'statusLabels' => [
                'all' => 'すべて',
                'checked_in' => '受付済み',
                'pending' => '未受付',
                'attending' => '出席回答',
                'declining' => '欠席回答',
            ],
        ];
    }

    private function findProfile(string $rawToken): ?GuestProfile
    {
        $token = $this->normalizeToken($rawToken);
        if ($token === '') {
            return null;
        }

        $profile = GuestProfile::with(['user.seatAssignment.seat.seatingTable', 'checkedInBy'])
            ->where('checkin_token', $token)
            ->first();

        if ($profile) {
            $profile->ensureCheckInToken();
        }

        return $profile;
    }

    private function normalizeToken(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $value = basename(rtrim($path, '/'));
        } elseif (str_contains($value, '/')) {
            $value = basename(rtrim($value, '/'));
        }

        return trim($value);
    }

    private function respond(Request $request, bool $success, string $message, ?GuestProfile $profile = null): JsonResponse|RedirectResponse
    {
        $payload = [
            'success' => $success,
            'message' => $message,
            'profile' => $profile ? $this->profilePayload($profile) : null,
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload, $success ? 200 : 422);
        }

        if ($success && $profile) {
            return redirect()
                ->route('admin.checkin.show', $profile->checkin_token)
                ->with('success', $message);
        }

        return $success
            ? redirect()->route('admin.checkin.index')->with('success', $message)
            : back()->with('error', $message);
    }

    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ], [
            'file.required' => '画像がありません',
            'file.image' => '画像ファイルを選択してください',
            'file.max' => '画像は1MB以下にしてください',
        ]);

        $file = $request->file('file');
        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => '画像がありません',
            ], 422);
        }

        try {
            $response = Http::timeout(10)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post('https://api.qrserver.com/v1/read-qr-code/');
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'QRの読み取りに失敗しました',
            ], 422);
        }

        if (! $response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'QRの読み取りに失敗しました',
            ], 422);
        }

        $items = $response->json();
        $value = data_get($items, '0.symbol.0.data');

        if (! is_string($value) || trim($value) === '') {
            return response()->json([
                'success' => false,
                'message' => 'QRコードを読み取れませんでした',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'token' => $this->normalizeToken($value),
        ]);
    }

    private function profilePayload(GuestProfile $profile): array
    {
        $profile->loadMissing(['user.seatAssignment.seat.seatingTable', 'checkedInBy']);

        return [
            'id'        => $profile->id,
            'name'      => $profile->fullName(),
            'username'  => $profile->user?->username,
            'status'    => $profile->participationLabel(),
            'checked'   => $profile->isCheckedIn(),
            'checked_at'=> optional($profile->checked_in_at)->format('Y/m/d H:i'),
            'table'     => $profile->user?->seatAssignment?->seat?->seatingTable?->name,
            'seat'      => $profile->user?->seatAssignment?->seat?->label,
            'by'        => $profile->checkedInBy?->name,
        ];
    }
}
