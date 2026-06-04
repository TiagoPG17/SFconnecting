<?php

declare(strict_types=1);

namespace App\Domain\Maestros\Repositories;

use App\Domain\Maestros\Models\MaestroComercial;
use App\Domain\Pipeline\Models\PipelineEstado;
use Illuminate\Database\Eloquent\Collection;

interface MaestroRepositoryInterface
{
    public function porTipo(string $tipo): Collection;

    public function todosActivos(): Collection;

    public function todos(): Collection;

    public function pipelineEstadosPorTipo(string $tipo): Collection;

    public function pipelineEstadosTodos(): Collection;

    public function buscarPipelineEstado(int $id): ?PipelineEstado;

    public function buscarMaestro(int $id): ?MaestroComercial;

    public function crearMaestro(array $datos): MaestroComercial;

    public function actualizarMaestro(MaestroComercial $maestro, array $datos): MaestroComercial;

    public function toggleMaestro(MaestroComercial $maestro): MaestroComercial;

    public function crearPipelineEstado(array $datos): PipelineEstado;

    public function actualizarPipelineEstado(PipelineEstado $estado, array $datos): PipelineEstado;

    public function togglePipelineEstado(PipelineEstado $estado): PipelineEstado;
}
