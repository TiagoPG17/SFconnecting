<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sf_maestros_comerciales', function (Blueprint $table) {
            $table->enum('tipo', [
                'tipo_negocio',
                'prioridad',
                'fuente_lead',
                'motivo_perdida',
                'tipo_actividad',
                'clasificacion',
                'segmento',
                'sector',
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('sf_maestros_comerciales', function (Blueprint $table) {
            $table->enum('tipo', [
                'tipo_negocio',
                'prioridad',
                'fuente_lead',
                'motivo_perdida',
                'tipo_actividad',
                'clasificacion',
                'segmento',
            ])->change();
        });
    }
};
