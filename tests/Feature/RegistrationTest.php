<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('GET /register は公開しない', function () {
    $this->get('/register')->assertNotFound();
});

it('POST /register は公開しない', function () {
    $this->post('/register', [
        'last_name' => '山田',
        'first_name' => '太郎',
        'email' => 'taro@example.com',
        'password' => 'SecurePass1',
        'password_confirmation' => 'SecurePass1',
    ])->assertNotFound();
});

it('ログイン済みでも /register は公開しない', function () {
    $this->actingAs(makeGuest('attending'))
        ->get('/register')
        ->assertNotFound();
});
