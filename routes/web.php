<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminOperationsController;
use App\Http\Controllers\AdminAuditController;
use App\Http\Controllers\AdminCheckInController;
use App\Http\Controllers\AdminLoginHistoryController;
use App\Http\Controllers\AdminRsvpController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminSeatingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminProgramController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\GuestSeatingController;
use App\Http\Controllers\CoupleProfileController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminTaskController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\AdminGalleryController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileBookController;
use App\Http\Controllers\AdminProfileBookController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\GuestbookController;
use App\Http\Controllers\AdminGuestbookController;
use App\Http\Controllers\AdminMediaController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ForgotEmailController;
use App\Http\Controllers\EmailRegistrationController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\AdminEmailAuditController;
use App\Http\Controllers\AdminReminderController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

// ── トップページ ─────────────────────────────────────────
Route::get('/', function () {
    if (Auth::check()) {
        if (! Auth::user()->isAdmin() && (blank(Auth::user()->email) || ! Auth::user()->hasVerifiedEmail())) {
            return redirect()->route('email.register');
        }

        if (! Auth::user()->isAdmin() && Auth::user()->password_change_required) {
            return redirect()->route('password.change');
        }

        return redirect()->route(
            Auth::user()->isAdmin() ? 'admin.dashboard' : 'dashboard'
        );
    }
    return redirect()->route('login');
});

// ── ログイン ──────────────────────────────────────────────
Route::get('/login',    [AuthController::class,  'showLogin'])   ->name('login');
Route::post('/login',   [AuthController::class,  'login'])        ->name('login.post');
Route::get('/register', [AccountController::class,'showRegister'])->name('register');
Route::post('/register',[AccountController::class,'register'])    ->name('register.post');
Route::get('/forgot-email', [ForgotEmailController::class, 'show'])->name('email.forgot');
Route::post('/forgot-email', [ForgotEmailController::class, 'lookup'])->name('email.forgot.lookup');
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');

// ── メール認証 ────────────────────────────────────────────
Route::get('/verify', [EmailVerificationController::class, 'verify'])->name('email.verify');
Route::post('/verify/resend', [EmailVerificationController::class, 'resend'])
    ->middleware('auth')->name('email.verify.resend');
Route::middleware('auth')->group(function () {
    Route::get('/email/register', [EmailRegistrationController::class, 'edit'])->name('email.register');
    Route::patch('/email/register', [EmailRegistrationController::class, 'update'])->name('email.register.update');
    // 代理ログインの終了。代理中はゲスト権限になっているため admin ミドルウェアの外に置く。
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
});
Route::middleware(['auth', 'email.ready'])->group(function () {
    Route::get('/password/change', [PasswordChangeController::class, 'edit'])->name('password.change');
    Route::patch('/password/change', [PasswordChangeController::class, 'update'])->name('password.change.update');
});

// ── ゲスト用ページ（認証必須）────────────────────────────
Route::middleware(['auth', 'email.ready', 'password.ready'])->group(function () {
    Route::get('/home',       [HomeController::class,       'index'])->name('dashboard');
    Route::get('/profile',    [ProfileController::class,   'show']) ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class,   'update'])->name('profile.update');
    Route::get('/invitation', [InvitationController::class,'index'])->name('invitation');
    Route::post('/invitation',[InvitationController::class,'update'])->name('invitation.update');
    Route::get('/seating',    [GuestSeatingController::class,'index'])->name('seating.guest');
    Route::get('/program',    [ProgramController::class,       'index'])->name('program');
    Route::get('/access',     [AccessController::class,        'index'])->name('access');
    Route::get('/faq',        [FaqController::class,           'index'])->name('faq');
    Route::get('/profiles',          [CoupleProfileController::class, 'index'])->name('profiles.index');
    Route::get('/profiles/{person}', [CoupleProfileController::class, 'show']) ->name('profiles.show')
         ->where('person', 'groom|bride');
    Route::get('/profile-book', [ProfileBookController::class, 'index'])->name('profile-book');
    // ギャラリー・ニュース一覧・ゲストブック
    Route::get('/gallery',              [GalleryController::class,   'index'])->name('gallery');
    Route::get('/gallery/upload',       [GalleryController::class,   'uploadForm'])->name('gallery.upload');
    Route::post('/gallery/upload',      [GalleryController::class,   'upload'])->name('gallery.upload.post');
    Route::get('/people',               [PeopleController::class,    'index'])->name('people.index');
    Route::get('/people/{user}',        [PeopleController::class,    'show'])->name('people.show');
    Route::get('/movies',               [MovieController::class, 'show'])->name('movies');
    Route::redirect('/ending', '/movies');
    Route::get('/news',               [NewsController::class,       'index'])->name('news.index');
    Route::get('/news/{id}',          [NewsController::class,       'show']) ->name('news.show');
    Route::get('/guestbook',          [GuestbookController::class,  'index'])->name('guestbook');
    Route::post('/guestbook',         [GuestbookController::class,  'store'])->name('guestbook.store');
    Route::delete('/guestbook/mine',  [GuestbookController::class,  'destroy'])->name('guestbook.destroy');
});

// ── 管理者用ページ（認証 + admin）────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',              [AdminController::class,            'index'])->name('dashboard');
    Route::get('/ops',           [AdminOperationsController::class, 'index'])->name('ops');
    Route::get('/ops/live',      [AdminOperationsController::class, 'metrics'])->name('ops.live');
    Route::get('/audit/check-in',[AdminAuditController::class, 'index'])->name('audit.checkin');
    Route::get('/audit/email', [AdminEmailAuditController::class, 'index'])->name('audit.email');
    Route::get('/rsvp/export',   [AdminRsvpController::class, 'export'])->name('rsvp.export');
    Route::get('/login-history', [AdminLoginHistoryController::class,'index'])->name('login-history');
    Route::get('/settings', [AdminSettingController::class, 'edit'])  ->name('settings');
    Route::post('/settings',[AdminSettingController::class, 'update'])->name('settings.update');

    // メディア管理
    Route::get   ('/media',                         [AdminMediaController::class, 'index'])      ->name('media');
    Route::get   ('/media/{location}',              [AdminMediaController::class, 'show'])       ->name('media.show');
    Route::post  ('/media/{location}/upload',       [AdminMediaController::class, 'store'])      ->name('media.store');
    Route::delete('/media/images/{id}',             [AdminMediaController::class, 'destroy'])    ->name('media.destroy');
    Route::patch ('/media/images/{id}/toggle',      [AdminMediaController::class, 'toggle'])     ->name('media.toggle');
    Route::post  ('/media/reorder',                 [AdminMediaController::class, 'reorder'])    ->name('media.reorder');
    Route::post  ('/media/{location}/mode',         [AdminMediaController::class, 'setMode'])    ->name('media.mode');
    Route::post  ('/media/hero/type',               [AdminMediaController::class, 'setHeroType'])->name('media.hero-type');
    Route::post  ('/media/hero/video',              [AdminMediaController::class, 'uploadVideo'])->name('media.video');
    Route::delete('/media/hero/video',              [AdminMediaController::class, 'deleteVideo'])->name('media.video-delete');
    Route::post  ('/media/movie/{type}',            [AdminMediaController::class, 'uploadMovie'])->where('type', 'opening|profile|ending')->name('media.movie.upload');
    Route::delete('/media/movie/{type}',            [AdminMediaController::class, 'deleteMovie'])->where('type', 'opening|profile|ending')->name('media.movie.delete');

    // お知らせ管理
    Route::get('/news',                  [AdminNewsController::class, 'index'])   ->name('news');
    Route::post('/news',                 [AdminNewsController::class, 'store'])   ->name('news.store');
    Route::patch('/news/{id}',           [AdminNewsController::class, 'update'])  ->name('news.update');
    Route::delete('/news/{id}',          [AdminNewsController::class, 'destroy']) ->name('news.destroy');
    Route::patch('/news/{id}/move-up',   [AdminNewsController::class, 'moveUp'])  ->name('news.move-up');
    Route::patch('/news/{id}/move-down', [AdminNewsController::class, 'moveDown'])->name('news.move-down');

    // ギャラリー管理
    Route::get('/gallery',                   [AdminGalleryController::class, 'index'])    ->name('gallery');
    Route::post('/gallery',                  [AdminGalleryController::class, 'store'])    ->name('gallery.store');
    Route::patch('/gallery/{id}',            [AdminGalleryController::class, 'update'])   ->name('gallery.update');
    Route::delete('/gallery/{id}',           [AdminGalleryController::class, 'destroy'])  ->name('gallery.destroy');
    Route::patch('/gallery/{id}/move-up',    [AdminGalleryController::class, 'moveUp'])   ->name('gallery.move-up');
    Route::patch('/gallery/{id}/move-down',  [AdminGalleryController::class, 'moveDown']) ->name('gallery.move-down');
    Route::post('/gallery/{id}/approve',     [AdminGalleryController::class, 'approve'])  ->name('gallery.approve');
    Route::post('/gallery/{id}/reject',      [AdminGalleryController::class, 'reject'])   ->name('gallery.reject');
    Route::post('/gallery/{id}/tag',         [AdminGalleryController::class, 'tag'])      ->name('gallery.tag');

    // プロフィールブック管理
    Route::get('/profile-book',                  [AdminProfileBookController::class, 'edit'])       ->name('profile-book');
    Route::post('/profile-book',                 [AdminProfileBookController::class, 'upload'])     ->name('profile-book.upload');
    Route::delete('/profile-book',                [AdminProfileBookController::class, 'destroy'])    ->name('profile-book.destroy');
    Route::delete('/profile-book/{id}',           [AdminProfileBookController::class, 'destroyPage'])->name('profile-book.destroy-page');
    Route::patch('/profile-book/{id}/move-up',    [AdminProfileBookController::class, 'moveUp'])     ->name('profile-book.move-up');
    Route::patch('/profile-book/{id}/move-down',  [AdminProfileBookController::class, 'moveDown'])   ->name('profile-book.move-down');

    // リマインダーメール管理
    Route::get('/reminders',              [AdminReminderController::class, 'index'])   ->name('reminders');
    Route::post('/reminders',             [AdminReminderController::class, 'store'])   ->name('reminders.store');
    Route::post('/reminders/{id}/send',   [AdminReminderController::class, 'send'])    ->name('reminders.send');
    Route::patch('/reminders/{id}/cancel',[AdminReminderController::class, 'cancel'])  ->name('reminders.cancel');
    Route::delete('/reminders/{id}',      [AdminReminderController::class, 'destroy']) ->name('reminders.destroy');

    // ゲストブック管理
    Route::get('/guestbook',         [AdminGuestbookController::class, 'index'])  ->name('guestbook');
    Route::patch('/guestbook/{id}',  [AdminGuestbookController::class, 'update']) ->name('guestbook.update');
    Route::delete('/guestbook/{id}', [AdminGuestbookController::class, 'destroy'])->name('guestbook.destroy');

    // プロフィール管理
    Route::get('/profiles',  [AdminProfileController::class, 'edit'])  ->name('profiles');
    Route::post('/profiles', [AdminProfileController::class, 'update'])->name('profiles.update');

    // 当日の役割管理
    Route::get('/tasks',                   [AdminTaskController::class, 'index'])   ->name('tasks');
    Route::post('/tasks',                  [AdminTaskController::class, 'store'])   ->name('tasks.store');
    Route::patch('/tasks/{id}',            [AdminTaskController::class, 'update'])  ->name('tasks.update');
    Route::delete('/tasks/{id}',           [AdminTaskController::class, 'destroy']) ->name('tasks.destroy');
    Route::patch('/tasks/{id}/move-up',    [AdminTaskController::class, 'moveUp'])  ->name('tasks.move-up');
    Route::patch('/tasks/{id}/move-down',  [AdminTaskController::class, 'moveDown'])->name('tasks.move-down');
    Route::post  ('/tasks/{id}/program',              [AdminTaskController::class, 'storeProgram'])  ->name('tasks.program.store');
    Route::patch ('/tasks/{id}/program/{pid}',        [AdminTaskController::class, 'updateProgram']) ->name('tasks.program.update');
    Route::delete('/tasks/{id}/program/{pid}',        [AdminTaskController::class, 'destroyProgram'])->name('tasks.program.destroy');
    Route::patch ('/tasks/{id}/program/{pid}/move-up',   [AdminTaskController::class, 'moveProgramUp'])  ->name('tasks.program.move-up');
    Route::patch ('/tasks/{id}/program/{pid}/move-down', [AdminTaskController::class, 'moveProgramDown'])->name('tasks.program.move-down');

    // 式次第管理
    Route::get('/program',                   [AdminProgramController::class, 'index'])   ->name('program');
    Route::post('/program',                  [AdminProgramController::class, 'store'])   ->name('program.store');
    Route::patch('/program/{id}',            [AdminProgramController::class, 'update'])  ->name('program.update');
    Route::patch('/program/{id}/move-up',    [AdminProgramController::class, 'moveUp'])  ->name('program.move-up');
    Route::patch('/program/{id}/move-down',  [AdminProgramController::class, 'moveDown'])->name('program.move-down');
    Route::delete('/program/{id}',           [AdminProgramController::class, 'destroy']) ->name('program.destroy');

    // Q&A管理
    Route::get('/faq',               [AdminFaqController::class, 'index'])   ->name('faq');
    Route::post('/faq',              [AdminFaqController::class, 'store'])   ->name('faq.store');
    Route::patch('/faq/{id}',        [AdminFaqController::class, 'update'])  ->name('faq.update');
    Route::patch('/faq/{id}/move-up',    [AdminFaqController::class, 'moveUp'])  ->name('faq.move-up');
    Route::patch('/faq/{id}/move-down',  [AdminFaqController::class, 'moveDown'])->name('faq.move-down');
    Route::delete('/faq/{id}',       [AdminFaqController::class, 'destroy']) ->name('faq.destroy');

    // ユーザー管理
    Route::get('/users',             [AdminUserController::class, 'index'])        ->name('users');
    Route::post('/users',            [AdminUserController::class, 'store'])        ->name('users.store');
    Route::get('/users/import/preview', fn () => redirect()->route('admin.users'));
    Route::post('/users/import/preview', [AdminUserController::class, 'previewImport'])->name('users.import.preview');
    Route::get('/users/import', fn () => redirect()->route('admin.users'));
    Route::post('/users/import',     [AdminUserController::class, 'import'])       ->name('users.import');
    Route::delete('/users',          [AdminUserController::class, 'bulkDestroy'])  ->name('users.bulk-destroy');
    Route::get('/users/{id}',        [AdminUserController::class, 'show'])         ->whereNumber('id')->name('users.show');
    Route::get('/users/{id}/qr',     [AdminUserController::class, 'qr'])           ->whereNumber('id')->name('users.qr');
    Route::get('/users/{id}/edit',   [AdminUserController::class, 'edit'])         ->whereNumber('id')->name('users.edit');
    Route::patch('/users/{id}',      [AdminUserController::class, 'update'])       ->whereNumber('id')->name('users.update');
    Route::patch('/users/{id}/password', [AdminUserController::class, 'updatePassword'])->whereNumber('id')->name('users.password');
    Route::patch('/users/{id}/guest-info', [AdminUserController::class, 'updateGuestInfo'])->whereNumber('id')->name('users.guest-info');
    // 代理ログイン開始（管理者のみ。終了側は代理中＝ゲスト権限になるため下の auth グループに置く）
    Route::post('/users/{id}/impersonate', [ImpersonationController::class, 'start'])->whereNumber('id')->name('users.impersonate');
    Route::delete('/users/{id}',     [AdminUserController::class, 'destroy'])      ->whereNumber('id')->name('users.destroy');

    // 受付チェックイン
    Route::get('/check-in',                    [AdminCheckInController::class, 'index']) ->name('checkin.index');
    Route::get('/check-in/guests',             [AdminCheckInController::class, 'guests']) ->name('checkin.guests');
    Route::post('/check-in/scan',              [AdminCheckInController::class, 'scan'])  ->name('checkin.scan');
    Route::post('/check-in/guests/{guestProfile}/check-in', [AdminCheckInController::class, 'checkInGuest'])->name('checkin.guests.check-in');
    Route::delete('/check-in/guests/{guestProfile}/check-in', [AdminCheckInController::class, 'cancelGuestCheckIn'])->name('checkin.guests.cancel');
    Route::get('/check-in/{token}',            [AdminCheckInController::class, 'show'])  ->name('checkin.show');
    Route::post('/check-in/{token?}',          [AdminCheckInController::class, 'store']) ->name('checkin.store');

    // 席次表管理
    Route::get('/seating',                             [AdminSeatingController::class, 'index'])         ->name('seating');
    Route::get('/seating/print',                       [AdminSeatingController::class, 'print'])         ->name('seating.print');
    Route::get('/seating/guest-preview',               [AdminSeatingController::class, 'guestPreview'])  ->name('seating.guest-preview');
    Route::get('/seating/escort-cards',               [AdminSeatingController::class, 'escortCards'])   ->name('seating.escort-cards');
    Route::get('/seating/escort-cards/pdf',           [AdminSeatingController::class, 'escortCardsPdf'])->name('seating.escort-cards.pdf');
    Route::post('/seating/tables',                     [AdminSeatingController::class, 'storeTable'])    ->name('seating.tables.store');
    Route::delete('/seating/tables/{tableId}',         [AdminSeatingController::class, 'destroyTable'])  ->name('seating.tables.destroy');
    Route::patch('/seating/tables/{tableId}/position', [AdminSeatingController::class, 'updatePosition'])->name('seating.tables.position');
    Route::patch('/seating/tables/{tableId}',          [AdminSeatingController::class, 'updateTable'])   ->name('seating.tables.update');
    Route::post('/seating/tables/{tableId}/seats',     [AdminSeatingController::class, 'storeSeat'])     ->name('seating.seats.store');
    Route::patch('/seating/seats/{seatId}',            [AdminSeatingController::class, 'updateSeat'])    ->name('seating.seats.update');
    Route::delete('/seating/seats/{seatId}',           [AdminSeatingController::class, 'destroySeat'])   ->name('seating.seats.destroy');
    Route::post('/seating/assign',                     [AdminSeatingController::class, 'assign'])        ->name('seating.assign');
    Route::delete('/seating/unassign/{userId}',        [AdminSeatingController::class, 'unassign'])      ->name('seating.unassign');
});

// ── ログアウト ────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── CSRF トークンリフレッシュ ──────────────────────────────
Route::get('/csrf-refresh', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');
