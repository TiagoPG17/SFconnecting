<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_negocios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospecto_id')
                ->nullable()
                ->constrained('sf_prospectos')
                ->nullOnDelete();
            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete();
            $table->foreignId('pipeline_estado_id')
                ->constrained('sf_pipeline_estados');
            $table->foreignId('tipo_negocio_id')
                ->nullable()
                ->constrained('sf_maestros_comerciales')
                ->nullOnDelete();
            $table->string('nombre_negocio', 200);
            $table->text('descripcion')->nullable();
            $table->decimal('valor_estimado', 15, 2)->default(0);
            $table->unsignedTinyInteger('probabilidad_cierre')->nullable();
            $table->date('fecha_estimada_cierre')->nullable();
            $table->date('fecha_cierre_real')->nullable();
            $table->foreignId('motivo_perdida_id')
                ->nullable()
                ->constrained('sf_maestros_comerciales')
                ->nullOnDelete();
            $table->foreignId('asesor_id')->constrained('users');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_estado_id', 'activo']);
            $table->index(['asesor_id', 'fecha_estimada_cierre']);
            $table->index(['prospecto_id']);
            $table->index(['cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_negocios');
    }
};
