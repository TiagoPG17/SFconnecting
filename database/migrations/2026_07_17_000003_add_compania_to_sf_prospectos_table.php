<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sf_prospectos', function (Blueprint $table) {
            $table->unsignedTinyInteger('compania')->nullable()->after('asesor_id');
            $table->index(['compania']);
        });
    }

    public function down(): void
    {
        Schema::table('sf_prospectos', function (Blueprint $table) {
            $table->dropIndex(['compania']);
            $table->dropColumn('compania');
        });
    }
};
