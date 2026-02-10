<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Models\TipoPacote;
use App\Models\TipoConta;
use App\Models\MotivoGlosa;
use App\Models\Pacote;
use App\Models\MovimentacaoPacote;
use App\Models\User;
use App\Models\PacoteAnuladoAudit; //  ADICIONAR APENAS ESTA LINHA
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\ExecutarUpgrade;

class ConfiguracoesController extends Controller
{
    /**
     * Middleware de autorização
     */
    public function __construct()
    {
        $this->middleware('can:anular-pacotes')->only([
            'anulacao', 
            'buscarPacote', 
            'anularPacote', 
            'listarPacotesAnulados'
        ]);
    }

    /**
     * Exibe a view de configurações do sistema com os tipos de pacote, tipos de conta e motivos de glosa
     */
    public function sistema()
    {
        $tiposPacote = TipoPacote::all();
        $tiposConta = TipoConta::all();
        $motivosGlosa = MotivoGlosa::all();
        return view('configuracoes.sistema', compact('tiposPacote', 'tiposConta', 'motivosGlosa'));
    }

    public function ocspsa()
    {
        return view('configuracoes.ocspsa');
    }

    public function upgrade()
    {
        [$commitHash, $commitDate] = $this->getCommitInfo();
        $upgradeStatus = $this->getUpgradeStatus();

        return view('configuracoes.upgrade', compact('commitHash', 'commitDate', 'upgradeStatus'));
    }

    public function upgradeVerificar()
    {
        [$commitHash, $commitDate] = $this->getCommitInfo();
        $status = 'indisponivel';
        $ahead = null;
        $behind = null;
        $message = null;

        if (is_dir(base_path('.git'))) {
            $repoPath = escapeshellarg(base_path());
            shell_exec('git -C ' . $repoPath . ' fetch origin 2>/dev/null');
            $counts = trim((string) shell_exec('git -C ' . $repoPath . ' rev-list --left-right --count HEAD...origin/main 2>/dev/null'));

            if ($counts !== '') {
                [$ahead, $behind] = array_map('intval', preg_split('/\s+/', $counts));
                $status = ($behind > 0) ? 'atualizacao_disponivel' : 'atualizado';
            } else {
                $message = 'Nao foi possivel ler o status do repositorio.';
            }
        } else {
            $message = 'Repositorio Git nao encontrado em ' . base_path() . '.';
        }

        return redirect()
            ->route('configuracoes.upgrade')
            ->with('upgrade_status', $status)
            ->with('upgrade_ahead', $ahead)
            ->with('upgrade_behind', $behind)
            ->with('upgrade_commit', $commitHash)
            ->with('upgrade_commit_date', $commitDate)
            ->with('upgrade_message', $message);
    }

    public function upgradeExecutar()
    {
        $this->setUpgradeStatus(['status' => 'em_execucao']);
        dispatch(new ExecutarUpgrade());

        return redirect()
            ->route('configuracoes.upgrade')
            ->with('upgrade_execucao', 'em_execucao');
    }

    public function upgradeWorker()
    {
        $queueConnection = config('queue.default');
        $workerAtivo = false;

        if ($queueConnection === 'sync') {
            $workerAtivo = false;
        } else {
            $workerAtivo = (bool) trim((string) shell_exec("ps aux | grep -E 'queue:work|queue:listen' | grep -v grep"));
        }

        return redirect()
            ->route('configuracoes.upgrade')
            ->with('upgrade_worker', $workerAtivo ? 'ativo' : 'inativo')
            ->with('upgrade_queue', $queueConnection);
    }

    private function getCommitInfo(): array
    {
        $commitHash = 'N/A';
        $commitDate = 'N/A';

        if (is_dir(base_path('.git'))) {
            $repoPath = escapeshellarg(base_path());
            $commitHash = trim((string) shell_exec('git -C ' . $repoPath . ' rev-parse --short HEAD 2>/dev/null'));
            $commitDate = trim((string) shell_exec('git -C ' . $repoPath . ' log -1 --format=%cd --date=iso 2>/dev/null'));

            if ($commitHash === '') {
                $commitHash = 'N/A';
            }
            if ($commitDate === '') {
                $commitDate = 'N/A';
            }
        }

        return [$commitHash, $commitDate];
    }

    private function getUpgradeStatus(): array
    {
        $statusPath = storage_path('logs/upgrade-status.json');
        if (!file_exists($statusPath)) {
            return ['status' => 'desconhecido'];
        }

        $raw = file_get_contents($statusPath);
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return ['status' => 'desconhecido'];
        }

        return $data;
    }

    private function setUpgradeStatus(array $data): void
    {
        $statusPath = storage_path('logs/upgrade-status.json');
        $payload = array_merge(['status' => 'desconhecido', 'updated_at' => now()->toDateTimeString()], $data);
        file_put_contents($statusPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function salvarSistema(Request $request)
    {
        // Aqui seria a lógica para salvar as configurações
        return redirect()->route('configuracoes.sistema')->with('success', 'Configurações do sistema atualizadas com sucesso!');
    }

    /**
     * Salva configurações do sistema
     */
    public function sistemasSalvar(Request $request)
    {
        // Validação
        $request->validate([
            'novo_logo' => 'nullable|image|mimes:jpeg,png,svg|max:2048', // Máximo 2MB
            'novo_favicon' => 'nullable|file|mimes:ico,png,jpeg,svg|max:1024', // Máximo 1MB
        ]);
        
        // Processar o logo caso tenha sido enviado
        if ($request->hasFile('novo_logo')) {
            $logo = $request->file('novo_logo');
            $logoPath = public_path('vendor/adminlte/dist/img/AdminLTELogo.png');
            
            // Criar uma cópia de backup do logo atual
            if (file_exists($logoPath)) {
                copy($logoPath, public_path('vendor/adminlte/dist/img/AdminLTELogo_backup.png'));
            }
            
            // Redimensionar e salvar o novo logo
            $img = Image::make($logo->getRealPath());
            $img->fit(130, 130); // Ajustar para 130x130px
            $img->save($logoPath);
        }
        
        // Processar o favicon caso tenha sido enviado
        if ($request->hasFile('novo_favicon')) {
            $favicon = $request->file('novo_favicon');
            $faviconPath = public_path('favicon.ico');
            
            // Criar uma cópia de backup do favicon atual
            if (file_exists($faviconPath)) {
                copy($faviconPath, public_path('favicon_backup.ico'));
            }
            
            // Redimensionar e salvar o novo favicon
            $img = Image::make($favicon->getRealPath());
            $img->fit(32, 32); // Ajustar para 32x32px
            
            // Se o arquivo original não é um .ico, precisamos convertê-lo
            if ($favicon->getClientOriginalExtension() !== 'ico') {
                $img->save($faviconPath);
            } else {
                // Se já é um .ico, apenas mova-o
                $favicon->move(public_path(), 'favicon.ico');
            }
        }
        
        // Salvar outras configurações...
        // ...
        
        return redirect()->route('configuracoes.sistema')->with('success', 'Configurações salvas com sucesso!');
    }

    /**
     * Adiciona um novo tipo de pacote
     */
    public function adicionarTipoPacote(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
        ]);
        
        TipoPacote::create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Tipo de pacote adicionado com sucesso!');
    }

    /**
     * Atualiza um tipo de pacote existente
     */
    public function editarTipoPacote(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
        ]);
        
        $tipoPacote = TipoPacote::findOrFail($id);
        $tipoPacote->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Tipo de pacote atualizado com sucesso!');
    }

    /**
     * Exclui um tipo de pacote
     */
    public function excluirTipoPacote($id)
    {
        $tipoPacote = TipoPacote::findOrFail($id);
        $tipoPacote->delete();
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Tipo de pacote excluído com sucesso!');
    }

    /**
     * Adiciona um novo tipo de conta
     */
    public function adicionarTipoConta(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
        ]);
        
        TipoConta::create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Tipo de conta adicionado com sucesso!');
    }

    /**
     * Atualiza um tipo de conta existente
     */
    public function editarTipoConta(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
        ]);
        
        $tipoConta = TipoConta::findOrFail($id);
        $tipoConta->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Tipo de conta atualizado com sucesso!');
    }

    /**
     * Exclui um tipo de conta
     */
    public function excluirTipoConta($id)
    {
        $tipoConta = TipoConta::findOrFail($id);
        $tipoConta->delete();
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Tipo de conta excluído com sucesso!');
    }

    /**
     * Adiciona um novo motivo de glosa
     */
    public function adicionarMotivoGlosa(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
        ]);
        
        MotivoGlosa::create([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Motivo de glosa adicionado com sucesso!');
    }

    /**
     * Atualiza um motivo de glosa existente
     */
    public function editarMotivoGlosa(Request $request, $id)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:255',
        ]);
        
        $motivoGlosa = MotivoGlosa::findOrFail($id);
        $motivoGlosa->update([
            'nome' => $request->nome,
            'descricao' => $request->descricao,
        ]);
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Motivo de glosa atualizado com sucesso!');
    }

    /**
     * Exclui um motivo de glosa
     */
    public function excluirMotivoGlosa($id)
    {
        $motivoGlosa = MotivoGlosa::findOrFail($id);
        $motivoGlosa->delete();
        
        return redirect()->route('configuracoes.sistema')
            ->with('success', 'Motivo de glosa excluído com sucesso!');
    }

    public function criarOcsPsa(Request $request)
    {
        // Aqui seria a lógica para criar uma OCS/PSA
        return redirect()->route('configuracoes.ocspsa')->with('success', 'OCS/PSA criada com sucesso!');
    }

    public function editarOcsPsa(Request $request, $id)
    {
        // Aqui seria a lógica para editar uma OCS/PSA
        return redirect()->route('configuracoes.ocspsa')->with('success', 'OCS/PSA atualizada com sucesso!');
    }

    public function excluirOcsPsa($id)
    {
        // Aqui seria a lógica para excluir uma OCS/PSA
        return redirect()->route('configuracoes.ocspsa')->with('success', 'OCS/PSA excluída com sucesso!');
    }

    /**
     * Exibe a página de anulação de pacotes
     */
    public function anulacao()
    {
        return view('configuracoes.anulacao');
    }

    /**
     * CORRIGIR: Busca pacote com validação correta
     */
    public function buscarPacote($id)
    {
        try {
            $pacote = Pacote::with(['ocsPsa', 'tipoPacote', 'tipoConta'])
                ->where('id', $id)
                ->first();

            if (!$pacote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pacote não encontrado.'
                ], 404);
            }

            // CORREÇÃO 2: Apenas "arquivado" não pode ser anulado
            if ($pacote->localizacao_atual === 'arquivado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pacotes arquivados não podem ser anulados.'
                ], 400);
            }

            // Formatar dados para exibição
            $dados = [
                'id' => $pacote->id,
                'numero_fatura' => $pacote->numero_fatura,
                'ocs_psa' => $pacote->ocsPsa ? $pacote->ocsPsa->nome : 'N/A',
                'valor_fatura' => number_format($pacote->valor_fatura, 2, ',', '.'),
                'data_entrada' => $pacote->data_entrada ? $pacote->data_entrada->format('d/m/Y') : 'N/A',
                'localizacao_atual' => ucfirst($pacote->localizacao_atual),
                'estado_geral' => $pacote->estado_geral,
                'estado_glosa' => $pacote->estado_glosa,
                'tipo_pacote' => $pacote->tipoPacote ? $pacote->tipoPacote->nome : 'N/A',
                'tipo_conta' => $pacote->tipoConta ? $pacote->tipoConta->nome : 'N/A',
                'valor_pago' => number_format($pacote->valor_pago ?? 0, 2, ',', '.'),
                'valor_pendente' => number_format($pacote->valor_pendente ?? 0, 2, ',', '.'),
                'valor_glosa' => number_format($pacote->valor_glosa ?? 0, 2, ',', '.')
            ];

            return response()->json([
                'success' => true,
                'pacote' => $dados
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar pacote para anulação: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * CORRIGIR: Anular pacote com validação correta
     */
    public function anularPacote(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_pacote' => 'required|integer|exists:pacotes,id',
                'motivo_anulacao' => 'required|string|min:10|max:500'
            ]);

            $pacote = Pacote::with(['ocsPsa', 'tipoPacote', 'tipoConta'])
                        ->findOrFail($validated['id_pacote']);

            // VERIFICAR: Pacote já anulado?
            if ($pacote->anulado || $pacote->localizacao_atual === 'anulado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pacote já foi anulado anteriormente.'
                ], 400);
            }

            // CORREÇÃO CRÍTICA: Apenas "arquivado" não pode ser anulado
            if ($pacote->localizacao_atual === 'arquivado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pacotes arquivados não podem ser anulados.'
                ], 400);
            }

            // ⭐ NOVA FUNCIONALIDADE: Criar auditoria e zerar valores
            DB::transaction(function () use ($pacote, $validated) {
                // 1. Criar registro de auditoria com valores originais
                PacoteAnuladoAudit::create([
                    'pacote_id' => $pacote->id,
                    'valor_fatura_original' => $pacote->valor_fatura,
                    'valor_pago_original' => $pacote->valor_pago ?? 0,
                    'valor_pendente_original' => $pacote->valor_pendente ?? 0,
                    'valor_glosa_original' => $pacote->valor_glosa ?? 0,
                    'valor_pos_lisura_original' => $pacote->valor_pos_lisura ?? 0,
                    'valor_recursado_original' => $pacote->valor_recursado ?? 0,
                    'valor_deferido_original' => $pacote->valor_deferido ?? 0,
                    'numero_fatura' => $pacote->numero_fatura,
                    'ocs_psa_nome' => $pacote->ocsPsa->nome ?? 'N/A',
                    'tipo_pacote_nome' => $pacote->tipoPacote->nome ?? 'N/A',
                    'tipo_conta_nome' => $pacote->tipoConta->nome ?? 'N/A',
                    'data_entrada_original' => $pacote->data_entrada,
                    'localizacao_no_momento' => $pacote->localizacao_atual,
                    'estado_geral_no_momento' => $pacote->estado_geral,
                    'estado_glosa_no_momento' => $pacote->estado_glosa,
                    'motivo_anulacao' => $validated['motivo_anulacao'],
                    'data_anulacao' => now(),
                    'usuario_anulacao_id' => Auth::id(),
                    'pode_reverter' => true
                ]);

                // 2. Zerar valores monetários no pacote original
                $pacote->update([
                    'valor_fatura' => 0.00,
                    'valor_pago' => 0.00,
                    'valor_pendente' => 0.00,
                    'valor_glosa' => 0.00,
                    'valor_pos_lisura' => 0.00,
                    'valor_recursado' => 0.00,
                    'valor_deferido' => 0.00,
                    'anulado' => true,
                    'estado_geral' => 'Anulado',
                    'localizacao_atual' => 'anulado',
                    'motivo_anulacao' => $validated['motivo_anulacao'],
                    'data_anulacao' => now(),
                    'usuario_anulacao_id' => Auth::id()
                ]);
            });

            // Registrar movimentação
            $this->registrarMovimentacaoAnulacao($pacote, $validated['motivo_anulacao']);

            Log::info('Pacote anulado COM AUDITORIA', [
                'pacote_id' => $pacote->id,
                'usuario_id' => Auth::id(),
                'motivo' => $validated['motivo_anulacao'],
                'valores_preservados' => true,
                'valores_zerados' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pacote #{$pacote->id} foi anulado com sucesso. Valores originais preservados para auditoria."
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao anular pacote com auditoria: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * FASE 2.4: Lista pacotes anulados COM dados da auditoria - SEM REVERSÃO
     */
    public function listarPacotesAnulados()
    {
        try {
            // Buscar da tabela de auditoria
            $anulacoes = PacoteAnuladoAudit::with(['pacote.ocsPsa', 'usuarioAnulacao'])
                ->orderBy('data_anulacao', 'desc')
                ->get();

            $dados = $anulacoes->map(function ($auditoria) {
                return [
                    'id' => $auditoria->pacote_id,
                    'numero_fatura' => $auditoria->numero_fatura,
                    'ocs_psa' => $auditoria->ocs_psa_nome,
                    'valor_fatura' => $auditoria->valor_fatura_original, // Valor original
                    'data_anulacao' => $auditoria->data_anulacao->format('d/m/Y H:i'),
                    'usuario_anulacao' => $auditoria->usuarioAnulacao->name ?? 'Sistema',
                    'motivo_anulacao' => $auditoria->motivo_anulacao,
                    'localizacao_atual' => 'anulado',
                    'estado_geral' => 'Anulado'
                    // ❌ REMOVIDO: 'pode_reverter' => $auditoria->pode_reverter && !$auditoria->data_reversao
                ];
            });

            return response()->json($dados);

        } catch (\Exception $e) {
            Log::error('Erro ao listar pacotes anulados: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Registrar movimentação de anulação - CORRIGIDO COMPLETO
     */
    private function registrarMovimentacaoAnulacao($pacote, $motivo)
    {
        try {
            // Usar o sistema de movimentações existente
            if (class_exists('\App\Models\MovimentacaoPacote')) {
                \App\Models\MovimentacaoPacote::create([
                    'pacote_id' => $pacote->id,
                    'acao' => 'Anulação',
                    'descricao' => 'Pacote anulado: ' . $motivo,
                    'mensagem' => 'Pacote anulado com preservação de auditoria',
                    'observacoes' => $motivo,
                    'localizacao_origem' => $pacote->localizacao_atual, // ⭐ ADICIONAR
                    'localizacao_destino' => 'anulado',
                    'localizacao_pos_acao' => 'anulado', // ⭐ ADICIONAR ESTE CAMPO
                    'estado_geral' => 'Anulado',
                    'estado_glosa' => $pacote->estado_glosa,
                    'usuario_id' => Auth::id()
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao registrar movimentação de anulação: ' . $e->getMessage());
            // Não falhar a anulação por causa do registro de movimentação
        }
    }

    /**
     * CORRIGIR: Método de verificação atualizado
     */
    private function verificarSePacketePodeSerAnulado($pacote)
    {
        // Verificar se já está anulado
        if ($pacote->anulado || $pacote->localizacao_atual === 'anulado') {
            return [
                'pode' => false,
                'motivo' => 'Este pacote já está anulado.'
            ];
        }

        // CORREÇÃO: Apenas "arquivado" não pode ser anulado
        if ($pacote->localizacao_atual === 'arquivado') {
            return [
                'pode' => false,
                'motivo' => 'Pacotes arquivados não podem ser anulados.'
            ];
        }

        // Verificar outras regras de negócio
        if ($pacote->estado_geral === 'Pago') {
            return [
                'pode' => false,
                'motivo' => 'Pacotes já pagos não podem ser anulados.'
            ];
        }

        // Pode ser anulado
        return [
            'pode' => true,
            'motivo' => ''
        ];
    }

    /**
     * FASE 2.5: Exibir detalhes do pacote anulado com dados da auditoria
     */
    public function verPacoteAnulado($id)
    {
        try {
            $auditoria = PacoteAnuladoAudit::with(['pacote', 'usuarioAnulacao'])
                ->where('pacote_id', $id)
                ->firstOrFail();

            return view('pacotes.anulado', compact('auditoria'));

        } catch (\Exception $e) {
            Log::error('Erro ao exibir pacote anulado: ' . $e->getMessage());
            return redirect()->route('configuracoes.anulacao')
                ->with('error', 'Pacote anulado não encontrado.');
        }
    }
}