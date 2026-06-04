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
            $table->foreignId('prospecto_id')
                ->nullable()
                ->after('cliente_id')
                ->constrained('sf_prospectos')
                ->nullOnDelete();

            $table->unsignedBigInteger('cliente_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('seguimientos', function (Blueprint $table) {
            $table->dropForeign(['prospecto_id']);
            $table->dropColumn('prospecto_id');
            $table->unsignedBigInteger('cliente_id')->nullable(false)->change();
        });
    }
};
