<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sf_pipeline_estados', function (Blueprint $table) {
            $table->enum('tipo', ['prospecto', 'negocio', 'solicitud_credito'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('sf_pipeline_estados', function (Blueprint $table) {
            $table->enum('tipo', ['prospecto', 'negocio'])->change();
        });
    }
};
