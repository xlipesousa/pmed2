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
        // Verificar se a localização "anulado" já existe
        $existeAnulado = DB::table('pacotes')
            ->where('localizacao_atual', 'anulado')
            ->exists();

        if (!$existeAnulado) {
            // Não precisa alterar estrutura da tabela, apenas documenta nova localização
            // A coluna localizacao_atual já aceita qualquer string
            
            // Opcional: Inserir documentação da nova localização em uma tabela de configurações
            $payload = [
                'chave' => 'localizacoes_validas',
                'valor' => json_encode([
                    'protocolo',
                    'lisura',
                    'sire',
                    'glosa',
                    'arquivo',
                    'anulado' // NOVA LOCALIZAÇÃO
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ];

            if (Schema::hasColumn('configuracoes', 'descricao')) {
                $payload['descricao'] = 'Localizações válidas para pacotes';
            }

            DB::table('configuracoes')->insertOrIgnore($payload);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter apenas pacotes anulados para protocolo (se necessário)
        DB::table('pacotes')
            ->where('localizacao_atual', 'anulado')
            ->update([
                'localizacao_atual' => 'protocolo',
                'updated_at' => now()
            ]);
            
        // Remover configuração
        DB::table('configuracoes')
            ->where('chave', 'localizacoes_validas')
            ->delete();
    }
};