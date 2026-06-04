<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Clientes\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Domain\Seguimientos\Models\Seguimiento> */
class SeguimientoFactory extends Factory
{
    protected $model = \App\Domain\Seguimientos\Models\Seguimiento::class;

    public function definition(): array
    {
        return [
            'cliente_id'        => Cliente::factory(),
            'user_id'           => User::factory(),
            'contacto_id'       => null,
            'tipo'              => fake()->randomElement(['llamada', 'reunion', 'email', 'visita', 'whatsapp']),
            'resultado'         => fake()->randomElement(['exitoso', 'no_contactado', 'pendiente']),
            'descripcion'       => fake()->paragraph(),
            'fecha_seguimiento' => fake()->dateTimeBetween('-6 months', 'now'),
            'proxima_fecha'     => fake()->optional(0.6)->dateTimeBetween('now', '+3 months'),
        ];
    }

    public function exitoso(): static
    {
        return $this->state(fn () => ['resultado' => 'exitoso']);
    }

    public function pendiente(): static
    {
        return $this->state(fn () => ['resultado' => 'pendiente', 'proxima_fecha' => now()->addDays(7)]);
    }

    public function futuro(): static
    {
        return $this->state(fn () => [
            'fecha_seguimiento' => now()->addDays(3),
            'resultado'         => 'pendiente',
        ]);
    }
}
