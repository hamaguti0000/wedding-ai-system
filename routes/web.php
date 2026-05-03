<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\AdminController;
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

// ── トップページ ─────────────────────────────────────────
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route(
            Auth::user()->isAdmin() ? 'admin.dashboard' : 'dashboard'
        );
    }
    return redirect()->route('login');
});

// ── ログイン ──────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])    ->name('login.post');

// ── ゲスト用ページ（認証必須）────────────────────────────
Route::middleware('auth')->group(function () {
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
});

// ── 管理者用ページ（認証 + admin）────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',              [AdminController::class,            'index'])->name('dashboard');
    Route::get('/rsvp',          [AdminRsvpController::class, 'index']) ->name('rsvp');
    Route::get('/rsvp/export',   [AdminRsvpController::class, 'export'])->name('rsvp.export');
    Route::get('/login-history', [AdminLoginHistoryController::class,'index'])->name('login-history');
    Route::get('/settings', [AdminSettingController::class, 'edit'])  ->name('settings');
    Route::post('/settings',[AdminSettingController::class, 'update'])->name('settings.update');

    // お知らせ管理
    Route::get('/news',                  [AdminNewsController::class, 'index'])   ->name('news');
    Route::post('/news',                 [AdminNewsController::class, 'store'])   ->name('news.store');
    Route::patch('/news/{id}',           [AdminNewsController::class, 'update'])  ->name('news.update');
    Route::delete('/news/{id}',          [AdminNewsController::class, 'destroy']) ->name('news.destroy');
    Route::patch('/news/{id}/move-up',   [AdminNewsController::class, 'moveUp'])  ->name('news.move-up');
    Route::patch('/news/{id}/move-down', [AdminNewsController::class, 'moveDown'])->name('news.move-down');

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
    Route::get('/users/{id}/edit',   [AdminUserController::class, 'edit'])         ->name('users.edit');
    Route::patch('/users/{id}',      [AdminUserController::class, 'update'])       ->name('users.update');
    Route::patch('/users/{id}/password', [AdminUserController::class, 'updatePassword'])->name('users.password');
    Route::delete('/users/{id}',     [AdminUserController::class, 'destroy'])      ->name('users.destroy');

    // 席次表管理
    Route::get('/seating',                             [AdminSeatingController::class, 'index'])         ->name('seating');
    Route::post('/seating/tables',                     [AdminSeatingController::class, 'storeTable'])    ->name('seating.tables.store');
    Route::delete('/seating/tables/{tableId}',         [AdminSeatingController::class, 'destroyTable'])  ->name('seating.tables.destroy');
    Route::patch('/seating/tables/{tableId}/position', [AdminSeatingController::class, 'updatePosition'])->name('seating.tables.position');
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
