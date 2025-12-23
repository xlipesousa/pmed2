<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar colunas se não existirem
        if (!Schema::hasColumn('pacotes', 'motivo_glosa_id')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->unsignedBigInteger('motivo_glosa_id')->nullable()->after('tipo_conta_id');
            });
        }
        
        if (!Schema::hasColumn('pacotes', 'descricao_glosa')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->text('descricao_glosa')->nullable()->after('motivo_glosa_id');
            });
        }
        
        // Adicionar chave estrangeira - usando try/catch para evitar erros
        try {
            Schema::table('pacotes', function (Blueprint $table) {
                // Tentar remover a chave estrangeira primeiro (se existir)
                try {
                    $table->dropForeign(['motivo_glosa_id']);
                } catch (\Exception $e) {
                    // Ignora erro se a chave não existir
                }
                
                // Adicionar a chave estrangeira
                $table->foreign('motivo_glosa_id')
                    ->references('id')
                    ->on('motivos_glosa')
                    ->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Se falhar, registrar o erro mas continuar a migração
            echo 'Aviso: Não foi possível adicionar a chave estrangeira - ' . $e->getMessage() . "\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remover chave estrangeira com tratamento de erro
        try {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->dropForeign(['motivo_glosa_id']);
            });
        } catch (\Exception $e) {
            // Ignora erro se a chave não existir
        }
        
        // Remover colunas
        if (Schema::hasColumn('pacotes', 'descricao_glosa')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->dropColumn('descricao_glosa');
            });
        }
        
        if (Schema::hasColumn('pacotes', 'motivo_glosa_id')) {
            Schema::table('pacotes', function (Blueprint $table) {
                $table->dropColumn('motivo_glosa_id');
            });
        }
    }
};
