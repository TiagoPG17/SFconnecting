<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sf_solicitudes_credito', function (Blueprint $table) {
            $table->json('referencias_comerciales')->nullable()->after('justificacion');
            $table->boolean('inventario_consignacion')->nullable()->after('referencias_comerciales');
        });
    }

    public function down(): void
    {
        Schema::table('sf_solicitudes_credito', function (Blueprint $table) {
            $table->dropColumn(['referencias_comerciales', 'inventario_consignacion']);
        });
    }
};
