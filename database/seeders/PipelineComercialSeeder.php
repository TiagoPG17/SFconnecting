<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PipelineComercialSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedMaestros();
        $this->seedPipelineEstados();
    }

    private function seedMaestros(): void
    {
        $maestros = [
            // Fuentes de leads
            ['tipo' => 'fuente_lead', 'nombre' => 'Sitio Web', 'slug' => 'sitio-web', 'icono' => 'globe', 'orden' => 1],
            ['tipo' => 'fuente_lead', 'nombre' => 'Referido', 'slug' => 'referido', 'icono' => 'users', 'orden' => 2],
            ['tipo' => 'fuente_lead', 'nombre' => 'LinkedIn', 'slug' => 'linkedin', 'icono' => 'linkedin', 'orden' => 3],
            ['tipo' => 'fuente_lead', 'nombre' => 'Llamada en frío', 'slug' => 'llamada-fria', 'icono' => 'phone', 'orden' => 4],
            ['tipo' => 'fuente_lead', 'nombre' => 'Evento / Feria', 'slug' => 'evento-feria', 'icono' => 'calendar', 'orden' => 5],
            ['tipo' => 'fuente_lead', 'nombre' => 'Otro', 'slug' => 'otro', 'icono' => 'more-horizontal', 'orden' => 99],

            // Prioridades
            ['tipo' => 'prioridad', 'nombre' => 'Alta', 'slug' => 'alta', 'color' => '#EF4444', 'icono' => 'chevrons-up', 'orden' => 1],
            ['tipo' => 'prioridad', 'nombre' => 'Media', 'slug' => 'media', 'color' => '#F59E0B', 'icono' => 'minus', 'orden' => 2],
            ['tipo' => 'prioridad', 'nombre' => 'Baja', 'slug' => 'baja', 'color' => '#6B7280', 'icono' => 'chevrons-down', 'orden' => 3],

            // Tipos de negocio
            ['tipo' => 'tipo_negocio', 'nombre' => 'Nuevo', 'slug' => 'nuevo', 'orden' => 1],
            ['tipo' => 'tipo_negocio', 'nombre' => 'Renovación', 'slug' => 'renovacion', 'orden' => 2],
            ['tipo' => 'tipo_negocio', 'nombre' => 'Expansión', 'slug' => 'expansion', 'orden' => 3],
            ['tipo' => 'tipo_negocio', 'nombre' => 'Upsell', 'slug' => 'upsell', 'orden' => 4],

            // Motivos de pérdida
            ['tipo' => 'motivo_perdida', 'nombre' => 'Precio', 'slug' => 'precio', 'orden' => 1],
            ['tipo' => 'motivo_perdida', 'nombre' => 'Competencia', 'slug' => 'competencia', 'orden' => 2],
            ['tipo' => 'motivo_perdida', 'nombre' => 'No hay presupuesto', 'slug' => 'sin-presupuesto', 'orden' => 3],
            ['tipo' => 'motivo_perdida', 'nombre' => 'Timing', 'slug' => 'timing', 'orden' => 4],
            ['tipo' => 'motivo_perdida', 'nombre' => 'No calificó', 'slug' => 'no-califico', 'orden' => 5],
            ['tipo' => 'motivo_perdida', 'nombre' => 'Sin respuesta', 'slug' => 'sin-respuesta', 'orden' => 6],

            // Tipos de actividad
            ['tipo' => 'tipo_actividad', 'nombre' => 'Llamada', 'slug' => 'llamada', 'icono' => 'phone', 'orden' => 1],
            ['tipo' => 'tipo_actividad', 'nombre' => 'Reunión', 'slug' => 'reunion', 'icono' => 'video', 'orden' => 2],
            ['tipo' => 'tipo_actividad', 'nombre' => 'Email', 'slug' => 'email', 'icono' => 'mail', 'orden' => 3],
            ['tipo' => 'tipo_actividad', 'nombre' => 'Demo', 'slug' => 'demo', 'icono' => 'monitor', 'orden' => 4],
            ['tipo' => 'tipo_actividad', 'nombre' => 'Propuesta', 'slug' => 'propuesta', 'icono' => 'file-text', 'orden' => 5],

            // Clasificaciones
            ['tipo' => 'clasificacion', 'nombre' => 'Enterprise', 'slug' => 'enterprise', 'orden' => 1],
            ['tipo' => 'clasificacion', 'nombre' => 'PYME', 'slug' => 'pyme', 'orden' => 2],
            ['tipo' => 'clasificacion', 'nombre' => 'Startup', 'slug' => 'startup', 'orden' => 3],
            ['tipo' => 'clasificacion', 'nombre' => 'Gobierno', 'slug' => 'gobierno', 'orden' => 4],

            // Segmentos
            ['tipo' => 'segmento', 'nombre' => 'Tecnología', 'slug' => 'tecnologia', 'orden' => 1],
            ['tipo' => 'segmento', 'nombre' => 'Retail', 'slug' => 'retail', 'orden' => 2],
            ['tipo' => 'segmento', 'nombre' => 'Manufactura', 'slug' => 'manufactura', 'orden' => 3],
            ['tipo' => 'segmento', 'nombre' => 'Servicios', 'slug' => 'servicios', 'orden' => 4],
            ['tipo' => 'segmento', 'nombre' => 'Salud', 'slug' => 'salud', 'orden' => 5],
            ['tipo' => 'segmento', 'nombre' => 'Educación', 'slug' => 'educacion', 'orden' => 6],
        ];

        foreach ($maestros as $maestro) {
            DB::table('sf_maestros_comerciales')->updateOrInsert(
                ['tipo' => $maestro['tipo'], 'slug' => $maestro['slug']],
                array_merge($maestro, [
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    private function seedPipelineEstados(): void
    {
        $estados = [
            // Pipeline de prospectos
            [
                'nombre' => 'Nuevo Lead',
                'slug' => 'nuevo-lead',
                'tipo' => 'prospecto',
                'color' => '#6B7280',
                'icono' => 'user-plus',
                'orden' => 1,
                'porcentaje_cierre' => 5,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Contactado',
                'slug' => 'contactado',
                'tipo' => 'prospecto',
                'color' => '#3B82F6',
                'icono' => 'phone-call',
                'orden' => 2,
                'porcentaje_cierre' => 15,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Calificado',
                'slug' => 'calificado',
                'tipo' => 'prospecto',
                'color' => '#8B5CF6',
                'icono' => 'check-circle',
                'orden' => 3,
                'porcentaje_cierre' => 30,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Convertido',
                'slug' => 'convertido',
                'tipo' => 'prospecto',
                'color' => '#10B981',
                'icono' => 'arrow-right-circle',
                'orden' => 4,
                'porcentaje_cierre' => 100,
                'es_final' => true,
                'es_ganado' => true,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Descartado',
                'slug' => 'descartado',
                'tipo' => 'prospecto',
                'color' => '#EF4444',
                'icono' => 'x-circle',
                'orden' => 5,
                'porcentaje_cierre' => 0,
                'es_final' => true,
                'es_ganado' => false,
                'es_perdido' => true,
            ],

            // Pipeline de negocios
            [
                'nombre' => 'Prospectando',
                'slug' => 'prospectando',
                'tipo' => 'negocio',
                'color' => '#6B7280',
                'icono' => 'search',
                'orden' => 1,
                'porcentaje_cierre' => 10,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Reunión agendada',
                'slug' => 'reunion-agendada',
                'tipo' => 'negocio',
                'color' => '#3B82F6',
                'icono' => 'calendar',
                'orden' => 2,
                'porcentaje_cierre' => 25,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Propuesta enviada',
                'slug' => 'propuesta-enviada',
                'tipo' => 'negocio',
                'color' => '#F59E0B',
                'icono' => 'file-text',
                'orden' => 3,
                'porcentaje_cierre' => 50,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Negociación',
                'slug' => 'negociacion',
                'tipo' => 'negocio',
                'color' => '#F97316',
                'icono' => 'handshake',
                'orden' => 4,
                'porcentaje_cierre' => 75,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Ganado',
                'slug' => 'ganado',
                'tipo' => 'negocio',
                'color' => '#10B981',
                'icono' => 'trophy',
                'orden' => 5,
                'porcentaje_cierre' => 100,
                'es_final' => true,
                'es_ganado' => true,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Perdido',
                'slug' => 'perdido',
                'tipo' => 'negocio',
                'color' => '#EF4444',
                'icono' => 'x-circle',
                'orden' => 6,
                'porcentaje_cierre' => 0,
                'es_final' => true,
                'es_ganado' => false,
                'es_perdido' => true,
            ],

            // Pipeline de solicitudes de crédito
            [
                'nombre' => 'Radicada',
                'slug' => 'credito-radicada',
                'tipo' => 'solicitud_credito',
                'color' => '#6B7280',
                'icono' => 'file-plus',
                'orden' => 1,
                'porcentaje_cierre' => 10,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'En Revisión',
                'slug' => 'credito-en-revision',
                'tipo' => 'solicitud_credito',
                'color' => '#3B82F6',
                'icono' => 'search',
                'orden' => 2,
                'porcentaje_cierre' => 50,
                'es_final' => false,
                'es_ganado' => false,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Aprobada',
                'slug' => 'credito-aprobada',
                'tipo' => 'solicitud_credito',
                'color' => '#10B981',
                'icono' => 'check-circle',
                'orden' => 3,
                'porcentaje_cierre' => 100,
                'es_final' => true,
                'es_ganado' => true,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Aprobada con Condiciones',
                'slug' => 'credito-aprobada-condiciones',
                'tipo' => 'solicitud_credito',
                'color' => '#F59E0B',
                'icono' => 'alert-triangle',
                'orden' => 4,
                'porcentaje_cierre' => 100,
                'es_final' => true,
                'es_ganado' => true,
                'es_perdido' => false,
            ],
            [
                'nombre' => 'Rechazada',
                'slug' => 'credito-rechazada',
                'tipo' => 'solicitud_credito',
                'color' => '#EF4444',
                'icono' => 'x-circle',
                'orden' => 5,
                'porcentaje_cierre' => 0,
                'es_final' => true,
                'es_ganado' => false,
                'es_perdido' => true,
            ],
        ];

        foreach ($estados as $estado) {
            DB::table('sf_pipeline_estados')->updateOrInsert(
                ['slug' => $estado['slug']],
                array_merge($estado, [
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
