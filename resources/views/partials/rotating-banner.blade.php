@php
    $slides = collect($images ?? $bannerImages ?? [])->filter();
    if ($slides->isEmpty() && isset($image) && $image) {
        $slides = collect([$image]);
    }
    $fallbackSrc = $fallback ?? asset('img/チャペル.jpg');
    $imgClass = $class ?? '';
@endphp

@if ($slides->isNotEmpty())
    @foreach ($slides as $i => $slide)
        <img src="{{ $slide->url }}" alt="{{ $alt ?? '' }}"
             class="{{ trim($imgClass . ' js-rotating-banner-slide ' . ($i === 0 ? 'is-active' : '')) }}">
    @endforeach
@else
    <img src="{{ $fallbackSrc }}" alt="{{ $alt ?? '' }}" class="{{ $imgClass }}">
@endif
