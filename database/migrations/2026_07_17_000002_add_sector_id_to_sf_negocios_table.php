<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sf_negocios', function (Blueprint $table) {
            $table->foreignId('sector_id')
                ->nullable()
                ->after('tipo_negocio_id')
                ->constrained('sf_maestros_comerciales')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sf_negocios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sector_id');
        });
    }
};
