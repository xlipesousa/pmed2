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
            // Campo principal para controle de anulação
            $table->boolean('anulado')->default(false)->after('localizacao_fisica');
            
            // Campos de auditoria da anulação
            $table->timestamp('data_anulacao')->nullable()->after('anulado');
            $table->text('motivo_anulacao')->nullable()->after('data_anulacao');
            $table->unsignedBigInteger('usuario_anulacao_id')->nullable()->after('motivo_anulacao');
            
            // Chave estrangeira para usuário que anulou
            $table->foreign('usuario_anulacao_id')->references('id')->on('users')->onDelete('set null');
            
            // Índice para performance nas consultas de pacotes válidos
            $table->index(['anulado']);
            $table->index(['anulado', 'localizacao_atual']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacotes', function (Blueprint $table) {
            // Remover chave estrangeira primeiro
            $table->dropForeign(['usuario_anulacao_id']);
            
            // Remover índices
            $table->dropIndex(['anulado']);
            $table->dropIndex(['anulado', 'localizacao_atual']);
            
            // Remover colunas
            $table->dropColumn([
                'anulado',
                'data_anulacao', 
                'motivo_anulacao',
                'usuario_anulacao_id'
            ]);
        });
    }
};