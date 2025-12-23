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
        Schema::table('pacotes', function (Blueprint $table) {
            $table->decimal('valor_recurso_glosa', 10, 2)->default(0.00)->after('data_recebimento_recurso');
            $table->decimal('valor_recursado', 10, 2)->default(0.00)->after('valor_recurso_glosa');
            $table->decimal('valor_deferido', 10, 2)->default(0.00)->after('valor_recursado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacotes', function (Blueprint $table) {
            $table->dropColumn([
                'valor_recurso_glosa',
                'valor_recursado',
                'valor_deferido'
            ]);
        });
    }
};
