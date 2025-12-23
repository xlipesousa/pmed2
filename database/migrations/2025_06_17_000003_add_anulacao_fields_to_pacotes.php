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
            // Campos de controle de anulação (apenas se não existirem)
            if (!Schema::hasColumn('pacotes', 'anulado')) {
                $table->boolean('anulado')->default(false)->comment('Pacote foi anulado?');
            }
            
            if (!Schema::hasColumn('pacotes', 'data_anulacao')) {
                $table->timestamp('data_anulacao')->nullable()->comment('Data da anulação');
            }
            
            if (!Schema::hasColumn('pacotes', 'usuario_anulacao_id')) {
                $table->unsignedBigInteger('usuario_anulacao_id')->nullable()->comment('Usuário que anulou');
            }
            
            if (!Schema::hasColumn('pacotes', 'motivo_anulacao')) {
                $table->text('motivo_anulacao')->nullable()->comment('Motivo da anulação');
            }
            
            // Índices para performance
            $table->index(['anulado', 'localizacao_atual'], 'idx_anulado_localizacao');
            $table->index('data_anulacao', 'idx_data_anulacao_pacotes');
            
            // Foreign Key se o campo foi criado
            if (!Schema::hasColumn('pacotes', 'usuario_anulacao_id')) {
                $table->foreign('usuario_anulacao_id')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pacotes', function (Blueprint $table) {
            // Remover índices
            $table->dropIndex('idx_anulado_localizacao');
            $table->dropIndex('idx_data_anulacao_pacotes');
            
            // Remover foreign key
            $table->dropForeign(['usuario_anulacao_id']);
            
            // Remover colunas (apenas se foram criadas por esta migration)
            $table->dropColumn(['anulado', 'data_anulacao', 'usuario_anulacao_id', 'motivo_anulacao']);
        });
    }
};