<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<\App\Domain\Pipeline\Models\PipelineEstado> */
class PipelineEstadoFactory extends Factory
{
    protected $model = \App\Domain\Pipeline\Models\PipelineEstado::class;

    public function definition(): array
    {
        $nombre = fake()->unique()->words(2, true);

        return [
            'nombre'            => ucwords($nombre),
            'slug'              => Str::slug($nombre) . '-' . fake()->unique()->randomNumber(4),
            'tipo'              => fake()->randomElement(['prospecto', 'negocio']),
            'color'             => '#' . fake()->hexColor(),
            'orden'             => fake()->numberBetween(1, 20),
            'porcentaje_cierre' => fake()->numberBetween(0, 100),
            'es_final'          => false,
            'es_ganado'         => false,
            'es_perdido'        => false,
            'activo'            => true,
        ];
    }
}
