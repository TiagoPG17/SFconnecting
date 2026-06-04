<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_comision_reglas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('base', ['ganado', 'pipeline_ponderado', 'facturado'])->default('ganado');
            $table->decimal('porc_default', 5, 2)->default(0);
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_comision_reglas');
    }
};
