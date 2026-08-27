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
        Schema::table('clientes', function (Blueprint $table) {
            $table->json('datos_carga')->nullable()->after('notas');
            $table->timestamp('revisado_contabilidad_en')->nullable()->after('datos_carga');
            $table->foreignId('revisado_contabilidad_por')->nullable()->after('revisado_contabilidad_en')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revisado_contabilidad_por');
            $table->dropColumn(['datos_carga', 'revisado_contabilidad_en']);
        });
    }
};
