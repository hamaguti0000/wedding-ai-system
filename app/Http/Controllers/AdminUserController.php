<?php

namespace App\Http\Controllers;

use App\Models\CheckInAuditLog;
use App\Models\EmailAuditLog;
use App\Models\GuestProfile;
use App\Models\GuestTaskAssignment;
use App\Models\User;
use App\Models\WeddingTask;
use App\Services\AvatarUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use RuntimeException;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with('guestProfile')->latest()->get();
        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'     => 'required|string|max:50|unique:users,username',
            'password'     => 'required|string|min:6|max:255',
            'email'        => 'nullable|string|email:rfc,filter|max:255|unique:users,email',
            'role'         => 'required|in:admin,guest',
            'avatar_type'  => ['nullable', Rule::in(array_keys(User::avatarTypeOptions()))],
            'avatar_emoji' => [
                Rule::requiredIf(fn () => $request->input('avatar_type') === User::AVATAR_EMOJI),
                'nullable',
                'string',
                Rule::in(array_keys(User::avatarEmojiOptions())),
            ],
            'avatar_bg_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'avatar_border_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'avatar_border_width' => [
                'nullable',
                'integer',
                'min:0',
                'max:10',
            ],
            'avatar_image' => [
                Rule::requiredIf(fn () => $request->input('avatar_type') === User::AVATAR_PHOTO),
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp,gif,heic,heif',
                'max:5120',
            ],
            'last_name'    => 'nullable|string|max:50',
            'first_name'   => 'nullable|string|max:50',
            'furigana_sei' => 'nullable|string|max:50',
            'furigana_mei' => 'nullable|string|max:50',
            'guest_side'   => 'nullable|in:groom,bride',
            'event_day'    => 'nullable|in:day1,day2',
            'relationship' => 'nullable|in:friend,family,colleague,other',
        ], [
            'username.required' => 'ユーザー名は必須です',
            'username.unique'   => 'このユーザー名はすでに使われています',
            'email.email'       => '正しいメールアドレスを入力してください',
            'email.max'         => 'メールアドレスは255文字以内で入力してください',
            'email.unique'      => 'このメールアドレスは既に登録されています',
            'password.required' => 'パスワードは必須です',
            'password.min'      => 'パスワードは6文字以上にしてください',
            'role.required'     => 'ロールを選択してください',
            'avatar_emoji.required' => '絵文字アイコンを選択してください',
            'avatar_emoji.in'       => '選択された絵文字アイコンは利用できません',
            'avatar_image.required' => '写真アイコンを使う場合は画像を選択してください',
            'avatar_image.mimes'    => '写真はJPEG・PNG・WEBP・GIF・HEIC形式のいずれかを選択してください',
            'avatar_image.max'      => '写真は5MB以下にしてください',
            'avatar_image.uploaded' => '写真のアップロードに失敗しました。ファイルサイズが大きすぎる可能性があります。時間をおいて再度お試しください',
            'avatar_bg_color.regex' => '背景色の形式が正しくありません',
            'avatar_border_color.regex' => '枠線色の形式が正しくありません',
            'avatar_border_width.integer' => '枠線の太さは数値で入力してください',
        ]);

        $fullName = trim(($request->last_name ?? '') . ' ' . ($request->first_name ?? ''));
        try {
            $avatarData = $this->buildAvatarData($request);
        } catch (RuntimeException $e) {
            return back()->withErrors(['avatar_image' => $e->getMessage()]);
        }

        $user = User::create([
            'name'     => $fullName ?: $request->username,
            'username' => $request->username,
            'email'    => $request->filled('email') ? trim($request->email) : null,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'email_verified_at' => $request->role === 'admin' && $request->filled('email') ? now() : null,
            'password_change_required' => $request->role === 'guest',
            'password_changed_at' => $request->role === 'admin' ? now() : null,
            ...$avatarData,
        ]);

        if ($user->email) {
            EmailAuditLog::record(
                $user,
                auth()->user(),
                'admin_set',
                null,
                $user->email,
                '管理者がメールアドレスを登録しました',
                ['role' => $user->role]
            );

            if (! $user->isAdmin()) {
                try {
                    $user->sendEmailVerification(force: true);
                } catch (\Throwable) {
                    // ユーザー作成はメール送信失敗で止めない
                }
            }
        }

        if ($request->role === 'guest') {
            $profile = GuestProfile::create([
                'user_id'       => $user->id,
                'last_name'     => $request->last_name,
                'first_name'    => $request->first_name,
                'furigana_sei'  => $request->furigana_sei,
                'furigana_mei'  => $request->furigana_mei,
                'guest_side'    => $request->guest_side ?: null,
                'event_day'     => $request->event_day ?: 'day2',
                'relationship'  => $request->relationship ?: null,
                'participation' => 'pending',
                'checkin_token' => (string) Str::uuid(),
            ]);

            $profile->ensureCheckInToken();

            CheckInAuditLog::record(
                $profile,
                auth()->user(),
                'user_create',
                'admin',
                '管理画面でゲストを作成しました',
                [
                    'user_id' => $user->id,
                ]
            );
        }

        return redirect()->route('admin.users')
            ->with('success', "「{$request->username}」を登録しました");
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'guest_csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'guest_csv.required' => 'CSVファイルを選択してください',
            'guest_csv.mimes' => 'CSVまたはテキストファイルを選択してください',
            'guest_csv.max' => 'CSVファイルは2MB以下にしてください',
        ]);

        $rows = $this->readGuestCsv($request->file('guest_csv')->getRealPath());
        if (count($rows) === 0) {
            throw ValidationException::withMessages([
                'guest_csv' => '登録できる行がありません',
            ]);
        }

        return view('admin.users-import-preview', [
            'rows' => $this->buildImportPreviewRows($rows),
        ]);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rows' => ['required', 'array', 'min:1'],
            'initial_password' => ['required', 'string', 'min:6', 'max:255'],
        ], [
            'rows.required' => '登録するゲストがありません',
            'initial_password.required' => '初期パスワードを入力してください',
            'initial_password.min' => '初期パスワードは6文字以上にしてください',
        ]);

        if ($validator->fails()) {
            $rows = $this->normalizeImportRows($request->input('rows', []));

            if (count($rows) === 0) {
                return redirect()->route('admin.users')
                    ->withErrors($validator)
                    ->withInput();
            }

            return view('admin.users-import-preview', [
                'rows' => $this->buildImportPreviewRows($rows),
            ])->withErrors($validator);
        }

        $rows = $this->normalizeImportRows($request->input('rows', []));
        $previewRows = $this->buildImportPreviewRows($rows);
        $hasErrors = collect($previewRows)->contains(fn ($row) => count($row['errors']) > 0);

        if ($hasErrors) {
            return view('admin.users-import-preview', [
                'rows' => $previewRows,
            ])->withErrors([
                'rows' => 'エラーのある行があります。ユーザー名の重複や空欄を修正してから登録してください。',
            ]);
        }

        $created = DB::transaction(function () use ($previewRows, $request) {
            $count = 0;

            foreach ($previewRows as $row) {
                $username = $this->cleanCsvValue($row['username'] ?? '');
                $lastName = $this->cleanCsvValue($row['last_name'] ?? '');
                $firstName = $this->cleanCsvValue($row['first_name'] ?? '');
                $fullName = trim($lastName . ' ' . $firstName);
                $relationshipDetail = $this->cleanCsvValue($row['relationship_detail'] ?? '');
                $notes = $this->cleanCsvValue($row['notes'] ?? '');

                $user = User::create([
                    'name' => $fullName ?: $username,
                    'username' => $username,
                    'email' => null,
                    'password' => Hash::make($request->initial_password),
                    'role' => 'guest',
                    'password_change_required' => true,
                    'password_changed_at' => null,
                    'avatar_type' => User::AVATAR_INITIAL,
                    'avatar_image_path' => null,
                    'avatar_bg_color' => null,
                    'avatar_border_color' => '#f0e4d0',
                    'avatar_border_width' => 3,
                ]);

                $profile = GuestProfile::create([
                    'user_id' => $user->id,
                    'last_name' => $lastName ?: null,
                    'first_name' => $firstName ?: null,
                    'guest_side' => $this->guestSideFromCsv($row),
                    'event_day' => 'day2',
                    'relationship' => $this->relationshipFromCsv($row),
                    'relationship_detail' => $relationshipDetail ?: null,
                    'participation' => 'pending',
                    'notes' => $notes ?: null,
                    'checkin_token' => (string) Str::uuid(),
                ]);

                $profile->ensureCheckInToken();

                CheckInAuditLog::record(
                    $profile,
                    auth()->user(),
                    'user_create',
                    'admin',
                    'CSV一括登録でゲストを作成しました',
                    ['user_id' => $user->id]
                );

                $count++;
            }

            return $count;
        });

        return redirect()->route('admin.users')
            ->with('success', "{$created}名のゲストをCSVから登録しました");
    }

    public function edit(int $id)
    {
        $user  = User::with(['guestProfile', 'taskAssignments'])->findOrFail($id);
        $tasks = WeddingTask::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.users-edit', compact('user', 'tasks'));
    }

    public function show(int $id)
    {
        $user = User::with([
            'guestProfile.checkedInBy',
            'seatAssignment.seat.seatingTable',
            'taskAssignments.task.programItems',
        ])->findOrFail($id);

        $user->guestProfile?->ensureCheckInToken();

        return view('admin.users-show', compact('user'));
    }

    public function qr(int $id)
    {
        $user = User::with([
            'guestProfile.checkedInBy',
            'seatAssignment.seat.seatingTable',
        ])->findOrFail($id);

        $user->guestProfile?->ensureCheckInToken();

        return view('admin.users-qr', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::with('guestProfile')->findOrFail($id);

        $request->validate([
            'username'            => 'required|string|max:50|unique:users,username,' . $id,
            'email'               => [
                'nullable',
                'string',
                'email:rfc,filter',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'delete_email'        => 'nullable|boolean',
            'role'                => 'required|in:admin,guest',
            'avatar_type'         => ['nullable', Rule::in(array_keys(User::avatarTypeOptions()))],
            'avatar_emoji'        => [
                Rule::requiredIf(fn () => $request->input('avatar_type') === User::AVATAR_EMOJI),
                'nullable',
                'string',
                Rule::in(array_keys(User::avatarEmojiOptions())),
            ],
            'avatar_bg_color'     => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'avatar_border_color' => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'avatar_border_width' => [
                'nullable',
                'integer',
                'min:0',
                'max:10',
            ],
            'avatar_image'        => [
                Rule::requiredIf(fn () => $request->input('avatar_type') === User::AVATAR_PHOTO && ! $user->avatar_image_path),
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp,gif,heic,heif',
                'max:5120',
            ],
            'last_name'           => 'nullable|string|max:50',
            'first_name'          => 'nullable|string|max:50',
            'furigana_sei'        => 'nullable|string|max:50',
            'furigana_mei'        => 'nullable|string|max:50',
            'guest_side'          => 'nullable|in:groom,bride',
            'relationship'        => 'nullable|in:friend,family,colleague,other',
            'event_day'           => 'nullable|in:day1,day2',
            'relationship_detail' => 'nullable|string|max:100',
            'participation'       => 'nullable|in:attending,declining,pending',
            'attending_count'     => 'nullable|integer|min:0|max:20',
            'children_count'      => 'nullable|integer|min:0|max:10',
            'has_allergy'         => 'nullable|in:0,1',
            'allergy_notes'       => 'nullable|string|max:500',
            'phone'               => ['nullable', 'string', 'max:20'],
            'postal_code'         => 'nullable|string|max:8',
            'address'             => 'nullable|string|max:200',
            'notes'               => 'nullable|string|max:1000',
            'password'            => 'nullable|string|min:6|max:255|confirmed',
        ], [
            'username.required'  => 'ユーザー名は必須です',
            'username.unique'    => 'このユーザー名はすでに使われています',
            'email.email'        => '正しいメールアドレスを入力してください',
            'email.max'          => 'メールアドレスは255文字以内で入力してください',
            'email.unique'       => 'このメールアドレスは既に登録されています',
            'password.min'       => 'パスワードは6文字以上にしてください',
            'password.confirmed' => '確認用パスワードが一致しません',
            'avatar_emoji.required' => '絵文字アイコンを選択してください',
            'avatar_emoji.in'       => '選択された絵文字アイコンは利用できません',
            'avatar_image.required' => '写真アイコンを使う場合は画像を選択してください',
            'avatar_image.mimes'    => '写真はJPEG・PNG・WEBP・GIF・HEIC形式のいずれかを選択してください',
            'avatar_image.max'      => '写真は5MB以下にしてください',
            'avatar_image.uploaded' => '写真のアップロードに失敗しました。ファイルサイズが大きすぎる可能性があります。時間をおいて再度お試しください',
            'avatar_bg_color.regex' => '背景色の形式が正しくありません',
            'avatar_border_color.regex' => '枠線色の形式が正しくありません',
            'avatar_border_width.integer' => '枠線の太さは数値で入力してください',
        ]);

        $fullName = trim(($request->last_name ?? '') . ' ' . ($request->first_name ?? ''));
        try {
            $avatarData = $this->buildAvatarData($request, $user);
        } catch (RuntimeException $e) {
            return back()->withErrors(['avatar_image' => $e->getMessage()]);
        }
        $originalEmail = $user->email;
        $newEmail = $request->boolean('delete_email')
            ? null
            : ($request->filled('email') ? trim($request->email) : null);

        $userData = [
            'username' => $request->username,
            'name'     => $fullName ?: $user->username,
            'role'     => $request->role,
            'email'    => $newEmail,
            ...$avatarData,
        ];

        if ($newEmail !== $originalEmail) {
            $userData['email_verified_at'] = $request->role === 'admin' && $newEmail ? now() : null;
            $userData['email_verification_token'] = null;
            $userData['email_verification_sent_at'] = null;
        } elseif ($request->role === 'admin' && $newEmail && ! $user->email_verified_at) {
            $userData['email_verified_at'] = now();
        }

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
            $userData['password_change_required'] = $request->role === 'guest';
            $userData['password_changed_at'] = $request->role === 'guest' ? null : now();
        }

        if ($request->role === 'admin') {
            $userData['password_change_required'] = false;
            $userData['password_changed_at'] = $user->password_changed_at ?? now();
        }

        $user->update($userData);

        if ($newEmail !== $originalEmail) {
            $action = $newEmail === null ? 'admin_delete' : ($originalEmail ? 'admin_update' : 'admin_set');
            $message = $newEmail === null
                ? '管理者がメールアドレスを削除しました'
                : ($originalEmail ? '管理者がメールアドレスを変更しました' : '管理者がメールアドレスを登録しました');

            EmailAuditLog::record(
                $user,
                auth()->user(),
                $action,
                $originalEmail,
                $newEmail,
                $message,
                ['role' => $request->role]
            );

            if ($newEmail && ! $user->isAdmin()) {
                try {
                    $user->sendEmailVerification(force: true);
                } catch (\Throwable) {
                    // メール送信失敗でも管理者の保存は完了させる
                }
            }
        }

        if ($request->role === 'guest') {
            $profile = GuestProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'last_name'           => $request->last_name,
                    'first_name'          => $request->first_name,
                    'furigana_sei'        => $request->furigana_sei,
                    'furigana_mei'        => $request->furigana_mei,
                    'guest_side'          => $request->guest_side ?: null,
                    'event_day'           => $request->event_day ?: 'day2',
                    'relationship'        => $request->relationship ?: null,
                    'relationship_detail' => $request->relationship_detail,
                    'participation'       => $request->participation ?? 'pending',
                    'attending_count'     => $request->attending_count ?? 0,
                    'children_count'      => $request->children_count ?? 0,
                    'has_allergy'         => $request->has_allergy === '1',
                    'allergy_notes'       => $request->allergy_notes,
                    'phone'               => $request->phone,
                    'postal_code'         => $request->postal_code,
                    'address'             => $request->address,
                    'notes'               => $request->notes,
                    'responded_at'        => $request->participation !== 'pending'
                                                ? ($user->guestProfile?->responded_at ?? now())
                                                : null,
                    'checkin_token'       => $user->guestProfile?->checkin_token ?: (string) Str::uuid(),
                ]
            );

            $profile->ensureCheckInToken();

            CheckInAuditLog::record(
                $profile,
                auth()->user(),
                'user_update',
                'admin',
                '管理画面でゲスト情報を更新しました',
                [
                    'user_id' => $user->id,
                ]
            );
        }

        // タスク割り当て更新
        $user->taskAssignments()->delete();
        foreach ($request->input('task_ids', []) as $taskId) {
            GuestTaskAssignment::create([
                'user_id'         => $user->id,
                'wedding_task_id' => $taskId,
                'custom_time'     => $request->input("task_times.{$taskId}") ?: null,
                'custom_note'     => $request->input("task_notes.{$taskId}") ?: null,
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', "「{$user->username}」の情報を更新しました");
    }

    /**
     * ユーザー一覧からの直接編集で「お立場」「ご関係」だけを更新する。
     * 通常の update() は RSVP・タスク割当など全項目を上書きしてしまうため、
     * 一覧上でのすばやい修正にはこの専用エンドポイントを使う。
     */
    public function updateGuestInfo(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return response()->json(['error' => '管理者アカウントには設定できません'], 422);
        }

        $request->validate([
            'guest_side'   => 'nullable|in:groom,bride',
            'event_day'    => 'nullable|in:day1,day2',
            'relationship' => 'nullable|in:friend,family,colleague,other',
        ]);

        $profile = $user->guestProfile ?: GuestProfile::create([
            'user_id'       => $user->id,
            'participation' => 'pending',
            'event_day'     => 'day2',
            'checkin_token' => (string) Str::uuid(),
        ]);

        $updates = [];
        if ($request->has('guest_side')) {
            $updates['guest_side'] = $request->guest_side ?: null;
        }
        if ($request->has('event_day')) {
            $updates['event_day'] = $request->event_day ?: 'day2';
        }
        if ($request->has('relationship')) {
            $updates['relationship'] = $request->relationship ?: null;
        }

        if ($updates) {
            $profile->update($updates);
        }

        $profile->ensureCheckInToken();

        return response()->json([
            'success'          => true,
            'guest_side'       => $profile->guest_side,
            'guest_side_label' => $profile->guestSideLabel(),
            'event_day'        => $profile->event_day,
            'event_day_label'  => $profile->eventDayLabel(),
            'relationship'         => $profile->relationship,
            'relationship_label'   => $profile->relationshipLabel(),
        ]);
    }

    public function updatePassword(Request $request, int $id)
    {
        $request->validate([
            'password' => 'required|string|min:6|max:255|confirmed',
        ], [
            'password.required'  => '新しいパスワードを入力してください',
            'password.min'       => 'パスワードは6文字以上にしてください',
            'password.confirmed' => '確認用パスワードが一致しません',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->password),
            'password_change_required' => $user->role === 'guest',
            'password_changed_at' => $user->role === 'guest' ? null : now(),
        ]);

        if ($user->guestProfile) {
            CheckInAuditLog::record(
                $user->guestProfile,
                auth()->user(),
                'password_change',
                'admin',
                '管理画面でパスワードを更新しました',
                [
                    'user_id' => $user->id,
                ]
            );
        }

        return redirect()->route('admin.users')
            ->with('success', "「{$user->username}」のパスワードを変更しました");
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', '自分自身は削除できません');
        }

        if ($user->guestProfile) {
            CheckInAuditLog::record(
                $user->guestProfile,
                auth()->user(),
                'user_delete',
                'admin',
                '管理画面でユーザーを削除しました',
                [
                    'user_id' => $user->id,
                ]
            );
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'ユーザーを削除しました');
    }


    public function bulkUpdateEventDay(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'event_day' => ['required', 'in:day1,day2'],
        ], [
            'user_ids.required' => '参加日を変更するゲストを選択してください',
            'event_day.required' => '参加日を選択してください',
        ]);

        $ids = collect($request->input('user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $users = User::with('guestProfile')
            ->where('role', 'guest')
            ->whereIn('id', $ids)
            ->get();

        foreach ($users as $user) {
            $profile = GuestProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'event_day' => $request->event_day,
                    'participation' => $user->guestProfile?->participation ?? 'pending',
                    'checkin_token' => $user->guestProfile?->checkin_token ?: (string) Str::uuid(),
                ]
            );

            $profile->ensureCheckInToken();
        }

        $label = $request->event_day === 'day1' ? '1日目' : '2日目';

        return redirect()->route('admin.users')
            ->with('success', $users->count() . "名を{$label}に変更しました");
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ], [
            'user_ids.required' => '削除するユーザーを選択してください',
        ]);

        $ids = collect($request->input('user_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn ($id) => $id === auth()->id())
            ->values();

        if ($ids->isEmpty()) {
            return redirect()->route('admin.users')
                ->with('error', '削除できるユーザーが選択されていません');
        }

        $users = User::with('guestProfile')->whereIn('id', $ids)->get();

        foreach ($users as $user) {
            if ($user->guestProfile) {
                CheckInAuditLog::record(
                    $user->guestProfile,
                    auth()->user(),
                    'user_delete',
                    'admin',
                    '管理画面でユーザーを一括削除しました',
                    [
                        'user_id' => $user->id,
                    ]
                );
            }
        }

        $deleted = User::whereIn('id', $users->pluck('id'))->delete();

        return redirect()->route('admin.users')
            ->with('success', "{$deleted}名のユーザーを削除しました");
    }

    private function buildAvatarData(Request $request, ?User $currentUser = null): array
    {
        if ($currentUser && ! $request->has('avatar_type')) {
            return [];
        }

        $avatarType = $request->input('avatar_type', User::AVATAR_INITIAL);
        $currentPath = $currentUser?->avatar_image_path;

        if ($avatarType === User::AVATAR_PHOTO) {
            if ($request->hasFile('avatar_image')) {
                $newPath = app(AvatarUploadService::class)->store($request->file('avatar_image'));
                if ($currentPath) {
                    Storage::disk('public')->delete($currentPath);
                }
                return [
                    'avatar_type' => $avatarType,
                    'avatar_emoji' => null,
                    'avatar_image_path' => $newPath,
                    'avatar_bg_color' => null,
                    'avatar_border_color' => $request->input('avatar_border_color', '#f0e4d0'),
                    'avatar_border_width' => (int) $request->input('avatar_border_width', 3),
                ];
            }

            return [
                'avatar_type' => $avatarType,
                'avatar_emoji' => null,
                'avatar_image_path' => $currentPath,
                'avatar_bg_color' => null,
                'avatar_border_color' => $request->input('avatar_border_color', '#f0e4d0'),
                'avatar_border_width' => (int) $request->input('avatar_border_width', 3),
            ];
        }

        if ($currentPath) {
            Storage::disk('public')->delete($currentPath);
        }

        return [
            'avatar_type' => $avatarType,
            'avatar_emoji' => $avatarType === User::AVATAR_EMOJI ? $request->input('avatar_emoji') : null,
            'avatar_image_path' => null,
            'avatar_bg_color' => $avatarType === User::AVATAR_EMOJI
                ? $request->input('avatar_bg_color', '#ffffff')
                : null,
            'avatar_border_color' => $request->input('avatar_border_color', '#f0e4d0'),
            'avatar_border_width' => (int) $request->input('avatar_border_width', 3),
        ];
    }

    private function normalizeImportRows(array $rows): array
    {
        return array_values(array_map(function ($row) {
            $title1 = $this->cleanCsvValue($row['title1'] ?? '');
            $title2 = $this->cleanCsvValue($row['title2'] ?? '');

            return [
                'username' => $this->cleanCsvValue($row['username'] ?? ''),
                'last_name' => $this->cleanCsvValue($row['last_name'] ?? ''),
                'first_name' => $this->cleanCsvValue($row['first_name'] ?? ''),
                'relationship_text' => $this->cleanCsvValue($row['relationship_text'] ?? ''),
                'title1' => $title1,
                'title2' => $title2,
                'relationship_detail' => trim(implode(' ', array_filter([$title1, $title2]))),
                'notes' => $this->cleanCsvValue($row['notes'] ?? ''),
                'source_errors' => $row['source_errors'] ?? [],
            ];
        }, $rows));
    }

    private function buildImportPreviewRows(array $rows): array
    {
        $rows = $this->normalizeImportRows($rows);
        $usernameCounts = [];

        foreach ($rows as $row) {
            if ($row['username'] !== '') {
                $usernameCounts[$row['username']] = ($usernameCounts[$row['username']] ?? 0) + 1;
            }
        }

        $existingUsernames = User::whereIn('username', array_keys($usernameCounts))
            ->pluck('username')
            ->flip();

        return array_map(function ($row, $index) use ($usernameCounts, $existingUsernames) {
            $errors = $row['source_errors'] ?? [];

            if ($row['username'] === '') {
                $errors[] = 'ユーザー名が空です';
            } elseif (mb_strlen($row['username']) > 50) {
                $errors[] = 'ユーザー名は50文字以内にしてください';
            } else {
                if (! preg_match('/\A[A-Za-z0-9._-]+\z/', $row['username'])) {
                    $errors[] = 'ユーザー名は半角英数字、ドット、ハイフン、アンダーバーのみ使えます';
                }

                if (($usernameCounts[$row['username']] ?? 0) > 1) {
                    $errors[] = 'CSV内でユーザー名が重複しています';
                }

                if ($existingUsernames->has($row['username'])) {
                    $errors[] = '既に登録済みのユーザー名です';
                }
            }

            foreach ([
                'last_name' => ['label' => '姓', 'max' => 50],
                'first_name' => ['label' => '名', 'max' => 50],
                'relationship_text' => ['label' => '関係', 'max' => 100],
                'title1' => ['label' => '肩書き1', 'max' => 100],
                'title2' => ['label' => '肩書き2', 'max' => 100],
                'relationship_detail' => ['label' => '肩書き', 'max' => 100],
                'notes' => ['label' => 'お言葉', 'max' => 1000],
            ] as $key => $rule) {
                if (mb_strlen($row[$key] ?? '') > $rule['max']) {
                    $errors[] = "{$rule['label']}は{$rule['max']}文字以内にしてください";
                }
            }

            if ($row['last_name'] === '' && $row['first_name'] === '') {
                $errors[] = '姓または名を入力してください';
            }

            foreach (['last_name' => '姓', 'first_name' => '名'] as $key => $label) {
                if (str_contains($row[$key] ?? '', '？') || str_contains($row[$key] ?? '', '?')) {
                    $errors[] = "{$label}に未確認文字「？」が含まれています";
                }
            }

            return [
                ...$row,
                'line' => $index + 2,
                'guest_side' => $this->guestSideFromCsv($row),
                'relationship' => $this->relationshipFromCsv($row),
                'errors' => $errors,
            ];
        }, $rows, array_keys($rows));
    }

    private function readGuestCsv(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false) {
            throw ValidationException::withMessages([
                'guest_csv' => 'CSVファイルを読み込めませんでした',
            ]);
        }

        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'SJIS-win,CP932,EUC-JP,UTF-8');
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (! $lines || trim($lines[0] ?? '') === '') {
            return [];
        }

        $delimiter = substr_count($lines[0], "\t") > substr_count($lines[0], ',') ? "\t" : ',';
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter);
        if (! is_array($headers)) {
            fclose($handle);
            return [];
        }

        $headers = $this->normalizeCsvHeaders($headers);
        $this->validateCsvHeaders($headers);
        $rows = [];
        $line = 1;

        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $sourceErrors = [];
            if (count($values) < count($headers)) {
                $sourceErrors[] = "{$line}行目: CSVの列数がヘッダーより少ないです";
            }

            if (count($values) > count($headers)) {
                $extraValues = array_slice($values, count($headers));
                if (count(array_filter($extraValues, fn ($value) => trim((string) $value) !== '')) > 0) {
                    $sourceErrors[] = "{$line}行目: CSVの列数がヘッダーより多いです。カンマを含む値はダブルクォートで囲んでください";
                }
            }

            $raw = [];
            foreach ($headers as $index => $key) {
                if ($key === '') {
                    continue;
                }
                $raw[$key] = $values[$index] ?? null;
            }

            $rows[] = [
                'username' => $this->firstCsvValue($raw, ['username', 'user_name', 'ユーザー名', 'ユーザーネーム', 'ログインid', 'ログインID', 'id', 'ID']),
                'last_name' => $this->firstCsvValue($raw, ['姓', '名字', '苗字', 'last_name']),
                'first_name' => $this->firstCsvValue($raw, ['名', '名前', 'first_name']),
                'relationship_text' => $this->firstCsvValue($raw, ['関係', 'ご関係', 'relationship']),
                'title1' => $this->firstCsvValue($raw, ['肩書き1', '肩書1', '肩書き', 'title1']),
                'title2' => $this->firstCsvValue($raw, ['肩書き2', '肩書2', '補足', 'title2']),
                'relationship_detail' => trim(implode(' ', array_filter([
                    $this->firstCsvValue($raw, ['肩書き1', '肩書1', '肩書き', 'title1']),
                    $this->firstCsvValue($raw, ['肩書き2', '肩書2', '補足', 'title2']),
                ], fn ($value) => filled($value)))),
                'notes' => $this->firstCsvValue($raw, ['お言葉', 'メッセージ', 'notes']),
                'source_errors' => $sourceErrors,
            ];
        }

        fclose($handle);

        return $rows;
    }

    private function normalizeCsvHeaders(array $headers): array
    {
        return array_map(function ($header) {
            return trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $header));
        }, $headers);
    }

    private function validateCsvHeaders(array $headers): void
    {
        $filledHeaders = array_values(array_filter($headers, fn ($header) => $header !== ''));
        if (count($filledHeaders) === 0) {
            throw ValidationException::withMessages([
                'guest_csv' => 'CSVのヘッダー行が空です',
            ]);
        }

        $duplicates = collect($filledHeaders)
            ->countBy()
            ->filter(fn ($count) => $count > 1)
            ->keys()
            ->all();

        if ($duplicates) {
            throw ValidationException::withMessages([
                'guest_csv' => '同じ列名が複数あります: ' . implode(', ', $duplicates),
            ]);
        }

        $hasUsernameHeader = collect($headers)->contains(function ($header) {
            return in_array($header, ['username', 'user_name', 'ユーザー名', 'ユーザーネーム', 'ログインid', 'ログインID', 'id', 'ID'], true);
        });

        if (! $hasUsernameHeader) {
            throw ValidationException::withMessages([
                'guest_csv' => 'ユーザー名列がありません。ヘッダーに「ユーザー名」または「username」を追加してください',
            ]);
        }
    }

    private function firstCsvValue(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return $row[$key];
            }
        }

        return null;
    }

    private function cleanCsvValue(?string $value): string
    {
        return trim((string) $value);
    }

    private function guestSideFromCsv(array $row): ?string
    {
        $text = implode(' ', [
            $row['title1'] ?? '',
            $row['title2'] ?? '',
        ]);

        if (str_contains($text, '新郎')) {
            return 'groom';
        }

        if (str_contains($text, '新婦')) {
            return 'bride';
        }

        return null;
    }

    private function relationshipFromCsv(array $row): ?string
    {
        $text = implode(' ', [
            $row['relationship_text'] ?? '',
            $row['title1'] ?? '',
            $row['title2'] ?? '',
        ]);

        if (str_contains($text, '親族') || str_contains($text, '家族') || str_contains($text, '父') || str_contains($text, '母') || str_contains($text, '祖') || str_contains($text, '兄') || str_contains($text, '姉') || str_contains($text, '弟') || str_contains($text, '妹') || str_contains($text, '伯') || str_contains($text, '叔') || str_contains($text, '従')) {
            return 'family';
        }

        if (str_contains($text, '職場') || str_contains($text, '会社') || str_contains($text, '同僚') || str_contains($text, '上司')) {
            return 'colleague';
        }

        if (str_contains($text, '友人') || str_contains($text, '知人')) {
            return 'friend';
        }

        return $text === '' ? null : 'other';
    }
}
