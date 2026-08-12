<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理者が各ゲストの画面をそのまま確認するための代理ログイン。
 *
 * 「ゲストには何がどう見えているのか」を管理者が確かめる手段が無く、確認のたびに
 * ゲストのパスワードを変更してログインし直す必要があった（実際に席次表の赤字表示を
 * 確認する際にこれをやっていた）。パスワードを書き換えずに確認できるようにする。
 *
 * 安全のため次を守る:
 *  - 開始できるのは管理者だけ（ルート側の admin ミドルウェア）
 *  - 管理者を代理対象にはできない（権限の横取りを防ぐ）
 *  - 代理中は元の管理者IDをセッションに保持し、いつでも戻れる
 *  - 代理中に入れ子で別のユーザーへは移れない
 *  - 開始・終了をログに残す
 */
class ImpersonationController extends Controller
{
    /** 代理元の管理者IDを保持するセッションキー。 */
    public const SESSION_KEY = 'impersonator_id';

    public function start(Request $request, int $id)
    {
        // 代理中にさらに別のユーザーへ乗り換えられると、元の管理者へ戻れなくなる。
        if ($request->session()->has(self::SESSION_KEY)) {
            return redirect()->route('dashboard')
                ->with('error', '代理ログイン中です。管理者に戻ってから操作してください。');
        }

        $admin  = $request->user();
        $target = User::findOrFail($id);

        if ($target->isAdmin()) {
            return back()->with('error', '管理者アカウントには代理ログインできません。');
        }
        if ($target->id === $admin->id) {
            return back()->with('error', '自分自身には代理ログインできません。');
        }

        Log::info('impersonation.start', [
            'admin_id'  => $admin->id,
            'target_id' => $target->id,
            'ip'        => $request->ip(),
        ]);

        // ログイン処理でセッションIDが変わるため、元管理者IDは再生成後に入れる。
        $request->session()->regenerate();
        Auth::login($target);
        $request->session()->put(self::SESSION_KEY, $admin->id);

        return redirect()->route('dashboard')
            ->with('message', $target->name . ' として表示しています。');
    }

    public function stop(Request $request)
    {
        $adminId = $request->session()->get(self::SESSION_KEY);
        if (! $adminId) {
            return redirect()->route('dashboard');
        }

        $admin = User::find($adminId);
        if (! $admin || ! $admin->isAdmin()) {
            // 代理元が消えている/権限を失っている場合は、そのままログアウトさせる。
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        Log::info('impersonation.stop', [
            'admin_id'  => $admin->id,
            'target_id' => $request->user()?->id,
            'ip'        => $request->ip(),
        ]);

        $request->session()->forget(self::SESSION_KEY);
        $request->session()->regenerate();
        Auth::login($admin);

        return redirect()->route('admin.dashboard')
            ->with('message', '管理者に戻りました。');
    }
}
