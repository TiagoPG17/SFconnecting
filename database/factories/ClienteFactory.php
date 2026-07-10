<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Domain\Clientes\Models\Cliente> */
class ClienteFactory extends Factory
{
    protected $model = \App\Domain\Clientes\Models\Cliente::class;

    public function definition(): array
    {
        return [
            'razon_social' => fake()->company(),
            'nit'          => $this->generarNit(),
            'compania'     => config('crm.compania', 1),
            'email'        => fake()->unique()->companyEmail(),
            'telefono'     => fake()->phoneNumber(),
            'ciudad'       => fake()->randomElement(['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena']),
            'direccion'    => fake()->streetAddress(),
            'estado'       => 'prospecto',
            'notas'        => null,
            'user_id'      => User::factory(),
        ];
    }

    public function activo(): static
    {
        return $this->state(fn () => ['estado' => 'activo']);
    }

    public function inactivo(): static
    {
        return $this->state(fn () => ['estado' => 'inactivo']);
    }

    public function prospecto(): static
    {
        return $this->state(fn () => ['estado' => 'prospecto']);
    }

    public function sinEmail(): static
    {
        return $this->state(fn () => ['email' => null]);
    }

    public function compania(int $compania): static
    {
        return $this->state(fn () => ['compania' => $compania]);
    }

    private function generarNit(): string
    {
        return (string) fake()->unique()->numberBetween(800000000, 999999999);
    }
}
