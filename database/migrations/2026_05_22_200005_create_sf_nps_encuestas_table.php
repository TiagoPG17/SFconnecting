<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_nps_encuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('negocio_id')->nullable()->constrained('sf_negocios')->nullOnDelete();
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('comentario')->nullable();
            $table->enum('canal', ['email', 'whatsapp', 'telefono', 'presencial', 'otro'])->default('email');
            $table->timestamp('fecha');
            $table->timestamps();

            $table->index(['cliente_id', 'fecha'], 'nps_cliente_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_nps_encuestas');
    }
};
