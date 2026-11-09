@php
    $current = $current ?? 'email';
    $emailDone = $emailDone ?? false;
    $passwordDone = $passwordDone ?? false;

    $steps = [
        'email' => ['number' => '1', 'label' => 'メール登録'],
        'verify' => ['number' => '2', 'label' => 'メール認証'],
        'password' => ['number' => '3', 'label' => 'パスワード変更'],
    ];

    $done = [
        'email' => $emailDone || in_array($current, ['verify', 'password'], true),
        'verify' => $emailDone || $current === 'password',
        'password' => $passwordDone,
    ];
@endphp

<div class="setup-steps" aria-label="初回設定の進行状況">
    @foreach ($steps as $key => $step)
        <div class="setup-step {{ $current === $key ? 'is-current' : '' }} {{ $done[$key] ? 'is-done' : '' }}">
            <span class="setup-step__number">{{ $done[$key] && $current !== $key ? '✓' : $step['number'] }}</span>
            <span class="setup-step__label">{{ $step['label'] }}</span>
        </div>
    @endforeach
</div>
