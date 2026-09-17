<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_reemplazo_clientes_movidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendedor_equivalencia_id');
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->unsignedBigInteger('titular_user_id');
            $table->timestamp('revertido_en')->nullable();
            $table->timestamps();

            $table->index('vendedor_equivalencia_id', 'reemplazo_clientes_vendedor_equiv_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_reemplazo_clientes_movidos');
    }
};
