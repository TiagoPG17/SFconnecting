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
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->enum('proxima_tipo', ['llamada', 'reunion', 'email', 'visita', 'whatsapp', 'otro'])
                  ->nullable()
                  ->after('proxima_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->dropColumn('proxima_tipo');
        });
    }
};
