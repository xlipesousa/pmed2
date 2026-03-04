<?php

namespace Tests\Feature;

use App\Models\MovimentacaoPacote;
use App\Models\OcsPsa;
use App\Models\Pacote;
use App\Models\TipoConta;
use App\Models\TipoPacote;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GraficosDesempenhoTest extends TestCase
{
    private static bool $baseMigrada = false;

    protected function setUp(): void
    {
        parent::setUp();

        $databaseDefault = config('database.default');
        if ($databaseDefault === 'sqlite' && !in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('Driver sqlite indisponível neste ambiente.');
        }

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Conexão de banco indisponível para testes de endpoint de desempenho.');
        }

        if (!self::$baseMigrada) {
            $this->artisan('migrate:fresh')->assertExitCode(0);
            self::$baseMigrada = true;
        }
    }

    public function test_endpoint_desempenho_retorna_estrutura_basica(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($user)
            ->get(route('graficos.desempenho'))
            ->assertOk()
            ->assertJsonStructure([
                'kpis' => ['media_score', 'melhor_colaborador', 'total_colaboradores', 'total_movimentacoes', 'retrabalho_medio', 'pesos'],
                'ranking' => ['labels', 'values'],
                'eixos' => ['labels', 'volume', 'tempo', 'qualidade', 'retrabalho'],
                'retrabalho' => ['labels', 'values'],
                'historico_mensal' => ['labels', 'valores'],
            ]);
    }

    public function test_endpoint_desempenho_com_dados_retorna_colaboradores(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $operador = User::factory()->create(['role' => 'protocolo']);
        $ocs = OcsPsa::create(['nome' => 'OCS Teste', 'codigo_interno' => 'OCS-T1', 'ativo' => true]);
        $tipoPacote = TipoPacote::create(['nome' => 'Consulta']);
        $tipoConta = TipoConta::create(['nome' => 'Ambulatorial']);

        $pacote = Pacote::create([
            'ocs_psa_id' => $ocs->id,
            'tipo_id' => $tipoPacote->id,
            'tipo_conta_id' => $tipoConta->id,
            'numero_fatura' => 'FAT-100',
            'data_entrada' => now()->subDays(5)->format('Y-m-d'),
            'valor_fatura' => 1000,
            'valor_glosa' => 0,
            'estado_geral' => 'Normal',
            'estado_glosa' => 'pendente',
            'localizacao_atual' => 'Protocolo',
            'localizacao_anterior' => 'Protocolo',
            'ultima_acao' => 'Teste',
        ]);

        MovimentacaoPacote::create([
            'pacote_id' => $pacote->id,
            'acao' => 'Entrada',
            'mensagem' => 'Movimentação inicial',
            'observacao' => 'OK',
            'localizacao_pos_acao' => 'Protocolo',
            'estado_geral' => 'Normal',
            'estado_glosa' => 'pendente',
            'usuario_id' => $operador->id,
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        MovimentacaoPacote::create([
            'pacote_id' => $pacote->id,
            'acao' => 'Ajuste',
            'mensagem' => 'Retornado para correção',
            'observacao' => 'retrabalho',
            'localizacao_pos_acao' => 'Lisura',
            'estado_geral' => 'Normal',
            'estado_glosa' => 'pendente',
            'usuario_id' => $operador->id,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('graficos.desempenho'));

        $response->assertOk();
        $response->assertJsonPath('kpis.total_colaboradores', 1);
        $response->assertJsonPath('ranking.labels.0', $operador->name);
    }
}
