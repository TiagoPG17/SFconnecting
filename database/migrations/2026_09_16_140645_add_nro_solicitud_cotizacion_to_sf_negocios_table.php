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
        Schema::table('sf_negocios', function (Blueprint $table) {
            // nro_solicitud de dbo.CRM_stg_Solicitudes_Cotizacion (Contiflex/SGP) cuando el
            // negocio se creó desde el modal "cargar solicitudes de cotización" — único para
            // que la misma solicitud no pueda convertirse dos veces en un negocio.
            $table->string('nro_solicitud_cotizacion', 20)->nullable()->unique()->after('compania');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sf_negocios', function (Blueprint $table) {
            $table->dropColumn('nro_solicitud_cotizacion');
        });
    }
};
