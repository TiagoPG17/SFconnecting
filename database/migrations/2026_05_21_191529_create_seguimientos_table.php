<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('contacto_id')->nullable()->constrained('contactos')->nullOnDelete();
            $table->enum('tipo', ['llamada', 'reunion', 'email', 'visita', 'whatsapp', 'otro'])->default('llamada');
            $table->enum('resultado', ['exitoso', 'no_contactado', 'pendiente', 'cancelado'])->default('pendiente');
            $table->text('descripcion');
            $table->timestamp('fecha_seguimiento');
            $table->timestamp('proxima_fecha')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'fecha_seguimiento']);
            $table->index(['user_id', 'proxima_fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};
