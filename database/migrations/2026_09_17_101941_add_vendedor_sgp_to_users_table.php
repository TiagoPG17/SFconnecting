<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Usuario de SGP (ej. "ADRIANA.MEDELLIN") asignado a este comercial —
            // filtra los modales de Solicitudes de Cotización (SGP) para que cada
            // comercial vea solo lo suyo. Sin código asignado no ve nada (falla seguro).
            $table->string('vendedor_sgp', 100)->nullable()->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vendedor_sgp');
        });
    }
};
