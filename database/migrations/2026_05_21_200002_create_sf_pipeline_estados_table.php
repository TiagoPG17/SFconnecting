<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_pipeline_estados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->enum('tipo', ['prospecto', 'negocio']);
            $table->string('color', 20)->default('#6B7280');
            $table->string('icono', 50)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->unsignedTinyInteger('porcentaje_cierre')->default(0);
            $table->boolean('es_final')->default(false);
            $table->boolean('es_ganado')->default(false);
            $table->boolean('es_perdido')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['tipo', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_pipeline_estados');
    }
};
