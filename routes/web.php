<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRsvpController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminSeatingController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\GuestSeatingController;

// ── トップページ ─────────────────────────────────────────
// 認証済み → role に応じてリダイレクト
// 未認証   → /login へ
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
    Route::get('/invitation', [InvitationController::class,'index'])->name('invitation');
    Route::post('/invitation',[InvitationController::class,'update'])->name('invitation.update');
    Route::get('/seating',    [GuestSeatingController::class,'index'])->name('seating.guest');
});

// ── 管理者用ページ（認証 + admin）────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',         [AdminController::class,        'index'])->name('dashboard');
    Route::get('/rsvp',     [AdminRsvpController::class,   'index'])->name('rsvp');
    Route::get('/settings', [AdminSettingController::class, 'edit'])  ->name('settings');
    Route::post('/settings',[AdminSettingController::class, 'update'])->name('settings.update');

    // ユーザー管理
    Route::get('/users',             [AdminUserController::class, 'index'])        ->name('users');
    Route::post('/users',            [AdminUserController::class, 'store'])        ->name('users.store');
    Route::get('/users/{id}/edit',   [AdminUserController::class, 'edit'])         ->name('users.edit');
    Route::patch('/users/{id}',      [AdminUserController::class, 'update'])       ->name('users.update');
    Route::patch('/users/{id}/password', [AdminUserController::class, 'updatePassword'])->name('users.password');
    Route::delete('/users/{id}',     [AdminUserController::class, 'destroy'])      ->name('users.destroy');

    // 席次表管理
    Route::get('/seating',                             [AdminSeatingController::class, 'index'])         ->name('seating');
    // テーブル
    Route::post('/seating/tables',                     [AdminSeatingController::class, 'storeTable'])    ->name('seating.tables.store');
    Route::delete('/seating/tables/{tableId}',         [AdminSeatingController::class, 'destroyTable'])  ->name('seating.tables.destroy');
    Route::patch('/seating/tables/{tableId}/position', [AdminSeatingController::class, 'updatePosition'])->name('seating.tables.position');
    // 席
    Route::post('/seating/tables/{tableId}/seats',     [AdminSeatingController::class, 'storeSeat'])     ->name('seating.seats.store');
    Route::patch('/seating/seats/{seatId}',            [AdminSeatingController::class, 'updateSeat'])    ->name('seating.seats.update');
    Route::delete('/seating/seats/{seatId}',           [AdminSeatingController::class, 'destroySeat'])   ->name('seating.seats.destroy');
    // 配置
    Route::post('/seating/assign',                     [AdminSeatingController::class, 'assign'])        ->name('seating.assign');
    Route::delete('/seating/unassign/{userId}',        [AdminSeatingController::class, 'unassign'])      ->name('seating.unassign');
});

// ── ログアウト ────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── CSRF トークンリフレッシュ（セッション維持ハートビート用）──
// 認証の有無に関わらずアクセス可能。セッションを延長しCSRFトークンを返す。
Route::get('/csrf-refresh', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');
