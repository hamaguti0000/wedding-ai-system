<?php

use App\Models\User;

it('avatar emoji groups are categorized and flattened options remain available', function () {
    $groups = User::avatarEmojiGroups();

    expect($groups)->toBeArray();
    expect($groups)->not->toBeEmpty();
    expect(array_column($groups, 'key'))->toContain('nature', 'animals', 'food', 'symbols');
    expect(User::avatarEmojiOptions())->toHaveKeys(['🌸', '🐶', '🍰', '🎀']);
});

it('emoji avatar background defaults to white when unset or invalid', function () {
    $user = new User([
        'name' => 'テスト',
        'avatar_type' => User::AVATAR_EMOJI,
    ]);

    expect($user->avatarBackgroundColor())->toBe('#ffffff');

    $user->avatar_bg_color = '#123abc';
    expect($user->avatarBackgroundColor())->toBe('#123abc');

    $user->avatar_bg_color = 'not-a-color';
    expect($user->avatarBackgroundColor())->toBe('#ffffff');
});

it('avatar border settings default to a soft gold ring and clamp width safely', function () {
    $user = new User(['name' => 'テスト']);

    expect($user->avatarBorderColor())->toBe('#f0e4d0');
    expect($user->avatarBorderWidth())->toBe(3);

    $user->avatar_border_color = '#123abc';
    $user->avatar_border_width = 7;
    expect($user->avatarBorderColor())->toBe('#123abc');
    expect($user->avatarBorderWidth())->toBe(7);

    $user->avatar_border_color = 'broken';
    $user->avatar_border_width = 99;
    expect($user->avatarBorderColor())->toBe('#f0e4d0');
    expect($user->avatarBorderWidth())->toBe(10);
});
