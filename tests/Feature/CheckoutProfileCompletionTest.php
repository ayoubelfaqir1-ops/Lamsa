<?php

use App\Models\User;

test('checkout redirects to profile when phone is missing', function () {
    $user = User::factory()->create([
        'phone' => null,
        'address' => '123 Test Street',
    ]);

    $response = $this->actingAs($user)->get(route('orders.create'));

    $response
        ->assertRedirect(route('profile'))
        ->assertSessionHas('error', 'Please complete your phone number and address before checking out.');
});

test('checkout redirects to profile when address is missing', function () {
    $user = User::factory()->create([
        'phone' => '+212600000000',
        'address' => null,
    ]);

    $response = $this->actingAs($user)->get(route('orders.create'));

    $response
        ->assertRedirect(route('profile'))
        ->assertSessionHas('error', 'Please complete your phone number and address before checking out.');
});

test('checkout is not blocked by profile middleware when phone and address are present', function () {
    $user = User::factory()->create([
        'phone' => '+212600000000',
        'address' => '123 Test Street',
    ]);

    $response = $this->actingAs($user)->get(route('orders.create'));

    $response
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('error', 'Your cart is empty.');
});
