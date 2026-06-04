<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_auditoria_pipeline', function (Blueprint $table) {
            $table->id();
            $table->morphs('auditable');
            $table->enum('evento', [
                'cambio_estado',
                'cambio_probabilidad',
                'cambio_forecast',
                'conversion_lead',
                'negocio_ganado',
                'negocio_perdido',
                'reactivacion',
            ]);
            $table->string('estado_anterior', 100)->nullable();
            $table->string('estado_nuevo', 100)->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->foreignId('usuario_id')->constrained('users');
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id', 'evento']);
            $table->index(['usuario_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_auditoria_pipeline');
    }
};
