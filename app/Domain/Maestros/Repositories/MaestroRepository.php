<?php

declare(strict_types=1);

namespace App\Domain\Maestros\Repositories;

use App\Domain\Maestros\Models\MaestroComercial;
use App\Domain\Pipeline\Models\PipelineEstado;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class MaestroRepository implements MaestroRepositoryInterface
{
    public function porTipo(string $tipo): Collection
    {
        return MaestroComercial::desTipo($tipo)->activos()->ordenados()->get();
    }

    public function todosActivos(): Collection
    {
        return MaestroComercial::activos()->ordenados()->get();
    }

    public function todos(): Collection
    {
        return MaestroComercial::ordenados()->get();
    }

    public function pipelineEstadosPorTipo(string $tipo): Collection
    {
        return PipelineEstado::desTipo($tipo)->activos()->ordenados()->get();
    }

    public function pipelineEstadosTodos(): Collection
    {
        return PipelineEstado::ordenados()->get();
    }

    public function buscarPipelineEstado(int $id): ?PipelineEstado
    {
        return PipelineEstado::find($id);
    }

    public function buscarMaestro(int $id): ?MaestroComercial
    {
        return MaestroComercial::find($id);
    }

    public function crearMaestro(array $datos): MaestroComercial
    {
        $datos['slug']  = Str::slug($datos['nombre']);
        $datos['orden'] = $datos['orden'] ?? (MaestroComercial::where('tipo', $datos['tipo'])->max('orden') + 1);

        return MaestroComercial::create($datos);
    }

    public function actualizarMaestro(MaestroComercial $maestro, array $datos): MaestroComercial
    {
        $datos['slug'] = Str::slug($datos['nombre']);
        $maestro->update($datos);

        return $maestro->fresh();
    }

    public function toggleMaestro(MaestroComercial $maestro): MaestroComercial
    {
        $maestro->update(['activo' => ! $maestro->activo]);

        return $maestro->fresh();
    }

    public function crearPipelineEstado(array $datos): PipelineEstado
    {
        $datos['slug']  = Str::slug($datos['nombre']);
        $datos['orden'] = $datos['orden'] ?? (PipelineEstado::where('tipo', $datos['tipo'])->max('orden') + 1);

        return PipelineEstado::create($datos);
    }

    public function actualizarPipelineEstado(PipelineEstado $estado, array $datos): PipelineEstado
    {
        $datos['slug'] = Str::slug($datos['nombre']);
        $estado->update($datos);

        return $estado->fresh();
    }

    public function togglePipelineEstado(PipelineEstado $estado): PipelineEstado
    {
        $estado->update(['activo' => ! $estado->activo]);

        return $estado->fresh();
    }
}
