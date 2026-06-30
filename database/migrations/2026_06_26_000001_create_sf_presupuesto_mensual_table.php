<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sf_presupuesto_mensual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('presupuesto_id')->constrained('sf_presupuesto')->cascadeOnDelete();
            $table->tinyInteger('mes'); // 1-12
            $table->decimal('valor', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['presupuesto_id', 'mes'], 'presupuesto_mensual_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sf_presupuesto_mensual');
    }
};
