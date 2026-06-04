<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesor_id')->constrained('users');
            $table->smallInteger('compania');
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('mes');
            $table->decimal('meta_valor', 15, 2)->default(0);
            $table->decimal('meta_unidades', 15, 2)->nullable();
            $table->timestamps();

            $table->unique(['asesor_id', 'compania', 'anio', 'mes'], 'metas_asesor_periodo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_metas');
    }
};
