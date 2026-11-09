<?php

namespace App\Http\Controllers;

use App\Models\WeddingSetting;

class CoupleProfileController extends Controller
{
    public function index()
    {
        return view('profiles.index', [
            'setting' => WeddingSetting::first(),
        ]);
    }

    public function show(string $person)
    {
        abort_unless(in_array($person, ['groom', 'bride']), 404);

        return view('profiles.show', [
            'setting' => WeddingSetting::first(),
            'person'  => $person,
        ]);
    }
}
