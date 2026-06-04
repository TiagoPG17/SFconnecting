<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaestrosComercialDemoSeeder extends Seeder
{
    public function run(): void
    {
        $maestros = [
            // Tipos de negocio
            ['tipo' => 'tipo_negocio',    'nombre' => 'Producto',         'color' => '#3b82f6', 'orden' => 1],
            ['tipo' => 'tipo_negocio',    'nombre' => 'Servicio',         'color' => '#8b5cf6', 'orden' => 2],
            ['tipo' => 'tipo_negocio',    'nombre' => 'Renovación',       'color' => '#10b981', 'orden' => 3],
            // Motivos de pérdida
            ['tipo' => 'motivo_perdida',  'nombre' => 'Precio',           'color' => '#ef4444', 'orden' => 1],
            ['tipo' => 'motivo_perdida',  'nombre' => 'Competencia',      'color' => '#f59e0b', 'orden' => 2],
            ['tipo' => 'motivo_perdida',  'nombre' => 'Sin presupuesto',  'color' => '#6366f1', 'orden' => 3],
            ['tipo' => 'motivo_perdida',  'nombre' => 'Timing',           'color' => '#0ea5e9', 'orden' => 4],
            // Fuentes de lead
            ['tipo' => 'fuente_lead',     'nombre' => 'Referido',         'color' => '#10b981', 'orden' => 1],
            ['tipo' => 'fuente_lead',     'nombre' => 'Página web',       'color' => '#3b82f6', 'orden' => 2],
            ['tipo' => 'fuente_lead',     'nombre' => 'Fría',             'color' => '#94a3b8', 'orden' => 3],
            ['tipo' => 'fuente_lead',     'nombre' => 'Evento',           'color' => '#f59e0b', 'orden' => 4],
            // Prioridades
            ['tipo' => 'prioridad',       'nombre' => 'Alta',             'color' => '#ef4444', 'orden' => 1],
            ['tipo' => 'prioridad',       'nombre' => 'Media',            'color' => '#f59e0b', 'orden' => 2],
            ['tipo' => 'prioridad',       'nombre' => 'Baja',             'color' => '#10b981', 'orden' => 3],
        ];

        foreach ($maestros as $m) {
            DB::table('sf_maestros_comerciales')->updateOrInsert(
                ['tipo' => $m['tipo'], 'nombre' => $m['nombre']],
                array_merge($m, [
                    'slug'      => \Illuminate\Support\Str::slug($m['nombre']),
                    'activo'    => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
