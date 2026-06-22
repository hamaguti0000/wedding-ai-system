@extends('layouts.app')
@section('title', '招待状 | Kakeru & Mirai Wedding')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/invitation.css') }}">
@endpush

@section('content')

{{-- ══ バナーヘッダー ══════════════════════════════════ --}}
<section class="inv-banner">
    <img src="{{ ($bannerImage?->url ?? asset('img/チャペル.jpg')) }}" alt="" class="inv-banner__img">
    <div class="inv-banner__overlay"></div>
    <div class="inv-banner__text">
        <span class="inv-banner__eyebrow">RSVP · ご出欠のご確認</span>
        <h1 class="inv-banner__title">
            @if ($setting)
                {{ $setting->groom_name_en ?: $setting->groom_name }}
                <em>&amp;</em>
                {{ $setting->bride_name_en ?: $setting->bride_name }}
            @else
                Kakeru <em>&amp;</em> Mirai
            @endif
        </h1>
        <div class="inv-banner__rule"></div>
        <p class="inv-banner__date">
            {{ $setting?->ceremony_date?->format('F j, Y') ?? 'July 19, 2026' }}
        </p>
    </div>
</section>

{{-- ══ メインコンテンツ ═════════════════════════════════ --}}
<div class="inv-wrap">

    {{-- 回答済みステータス --}}
    @if ($profile && $profile->responded_at && $profile->participation !== 'pending')
    <div class="inv-status inv-status--{{ $profile->participation }}">
        <span class="inv-status__icon">{{ $profile->isAttending() ? '✦' : '✧' }}</span>
        <div>
            <div class="inv-status__label">
                {{ $profile->fullName() }} 様 ／ {{ $profile->participationLabel() }} でご登録済みです
            </div>
            <div class="inv-status__date">
                {{ $profile->responded_at->format('Y年m月d日 H:i') }} 回答 ・ 内容はいつでも更新できます
            </div>
        </div>
    </div>
    @endif

    @if ($deadlinePassed)
    <div class="inv-alert inv-alert--deadline">
        <span class="inv-alert__icon">🔒</span>
        <div>
            <strong>出欠回答の受付は終了しました</strong>
            @if ($setting?->rsvp_deadline)
            <span class="inv-alert__sub">（締め切り：{{ $setting->rsvp_deadline->format('Y年n月j日') }}）</span>
            @endif
        </div>
    </div>
    @endif

    @if (session('success'))
    <div class="inv-alert inv-alert--success">{{ session('success') }}</div>
    @endif

    @if (session('deadline_error'))
    <div class="inv-alert inv-alert--deadline">{{ session('deadline_error') }}</div>
    @endif

    @if ($errors->any())
    <div class="inv-alert inv-alert--error">
        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    @php
        $cur        = old('participation', $profile->participation ?? 'pending');
        $allergyVal = old('has_allergy', $profile ? ($profile->has_allergy ? '1' : '0') : '0');

        $restoreDraft = (!$profile || $profile->participation === 'pending') && !$errors->any() ? '1' : '0';
        $hasErr = fn(string $f): string => $errors->has($f) ? 'has-error' : '';
    @endphp

    <form method="POST" action="{{ route('invitation.update') }}"
          id="rsvpForm"
          data-restore-draft="{{ $restoreDraft }}">
        @csrf

        {{-- ══ CARD 1: 出欠のご確認 ══════════════════════ --}}
        <div class="inv-card">
            <div class="inv-card__head {{ $errors->has('participation') ? 'inv-card__head--has-error' : '' }}">
                <span class="inv-section-en">ご出欠</span>
                <h2 class="inv-section-ja">出欠のご確認</h2>
                <div class="inv-section-rule"></div>
            </div>

            <div class="inv-participation">
                <button type="button"
                    class="inv-part-btn attending {{ $cur === 'attending' ? 'selected' : '' }}"
                    data-value="attending">
                    <span class="inv-part-btn__icon">✦</span>
                    <span class="inv-part-btn__main">出席します</span>
                    <span class="inv-part-btn__label">ATTEND</span>
                </button>
                <button type="button"
                    class="inv-part-btn declining {{ $cur === 'declining' ? 'selected' : '' }}"
                    data-value="declining">
                    <span class="inv-part-btn__icon">✧</span>
                    <span class="inv-part-btn__main">欠席します</span>
                    <span class="inv-part-btn__label">DECLINE</span>
                </button>
            </div>
            <input type="hidden" name="participation" id="participation" value="{{ $cur }}">
            @error('participation')
            <p class="field-error" style="text-align:center;margin-top:12px;">{{ $message }}</p>
            @enderror
        </div>

        {{-- ══ CARD 3: ご連絡先（常時表示）══════════════════ --}}
        <div class="inv-card">
            <div class="inv-card__head {{ $errors->hasAny(['phone','postal_code','address']) ? 'inv-card__head--has-error' : '' }}">
                <span class="inv-section-en">連絡先</span>
                <h2 class="inv-section-ja">ご連絡先</h2>
                <div class="inv-section-rule"></div>
            </div>

            <div class="form-row">
                <div class="form-group {{ $hasErr('phone') }}">
                    <label>電話番号</label>
                    <input type="tel" name="phone"
                        value="{{ old('phone', $profile->phone ?? '') }}"
                        placeholder="090-0000-0000" autocomplete="tel" inputmode="tel">
                    @error('phone')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>郵便番号</label>
                    <input type="text" name="postal_code"
                        value="{{ old('postal_code', $profile->postal_code ?? '') }}"
                        placeholder="000-0000" maxlength="8" inputmode="numeric">
                    <p class="field-note">お礼状の送付に使用します</p>
                </div>
            </div>

            <div class="form-group {{ $hasErr('address') }}">
                <label>ご住所</label>
                <input type="text" name="address"
                    value="{{ old('address', $profile->address ?? '') }}"
                    placeholder="東京都◯◯区◯◯町1-2-3"
                    autocomplete="street-address">
            </div>
        </div>

        {{-- ══ 出席時のみ表示（CARD 4: ご出席の詳細）══════ --}}
        <div class="inv-section-toggle {{ $cur !== 'attending' ? 'hidden' : '' }}">
        <div class="inv-section-toggle__inner">

        {{-- ── CARD 4: ご出席の詳細 ── --}}
        <div class="inv-card">
            <div class="inv-card__head {{ $errors->hasAny(['attending_count','children_count','has_allergy','allergy_notes']) ? 'inv-card__head--has-error' : '' }}">
                <span class="inv-section-en">ご出席の詳細</span>
                <h2 class="inv-section-ja">人数・アレルギー</h2>
                <div class="inv-section-rule"></div>
            </div>

            <div class="count-row">
                <div class="form-group {{ $hasErr('attending_count') }}">
                    <label>ご出席人数（合計） <span class="req">*</span></label>
                    <select name="attending_count" id="attendingCount">
                        @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ old('attending_count', $profile->attending_count ?? 1) == $i ? 'selected' : '' }}>{{ $i }}名</option>
                        @endfor
                    </select>
                    <p class="field-note">ご本人を含む合計人数</p>
                    @error('attending_count')<span class="field-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label>うちお子様（12歳以下）</label>
                    <select name="children_count">
                        @for ($i = 0; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ old('children_count', $profile->children_count ?? 0) == $i ? 'selected' : '' }}>{{ $i }}名</option>
                        @endfor
                    </select>
                </div>
            </div>

            {{-- アレルギー有無 --}}
            <div class="form-group {{ $hasErr('has_allergy') }}">
                <label>食物アレルギー <span class="req">*</span></label>
                <div class="inv-btn-group">
                    <button type="button"
                        class="inv-btn-option {{ $allergyVal === '0' ? 'selected' : '' }}"
                        data-target="hasAllergy" data-value="0">
                        <span class="inv-btn-option__sub">なし</span>
                    </button>
                    <button type="button"
                        class="inv-btn-option {{ $allergyVal === '1' ? 'selected' : '' }}"
                        data-target="hasAllergy" data-value="1">
                        <span class="inv-btn-option__sub">あり</span>
                    </button>
                </div>
                <input type="hidden" name="has_allergy" id="hasAllergy" value="{{ $allergyVal }}">
                @error('has_allergy')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            {{-- アレルギー詳細（条件付き表示）--}}
            <div class="inv-conditional {{ $allergyVal === '1' ? 'visible' : '' }}" id="allergyDetail">
                <div>
                    <div class="form-group {{ $hasErr('allergy_notes') }}">
                        <label>アレルギーの詳細 <span class="req">*</span></label>
                        <textarea name="allergy_notes"
                            placeholder="例：卵・乳製品アレルギーがあります。&#10;例：ナッツ類は全般的に不可です。&#10;宗教上の制限がある場合もご記入ください。">{{ old('allergy_notes', $profile->allergy_notes ?? '') }}</textarea>
                        <p class="field-note">会場へ事前にお伝えします。できるだけ具体的にご記入ください。</p>
                        @error('allergy_notes')<span class="field-error">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>

        </div>{{-- /.inv-card --}}

        </div>{{-- /.inv-section-toggle__inner --}}
        </div>{{-- /.inv-section-toggle --}}

        {{-- ══ CARD 5: おふたりへのメッセージ（常時）════ --}}
        <div class="inv-card">
            <div class="inv-card__head">
                <span class="inv-section-en">メッセージ</span>
                <h2 class="inv-section-ja">おふたりへ</h2>
                <div class="inv-section-rule"></div>
            </div>
            <div class="form-group {{ $hasErr('notes') }}">
                <label>お祝いのメッセージ</label>
                <textarea name="notes"
                    placeholder="おふたりへのメッセージをお聞かせください。（任意）">{{ old('notes', $profile->notes ?? '') }}</textarea>
                @error('notes')<span class="field-error">{{ $message }}</span>@enderror
            </div>
        </div>

        {{-- ══ 送信ボタン ══════════════════════════════════ --}}
        <div class="inv-submit-wrap">
            @if ($deadlinePassed)
            <button type="button" class="inv-submit inv-submit--disabled" disabled>
                受付終了
            </button>
            @else
            <button type="submit" class="inv-submit">
                {{ $profile && $profile->participation !== 'pending' ? '回答を更新する' : '回答を送信する' }}
            </button>
            @endif
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/invitation.js') }}"></script>
@endpush
