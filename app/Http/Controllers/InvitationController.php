<?php

namespace App\Http\Controllers;

use App\Models\CheckInAuditLog;
use App\Models\GuestProfile;
use App\Models\WeddingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $user    = Auth::user()->load('guestProfile');
        $setting = WeddingSetting::first();

        return view('invitation', [
            'user'           => $user,
            'profile'        => $user->guestProfile,
            'setting'        => $setting,
            'deadlinePassed' => $this->isDeadlinePassed($setting),
        ]);
    }

    public function update(Request $request)
    {
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $setting = WeddingSetting::first();
        if ($this->isDeadlinePassed($setting)) {
            return redirect()->route('invitation')
                ->with('deadline_error', '出欠回答の受付は終了しました。');
        }

        $user      = Auth::user();
        $attending = $request->participation === 'attending';
        $hasAllergy = $attending && $request->input('has_allergy') === '1';

        $request->validate([
            'participation'   => 'required|in:attending,declining',
            'phone'           => ['nullable', 'string', 'max:20', 'regex:/^[0-9\-\+\(\)\s]+$/'],
            'postal_code'     => 'nullable|string|max:8',
            'address'         => 'nullable|string|max:200',
            'attending_count' => $attending ? 'required|integer|min:1|max:20' : 'nullable|integer',
            'children_count'  => 'nullable|integer|min:0|max:10',
            'has_allergy'     => $attending ? 'required|in:0,1'              : 'nullable',
            'allergy_notes'   => $hasAllergy ? 'required|string|max:500'     : 'nullable|string|max:500',
            'notes'           => 'nullable|string|max:1000',
        ], [
            'participation.required'   => '出欠のご回答は必須です',
            'phone.regex'              => '電話番号の形式が正しくありません',
            'attending_count.required' => '出席人数をご入力ください',
            'attending_count.min'      => '出席人数は1名以上を入力してください',
            'has_allergy.required'     => 'アレルギーの有無をお選びください',
            'allergy_notes.required'   => 'アレルギーの詳細をご入力ください',
        ]);

        $profile = GuestProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone'           => $request->phone,
                'postal_code'     => $request->postal_code,
                'address'         => $request->address,
                'participation'   => $request->participation,
                'attending_count' => $attending ? ($request->attending_count ?? 1) : 0,
                'children_count'  => $request->children_count ?? 0,
                'has_allergy'     => $attending && $request->input('has_allergy') === '1',
                'allergy_notes'   => $hasAllergy ? $request->allergy_notes : null,
                'notes'           => $request->notes,
                'responded_at'    => now(),
            ]
        );

        CheckInAuditLog::record(
            $profile,
            $user,
            'rsvp_update',
            'rsvp',
            $request->participation === 'attending'
                ? '出席回答を登録しました'
                : '欠席回答を登録しました',
            [
                'participation' => $request->participation,
            ]
        );

        return redirect()->route('invitation')
            ->with('success', 'ご回答ありがとうございます。');
    }

    private function isDeadlinePassed(?WeddingSetting $setting): bool
    {
        return $setting?->rsvp_deadline !== null
            && today()->isAfter($setting->rsvp_deadline);
    }
}
