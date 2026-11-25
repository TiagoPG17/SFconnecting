<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Bitácora de rótulos de costales enviados a la Zebra (no toca SGP). Una fila por material impreso. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_rotulos_mp_impresiones', function (Blueprint $table) {
            $table->id();
            $table->string('documento', 20);
            $table->unsignedBigInteger('renglon_id')->comment('sgpforma.dbo.inv_movtodet.id_row');
            $table->string('referencia', 50);
            $table->string('lote', 20)->nullable();
            $table->string('unidad', 10)->nullable();
            $table->decimal('cantidad_total', 18, 5);
            $table->decimal('peso_costal', 18, 5);
            $table->unsignedInteger('num_rotulos');
            $table->string('impresora', 100);
            $table->string('impresora_ip', 45);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_rotulos_mp_impresiones');
    }
};
