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
            $table->string('estado_geral', 50)->change();
            $table->string('estado_glosa', 50)->change();
            $table->string('localizacao_atual', 50)->change();
            $table->string('localizacao_anterior', 50)->change();
            $table->string('ultima_acao', 255)->change();
        });
        
        Schema::table('movimentacoes_pacote', function (Blueprint $table) {
            $table->string('estado_geral', 50)->change();
            $table->string('estado_glosa', 50)->change();
            $table->string('localizacao_pos_acao', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacotes', function (Blueprint $table) {
            $table->string('estado_geral', 20)->change();
            $table->string('estado_glosa', 20)->change();
            $table->string('localizacao_atual', 20)->change();
            $table->string('localizacao_anterior', 20)->change();
            $table->string('ultima_acao', 100)->change();
        });
        
        Schema::table('movimentacoes_pacote', function (Blueprint $table) {
            $table->string('estado_geral', 20)->change();
            $table->string('estado_glosa', 20)->change();
            $table->string('localizacao_pos_acao', 20)->change();
        });
    }
};
