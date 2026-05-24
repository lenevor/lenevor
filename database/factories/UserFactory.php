<?php

namespace Database\Factories;

use App\Models\User;
use Syscodes\Components\Database\Erostrine\Factories\Factory;
use Syscodes\Components\Support\Facades\Hash;
use Syscodes\Components\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => Str::random(10),
            'email' => Str::random(10).'@lenevor.com',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }
}