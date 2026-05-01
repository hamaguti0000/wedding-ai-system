<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '結婚式招待ページ | Kakeru＆Mirai')</title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;1,400&family=Noto+Sans+JP:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @if (Auth::user()?->isAdmin())
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
</head>

<body>

    @include('layouts.header')

    <main>
        @yield('content')
    </main>

    @include('layouts.footer')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.13.0/gsap.min.js"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    @stack('scripts')

    @php
        $__cdVisible = Auth::check() && !Auth::user()->isAdmin();
        $__cdDate    = $__cdVisible ? \App\Models\WeddingSetting::value('ceremony_date') : null;
    @endphp
    @if ($__cdDate)
    <div class="countdown" id="countdownWidget" style="display:none;" title="クリックで非表示">
        <span id="countdownDays"></span>
    </div>
    <script>
    (function () {
        const widget = document.getElementById('countdownWidget');
        const label  = document.getElementById('countdownDays');
        const target = new Date('{{ \Carbon\Carbon::parse($__cdDate)->format('Y-m-d') }}T00:00:00+09:00');

        function update() {
            const now  = new Date();
            const diff = target - now;
            if (diff <= 0) { widget.style.display = 'none'; return; }
            const days = Math.floor(diff / 86400000);
            label.textContent = days === 0 ? '本日が挙式です ✦' : `挙式まで あと ${days}日`;
            if (!sessionStorage.getItem('cdDismissed')) {
                widget.style.display = 'block';
            }
        }

        update();
        setInterval(update, 60000);
        widget.addEventListener('click', () => {
            widget.style.display = 'none';
            sessionStorage.setItem('cdDismissed', '1');
        });
    })();
    </script>
    @endif

</body>

</html>
