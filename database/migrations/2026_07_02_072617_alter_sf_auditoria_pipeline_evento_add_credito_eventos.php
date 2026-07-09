<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sf_auditoria_pipeline', function (Blueprint $table) {
            $table->enum('evento', [
                'cambio_estado',
                'cambio_probabilidad',
                'cambio_forecast',
                'conversion_lead',
                'negocio_ganado',
                'negocio_perdido',
                'reactivacion',
                'solicitud_credito_aprobada',
                'solicitud_credito_aprobada_condiciones',
                'solicitud_credito_rechazada',
            ])->change();
        });
    }

    public function down(): void
    {
        Schema::table('sf_auditoria_pipeline', function (Blueprint $table) {
            $table->enum('evento', [
                'cambio_estado',
                'cambio_probabilidad',
                'cambio_forecast',
                'conversion_lead',
                'negocio_ganado',
                'negocio_perdido',
                'reactivacion',
            ])->change();
        });
    }
};
