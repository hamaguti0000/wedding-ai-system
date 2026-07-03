@extends('layouts.app')
@section('title', 'プロフィールブック | Wedding')

@push('styles')
<link rel="stylesheet" href="{{ css_asset('css/profile-book.css') }}">
@endpush

@section('content')

<div class="pb-wrap">
    <div class="pb-intro">
        <span class="pb-section-en">Profile Book</span>
        <h1 class="pb-section-ja">プロフィールブック</h1>
        <div class="pb-rule"></div>
    </div>

    @if ($pages->isEmpty())
    <div class="pb-empty">
        <p>プロフィールブックは準備中です。</p>
    </div>
    @else
    <div class="pb-book-area">
        <div id="pbBook" class="pb-book"
             data-pages='@json($pages->pluck('url'))'></div>

        <div class="pb-controls">
            <button type="button" id="pbPrev" class="pb-btn" aria-label="前のページ">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <span class="pb-page-indicator" id="pbPageIndicator">1 / {{ $pages->count() }}</span>
            <button type="button" id="pbNext" class="pb-btn" aria-label="次のページ">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
        <p class="pb-hint">ページの端をクリック、またはスワイプでめくれます</p>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/vendor/page-flip.browser.js') }}"></script>
<script src="{{ asset('js/profile-book.js') }}"></script>
@endpush
