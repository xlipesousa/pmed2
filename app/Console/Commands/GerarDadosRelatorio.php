<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pacote;
use App\Models\OcsPsa;
use App\Models\TipoPacote;
use App\Models\TipoConta;
use App\Models\MotivoGlosa;

class GerarDadosRelatorio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'relatorios:gerar-dados {quantidade=100 : Quantidade de pacotes a serem gerados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera dados fictícios para testes de relatórios';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $quantidade = $this->argument('quantidade');
        
        // Verificar se há dados de referência
        $this->verificarReferencias();
        
        $this->info("Iniciando geração de {$quantidade} pacotes para teste...");
        
        $bar = $this->output->createProgressBar($quantidade);
        $bar->start();
        
        // Gerar pacotes normais (60%)
        $quantidadeNormal = (int) ($quantidade * 0.6);
        Pacote::factory()->count($quantidadeNormal)->create();
        $bar->advance($quantidadeNormal);
        
        // Gerar pacotes atrasados (25%)
        $quantidadeAtrasados = (int) ($quantidade * 0.25);
        Pacote::factory()->atrasado()->count($quantidadeAtrasados)->create();
        $bar->advance($quantidadeAtrasados);
        
        // Gerar pacotes críticos (15%)
        $quantidadeCriticos = $quantidade - $quantidadeNormal - $quantidadeAtrasados;
        Pacote::factory()->critico()->count($quantidadeCriticos)->create();
        $bar->advance($quantidadeCriticos);
        
        $bar->finish();
        
        $this->newLine();
        $this->info("Dados gerados com sucesso!");
        return 0;
    }
    
    /**
     * Verifica e cria dados de referência se necessário
     */
    private function verificarReferencias()
    {
        // Verificar OcsPsa
        if (OcsPsa::count() == 0) {
            $this->info("Criando dados de referência para OCS/PSA...");
            $ocspsa = [
                ['nome' => 'Hospital Santa Maria', 'cnpj' => '12345678901234', 'status' => 'ativo'],
                ['nome' => 'Clínica São Lucas', 'cnpj' => '98765432109876', 'status' => 'ativo'],
                ['nome' => 'Laboratório Central', 'cnpj' => '45678901234567', 'status' => 'ativo'],
                ['nome' => 'Centro Médico Saúde', 'cnpj' => '78901234567890', 'status' => 'ativo'],
                ['nome' => 'Hospital Esperança', 'cnpj' => '56789012345678', 'status' => 'ativo'],
            ];
            
            foreach ($ocspsa as $dados) {
                OcsPsa::create($dados);
            }
        }
        
        // Verificar TipoPacote
        if (TipoPacote::count() == 0) {
            $this->info("Criando dados de referência para Tipos de Pacote...");
            $tipos = [
                ['nome' => 'Ambulatorial', 'descricao' => 'Pacotes ambulatoriais'],
                ['nome' => 'Internação', 'descricao' => 'Pacotes de internação'],
                ['nome' => 'Odontológico', 'descricao' => 'Pacotes odontológicos'],
                ['nome' => 'Exames', 'descricao' => 'Pacotes de exames'],
            ];
            
            foreach ($tipos as $tipo) {
                TipoPacote::create($tipo);
            }
        }
        
        // Verificar TipoConta
        if (TipoConta::count() == 0) {
            $this->info("Criando dados de referência para Tipos de Conta...");
            $tiposConta = [
                ['nome' => 'Particular', 'descricao' => 'Contas particulares'],
                ['nome' => 'Convênio', 'descricao' => 'Contas de convênio'],
                ['nome' => 'SUS', 'descricao' => 'Contas do SUS'],
            ];
            
            foreach ($tiposConta as $tipo) {
                TipoConta::create($tipo);
            }
        }
        
        // Verificar MotivoGlosa
        if (MotivoGlosa::count() == 0) {
            $this->info("Criando dados de referência para Motivos de Glosa...");
            $motivos = [
                ['nome' => 'Falta de Documentação', 'descricao' => 'Documentação incompleta ou ausente'],
                ['nome' => 'Procedimento Não Autorizado', 'descricao' => 'Procedimento realizado sem autorização'],
                ['nome' => 'Valor Excedente', 'descricao' => 'Valor cobrado acima do contratado'],
                ['nome' => 'Duplicidade', 'descricao' => 'Cobrança em duplicidade'],
                ['nome' => 'Erro de Informação', 'descricao' => 'Informações incorretas'],
            ];
            
            foreach ($motivos as $motivo) {
                MotivoGlosa::create($motivo);
            }
        }
    }
}