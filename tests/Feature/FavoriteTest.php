<?php

use App\Models\Favorite;
use App\Models\Furniture;
use App\Models\User;
use Illuminate\Database\QueryException;

test('creates a favorite linking a user to furniture', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create();

    $favorite = Favorite::factory()->create([
        'user_id' => $user->id,
        'furniture_id' => $furniture->furniture_id,
    ]);

    expect($favorite->user_id)->toBe($user->id)
        ->and($favorite->furniture_id)->toBe($furniture->furniture_id)
        ->and($favorite->user->is($user))->toBeTrue()
        ->and($favorite->furniture->is($furniture))->toBeTrue();
});

test('furniture is exposed through the user favorites relation', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create();

    Favorite::factory()->create([
        'user_id' => $user->id,
        'furniture_id' => $furniture->furniture_id,
    ]);

    expect($user->favorites)
        ->toHaveCount(1)
        ->first()->furniture_id->toBe($furniture->furniture_id);
});

test('rejects adding the same furniture twice for one user', function () {
    $user = User::factory()->create();
    $furniture = Furniture::factory()->create();

    Favorite::factory()->create([
        'user_id' => $user->id,
        'furniture_id' => $furniture->furniture_id,
    ]);

    expect(fn () => Favorite::factory()->create([
        'user_id' => $user->id,
        'furniture_id' => $furniture->furniture_id,
    ]))->toThrow(QueryException::class);
});
