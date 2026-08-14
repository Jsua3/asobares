<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            // El panel exige segundo factor, así que una cuenta sin él no
            // llega al escritorio: se queda en la pantalla de alta. Lo normal
            // en una cuenta en uso es tenerlo, y eso es lo que debe producir
            // la factory; para probar la propia exigencia está
            // `sinSegundoFactor()`.
            'has_email_authentication' => true,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /** Cuenta recién creada, todavía sin dar de alta su segundo factor. */
    public function sinSegundoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_email_authentication' => false,
            'app_authentication_secret' => null,
        ]);
    }
}
