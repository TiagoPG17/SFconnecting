<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_comision_tramos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regla_id')->constrained('sf_comision_reglas')->cascadeOnDelete();
            $table->decimal('desde_valor', 15, 2)->default(0);
            $table->decimal('hasta_valor', 15, 2)->nullable();
            $table->decimal('porcentaje', 5, 2);
            $table->timestamps();

            $table->index('regla_id', 'comision_tramos_regla_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_comision_tramos');
    }
};
