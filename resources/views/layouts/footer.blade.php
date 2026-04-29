@php
    $footerSetting = \App\Models\WeddingSetting::first();
    $groomName = $footerSetting?->groom_name ?? 'Kakeru';
    $brideName = $footerSetting?->bride_name ?? 'Mirai';
@endphp
<footer>
    <p>&copy; {{ now()->year }} {{ $groomName }} &amp; {{ $brideName }} Wedding</p>
</footer>
