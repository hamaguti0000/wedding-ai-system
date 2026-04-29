<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'             => User::factory(),
            'guest_side'          => null,
            'relationship'        => null,
            'relationship_detail' => null,
            'last_name'           => fake()->lastName(),
            'first_name'          => fake()->firstName(),
            'furigana_sei'        => null,
            'furigana_mei'        => null,
            'phone'               => null,
            'postal_code'         => null,
            'address'             => null,
            'participation'       => 'pending',
            'attending_count'     => 1,
            'children_count'      => 0,
            'has_allergy'         => false,
            'dietary_notes'       => null,
            'allergy_notes'       => null,
            'notes'               => null,
            'responded_at'        => null,
        ];
    }
}
