<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_solicitudes_credito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('sf_negocios');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('pipeline_estado_id')->constrained('sf_pipeline_estados');
            $table->foreignId('asesor_id')->constrained('users');
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('monto_solicitado', 15, 2);
            $table->unsignedSmallInteger('plazo_solicitado_dias')->nullable();
            $table->text('justificacion')->nullable();
            $table->json('dossier_erp')->nullable();
            $table->text('comentario_revision')->nullable();
            $table->text('condiciones')->nullable();
            $table->timestamp('revisado_en')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_estado_id']);
            $table->index(['negocio_id']);
            $table->index(['cliente_id']);
            $table->index(['asesor_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_solicitudes_credito');
    }
};
