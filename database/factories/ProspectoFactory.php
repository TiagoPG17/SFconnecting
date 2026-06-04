<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Pipeline\Models\PipelineEstado;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Domain\Prospectos\Models\Prospecto> */
class ProspectoFactory extends Factory
{
    protected $model = \App\Domain\Prospectos\Models\Prospecto::class;

    public function definition(): array
    {
        return [
            'codigo'            => strtoupper(fake()->unique()->bothify('PRO-####')),
            'empresa'           => fake()->company(),
            'contacto'          => fake()->name(),
            'email'             => fake()->optional()->safeEmail(),
            'telefono'          => fake()->optional()->phoneNumber(),
            'estado_pipeline_id' => PipelineEstado::factory(),
            'asesor_id'         => User::factory(),
            'activo'            => true,
        ];
    }
}
