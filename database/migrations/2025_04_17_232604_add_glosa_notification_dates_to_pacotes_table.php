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
            $table->dateTime('data_notificacao_glosa')->nullable()->after('estado_glosa');
            $table->dateTime('data_limite_retirada')->nullable()->after('data_notificacao_glosa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacotes', function (Blueprint $table) {
            $table->dropColumn('data_notificacao_glosa');
            $table->dropColumn('data_limite_retirada');
        });
    }
};
