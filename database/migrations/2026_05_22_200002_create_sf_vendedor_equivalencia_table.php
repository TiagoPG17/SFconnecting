<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_vendedor_equivalencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('users');
            $table->smallInteger('compania');
            $table->string('cod_vendedor_siesa', 20)->nullable();
            $table->string('nombre_vendedor', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['asesor_id', 'compania'], 'vend_equiv_unique');
            $table->index(['compania', 'cod_vendedor_siesa'], 'vend_equiv_siesa_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_vendedor_equivalencia');
    }
};
