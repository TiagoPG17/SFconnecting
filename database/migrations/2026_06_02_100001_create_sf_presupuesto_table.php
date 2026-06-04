<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_presupuesto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('users');
            $table->smallInteger('compania');
            $table->smallInteger('anio');
            $table->decimal('presupuesto', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['asesor_id', 'compania', 'anio'], 'presupuesto_unique');
            $table->index(['compania', 'anio'], 'presupuesto_periodo_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_presupuesto');
    }
};
