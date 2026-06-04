<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Clientes\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Domain\Clientes\Models\Contacto> */
class ContactoFactory extends Factory
{
    protected $model = \App\Domain\Clientes\Models\Contacto::class;

    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'nombre'     => fake()->name(),
            'cargo'      => fake()->jobTitle(),
            'email'      => fake()->email(),
            'telefono'   => fake()->phoneNumber(),
            'principal'  => false,
        ];
    }

    public function principal(): static
    {
        return $this->state(fn () => ['principal' => true]);
    }
}
