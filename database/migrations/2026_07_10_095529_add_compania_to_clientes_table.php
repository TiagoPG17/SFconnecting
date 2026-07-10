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
            $table->unsignedTinyInteger('compania')->default(1)->after('nit');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique('clientes_nit_unique');
            $table->unique(['nit', 'compania']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropUnique(['nit', 'compania']);
            $table->dropColumn('compania');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->unique('nit');
        });
    }
};
