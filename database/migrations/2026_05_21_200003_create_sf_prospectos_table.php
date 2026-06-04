<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_prospectos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('empresa', 200);
            $table->string('contacto', 150);
            $table->string('email', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->foreignId('origen_id')
                ->nullable()
                ->constrained('sf_maestros_comerciales')
                ->nullOnDelete();
            $table->foreignId('estado_pipeline_id')
                ->constrained('sf_pipeline_estados');
            $table->foreignId('prioridad_id')
                ->nullable()
                ->constrained('sf_maestros_comerciales')
                ->nullOnDelete();
            $table->decimal('valor_estimado', 15, 2)->nullable();
            $table->unsignedTinyInteger('probabilidad_cierre')->nullable();
            $table->date('fecha_proximo_contacto')->nullable();
            $table->foreignId('asesor_id')->constrained('users');
            $table->text('observaciones')->nullable();
            $table->foreignId('convertido_cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete();
            $table->timestamp('fecha_conversion')->nullable();
            $table->foreignId('convertido_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado_pipeline_id', 'activo']);
            $table->index(['asesor_id', 'fecha_proximo_contacto']);
            $table->index(['convertido_cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_prospectos');
    }
};
