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
            $table->text('observacion_perdida')->nullable()->after('motivo_perdida_id');
        });
    }

    public function down(): void
    {
        Schema::table('sf_negocios', function (Blueprint $table) {
            $table->dropColumn('observacion_perdida');
        });
    }
};
