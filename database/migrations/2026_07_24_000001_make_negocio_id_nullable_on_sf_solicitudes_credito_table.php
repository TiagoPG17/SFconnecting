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
            $table->dropForeign(['negocio_id']);
        });

        Schema::table('sf_solicitudes_credito', function (Blueprint $table) {
            $table->foreignId('negocio_id')->nullable()->change();
        });

        Schema::table('sf_solicitudes_credito', function (Blueprint $table) {
            $table->foreign('negocio_id')->references('id')->on('sf_negocios');
        });
    }

    public function down(): void
    {
        Schema::table('sf_solicitudes_credito', function (Blueprint $table) {
            $table->dropForeign(['negocio_id']);
        });

        Schema::table('sf_solicitudes_credito', function (Blueprint $table) {
            $table->foreignId('negocio_id')->nullable(false)->change();
        });

        Schema::table('sf_solicitudes_credito', function (Blueprint $table) {
            $table->foreign('negocio_id')->references('id')->on('sf_negocios');
        });
    }
};
