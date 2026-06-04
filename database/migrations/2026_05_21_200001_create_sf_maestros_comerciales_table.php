<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_maestros_comerciales', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', [
                'tipo_negocio',
                'prioridad',
                'fuente_lead',
                'motivo_perdida',
                'tipo_actividad',
                'clasificacion',
                'segmento',
            ]);
            $table->string('nombre', 100);
            $table->string('slug', 100);
            $table->string('color', 20)->nullable();
            $table->string('icono', 50)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['tipo', 'slug']);
            $table->index(['tipo', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_maestros_comerciales');
    }
};
