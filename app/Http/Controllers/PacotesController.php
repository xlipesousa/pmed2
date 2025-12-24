<?php

namespace App\Http\Controllers;

use App\Models\Pacote;
use App\Models\OcsPsa;
use App\Models\TipoPacote;
use App\Models\TipoConta;
use App\Models\MovimentacaoPacote;
use App\Models\MotivoGlosa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PacotesController extends Controller
{
    /**
     * Lista os pacotes por localização atual
     */
    public function index(Request $request)
    {
        // Determinar qual aba deve estar ativa inicialmente
        $localizacaoAtiva = $request->query('localizacao', 'protocolo');
        $localizacaoAtiva = strtolower($localizacaoAtiva);
        
        // Validar se a localização é válida
        $localizacoesValidas = ['protocolo', 'lisura', 'sire', 'glosa', 'arquivo', 'arquivado'];
        if (!in_array($localizacaoAtiva, $localizacoesValidas)) {
            $localizacaoAtiva = 'protocolo';
        }
        
        // Buscar todos os pacotes com relacionamentos, sem filtrar por localização
        $pacotes = Pacote::with(['ocsPsa', 'tipoPacote', 'tipoConta'])
                        ->orderBy('id', 'desc')
                        ->get();
        
        return view('pacotes.index', compact('pacotes', 'localizacaoAtiva'));
    }

    /**
     * Exibe o formulário para criar um novo pacote
     */
    public function create()
    {
        // Verificar se o usuário tem permissão para criar
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('protocolo')) {
            return redirect()->route('pacotes.index')
                ->with('error', 'Você não tem permissão para criar pacotes.');
        }

        // Buscar dados para os dropdowns
        $ocsPsaList = OcsPsa::where('ativo', true)->orderBy('nome')->get();
        $tiposPacote = TipoPacote::orderBy('nome')->get();
        $tiposConta = TipoConta::orderBy('nome')->get();

        return view('pacotes.criar', compact('ocsPsaList', 'tiposPacote', 'tiposConta'));
    }

    /**
     * Armazena um novo pacote no banco de dados
     */
    public function store(Request $request)
    {
        // Verificar se o usuário tem permissão para criar
        if (!Auth::user()->isAdmin() && !Auth::user()->hasRole('protocolo')) {
            return redirect()->route('pacotes.index')
                ->with('error', 'Você não tem permissão para criar pacotes.');
        }

        // Validar a requisição
        $validated = $request->validate([
            'ocs_psa_id' => 'required|exists:ocs_psa,id',
            'tipo_id' => 'required|exists:tipos_pacote,id',
            'numero_fatura' => 'required|string|max:50',
            'data_entrada' => 'required|date_format:d/m/Y|before_or_equal:today',
            'valor_fatura' => 'required|string',
        ]);

        // Converter valor_fatura de formatação brasileira para decimal
        $valorFatura = str_replace('.', '', $request->valor_fatura);  // Remove pontos
        $valorFatura = str_replace(',', '.', $valorFatura);  // Substitui vírgula por ponto

        // Converter data de entrada do formato brasileiro para o formato do banco
        $dataEntrada = \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_entrada)->format('Y-m-d');

        try {
            // Criar um novo pacote
            $pacote = new \App\Models\Pacote();
            $pacote->ocs_psa_id = $request->ocs_psa_id;
            $pacote->tipo_id = $request->tipo_id;
            $pacote->numero_fatura = $request->numero_fatura;
            $pacote->data_entrada = $dataEntrada;
            $pacote->valor_fatura = $valorFatura;
            $pacote->valor_glosa = 0;
            $pacote->valor_pos_lisura = $valorFatura;
            $pacote->valor_pago = 0;
            $pacote->valor_pendente = $valorFatura;
            
            // Usar valores entre aspas simples para garantir que sejam tratados como strings
            $pacote->estado_geral = "Normal";
            $pacote->estado_glosa = "Glosa não identificada"; // Agora podemos usar o texto completo
            $pacote->localizacao_atual = "protocolo";
            $pacote->localizacao_anterior = "sistema";
            $pacote->ultima_acao = "Pacote criado no sistema";
            $pacote->observacoes = $request->observacoes;
            $pacote->save();

            // Registrar movimentação no histórico
            $this->registrarMovimentacao(
                $pacote->id,
                'Criar novo Pacote',
                'Novo pacote criado no sistema',
                $request->observacoes,
                'protocolo',
                'Normal',
                'Não identificada'
            );

            return redirect()->route('pacotes.show', $pacote->id)
                ->with('success', 'Pacote criado com sucesso!');
                
        } catch (\Exception $e) {
            // Log do erro para diagnóstico
            Log::error('Erro ao salvar pacote: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao salvar o pacote: ' . $e->getMessage());
        }
    }

    /**
     * Exibe um pacote específico
     */
    public function show($id)
    {
        $pacote = Pacote::with(['ocsPsa', 'tipoPacote', 'tipoConta'])->findOrFail($id);
        return view('pacotes.ver', compact('pacote'));
    }

    /**
     * Exibe o formulário para editar um pacote existente
     */
    public function edit($id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar a localização atual do pacote
        $localizacao = $pacote->localizacao_atual;
        
        // Verificar permissões específicas para cada localização
        if ($localizacao === 'protocolo' && !Auth::user()->hasRole('protocolo') && !Auth::user()->hasRole('admin')) {
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Você não tem permissão para editar pacotes no Protocolo.');
        }
        
        if ($localizacao === 'lisura' && !Auth::user()->hasRole('lisura') && !Auth::user()->hasRole('admin')) {
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Você não tem permissão para editar pacotes na Lisura.');
        }
        
        // Dados para os dropdowns
        $ocsPsaList = OcsPsa::where('ativo', true)->orderBy('nome')->get();
        $tiposPacote = TipoPacote::orderBy('nome')->get();
        $tiposConta = TipoConta::orderBy('nome')->get();
        
        // Carregar a view apropriada com base na localização
        if ($localizacao === 'protocolo') {
            return view('pacotes.editar_protocolo', compact('pacote', 'ocsPsaList', 'tiposPacote'));
        } else if ($localizacao === 'lisura') {
            // Carregar também os motivos de glosa
            $motivosGlosa = MotivoGlosa::orderBy('descricao')->get(); // Corrigido de MotivosGlosa para MotivoGlosa
            return view('pacotes.editar_lisura', compact('pacote', 'ocsPsaList', 'tiposPacote', 'tiposConta', 'motivosGlosa'));
        } else {
            // Para outras localizações, redirecionar para a visualização
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Não é possível editar pacotes nesta etapa do fluxo.');
        }
    }

    /**
     * Atualiza um pacote existente no banco de dados
     */
    public function update(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar a localização atual do pacote
        $localizacao = $pacote->localizacao_atual;
        
        // Verificar permissões específicas para cada localização
        if ($localizacao === 'protocolo' && !Auth::user()->hasRole('protocolo') && !Auth::user()->hasRole('admin')) {
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Você não tem permissão para atualizar pacotes no Protocolo.');
        }
        
        if ($localizacao === 'lisura' && !Auth::user()->hasRole('lisura') && !Auth::user()->hasRole('admin')) {
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Você não tem permissão para atualizar pacotes na Lisura.');
        }
        
        // Validação específica para cada localização
        if ($localizacao === 'protocolo') {
            $validated = $request->validate([
                'ocs_psa_id' => 'required|exists:ocs_psa,id',
                'tipo_id' => 'required|exists:tipos_pacote,id',
                'numero_fatura' => 'required|string|max:50',
                'data_entrada' => 'required|date_format:d/m/Y|before_or_equal:today',
                'valor_fatura' => 'required|string',
                'observacoes' => 'nullable|string',
            ]);
            
            // Converter valor_fatura de formatação brasileira para decimal
            $valorFatura = str_replace('.', '', $request->valor_fatura);
            $valorFatura = str_replace(',', '.', $valorFatura);
            
            // Converter data de entrada do formato brasileiro para o formato do banco
            $dataEntrada = \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_entrada)->format('Y-m-d');
            
            // Atualizar campos do pacote
            $pacote->ocs_psa_id = $request->ocs_psa_id;
            $pacote->tipo_id = $request->tipo_id;
            $pacote->numero_fatura = $request->numero_fatura;
            $pacote->data_entrada = $dataEntrada;
            $pacote->valor_fatura = $valorFatura;
            
            // Recalcular o valor pós lisura com base no valor da fatura e glosa
            $pacote->valor_pos_lisura = $valorFatura - $pacote->valor_glosa;

            // Atualizar o valor pendente
            $pacote->valor_pendente = $pacote->valor_pos_lisura - $pacote->valor_pago;

            // NOVA LÓGICA: Se o valor pendente ficar em zero, alterar o estado geral para Normal
            if ($pacote->valor_pendente == 0) {
                $pacote->estado_geral = 'Normal';
            }
            
            $pacote->observacoes = $request->observacoes;
            $pacote->save();
            
            // Registrar movimentação
            $this->registrarMovimentacao(
                $pacote->id,
                'Edição',
                'Pacote editado no Protocolo',
                $request->observacoes,
                'protocolo',
                $pacote->estado_geral,
                $pacote->estado_glosa
            );
            
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('success', 'Pacote atualizado com sucesso.');
        }
        
        if ($localizacao === 'lisura') {
            return $this->updateLisura($request, $id);
        }
        
        return redirect()->route('pacotes.show', $pacote->id)
            ->with('error', 'Não é possível atualizar pacotes nesta etapa do fluxo.');
    }

    /**
     * Atualização pelo setor Lisura
     */
    public function updateLisura(Request $request, $id)
    {
        Log::info("Iniciando atualização do pacote pela Lisura", ['id' => $id, 'request' => $request->all()]);

        try {
            // Primeiro converter os valores formatados para números
            if (is_string($request->valor_fatura)) {
                $valor_fatura = str_replace('.', '', $request->valor_fatura);
                $valor_fatura = str_replace(',', '.', $valor_fatura);
                $valor_fatura = (float)$valor_fatura;
                
                // Substituir o valor formatado pelo valor numérico no request
                $request->merge(['valor_fatura' => $valor_fatura]);
                
                Log::info("Valor fatura convertido:", ['original' => $request->input('valor_fatura_original', ''), 'convertido' => $valor_fatura]);
            }
            
            if (is_string($request->valor_glosa)) {
                $valor_glosa = str_replace('.', '', $request->valor_glosa);
                $valor_glosa = str_replace(',', '.', $valor_glosa);
                $valor_glosa = (float)$valor_glosa;
                
                // Substituir o valor formatado pelo valor numérico no request
                $request->merge(['valor_glosa' => $valor_glosa]);
                
                Log::info("Valor glosa convertido:", ['original' => $request->input('valor_glosa_original', ''), 'convertido' => $valor_glosa]);
            }
            
            // Depois validar com os valores já convertidos
            $validated = $request->validate([
                'ocs_psa_id' => 'required|exists:ocs_psa,id',
                'numero_fatura' => 'required|string|max:255',
                'valor_fatura' => 'required|numeric|min:0',
                'tipo_conta_id' => 'required|exists:tipos_conta,id',
                'valor_glosa' => 'nullable|numeric|min:0|max:' . $request->valor_fatura,
                'motivo_glosa' => 'nullable|exists:motivos_glosa,id',
                'descricao_glosa' => 'nullable|string',
                'observacoes' => 'nullable|string',
            ]);
            
            // Buscar o pacote
            $pacote = Pacote::findOrFail($id);
            
            // Guardar valores antigos para registrar no histórico
            $valoresAntigos = [
                'ocs_psa_id' => $pacote->ocs_psa_id,
                'numero_fatura' => $pacote->numero_fatura,
                'valor_fatura' => $pacote->valor_fatura,
                'tipo_conta_id' => $pacote->tipo_conta_id,
                'valor_glosa' => $pacote->valor_glosa,
                'motivo_glosa_id' => $pacote->motivo_glosa_id,
                'descricao_glosa' => $pacote->descricao_glosa,
                'estado_glosa' => $pacote->estado_glosa,
                'observacoes' => $pacote->observacoes
            ];
            
            // Atualizar valores
            $pacote->ocs_psa_id = $request->ocs_psa_id;
            $pacote->numero_fatura = $request->numero_fatura;
            $pacote->valor_fatura = $request->valor_fatura;
            $pacote->tipo_conta_id = $request->tipo_conta_id;
            $pacote->valor_glosa = $request->valor_glosa ?? 0;
            $pacote->motivo_glosa_id = $request->motivo_glosa;
            $pacote->descricao_glosa = $request->descricao_glosa;
            
            // Recalcular valores derivados
            $pacote->valor_pos_lisura = $pacote->valor_fatura - $pacote->valor_glosa;
            $pacote->valor_pendente = $pacote->valor_pos_lisura - $pacote->valor_pago;
            $pacote->observacoes = $request->observacoes;
            $pacote->ultima_acao = 'Pacote editado pela Lisura';
            
            // Atualizar o estado da glosa com base no valor da glosa
            if ($pacote->valor_glosa > 0) {
                $pacote->estado_glosa = 'Glosa Identificada';
            } else {
                $pacote->estado_glosa = 'Não identificada';
            }
            
            $pacote->save();
            
            // Registrar as alterações no histórico
            $mensagem = $this->gerarMensagemAlteracoes($valoresAntigos, $request->all());
            $this->registrarMovimentacao(
                $pacote->id, 
                'Edição', 
                $mensagem, 
                $request->observacoes,
                $pacote->localizacao_atual,
                $pacote->estado_geral,
                $pacote->estado_glosa
            );
            
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('success', 'Pacote atualizado com sucesso pela Lisura!');
                
        } catch (\Exception $e) {
            Log::error("Erro durante a atualização do pacote", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar o pacote: ' . $e->getMessage());
        }
    }

    /**
     * Atualização pelo setor Protocolo
     */
    private function updateProtocolo(Request $request, Pacote $pacote)
    {
        $request->validate([
            'ocs_psa_id' => 'required|exists:ocs_psa,id',
            'tipo_id' => 'required|exists:tipos_pacote,id',
            'tipo_conta_id' => 'required|exists:tipos_conta,id',
            'numero_fatura' => 'required|string|max:50',
            'data_entrada' => 'required|date|before_or_equal:today',
            'valor_fatura' => 'required|numeric|min:0',
        ]);

        // Guardar valores antigos para registrar no histórico
        $valoresAntigos = [
            'ocs_psa_id' => $pacote->ocs_psa_id,
            'tipo_id' => $pacote->tipo_id,
            'tipo_conta_id' => $pacote->tipo_conta_id,
            'numero_fatura' => $pacote->numero_fatura,
            'data_entrada' => $pacote->data_entrada,
            'valor_fatura' => $pacote->valor_fatura,
            'observacoes' => $pacote->observacoes
        ];

        // Atualizar valores
        $pacote->ocs_psa_id = $request->ocs_psa_id;
        $pacote->tipo_id = $request->tipo_id;
        $pacote->tipo_conta_id = $request->tipo_conta_id;
        $pacote->numero_fatura = $request->numero_fatura;
        $pacote->data_entrada = $request->data_entrada;
        $pacote->valor_fatura = $request->valor_fatura;
        $pacote->valor_pos_lisura = $request->valor_fatura; // Recalcular o valor pós-lisura
        $pacote->valor_pendente = $request->valor_fatura; // Recalcular o valor pendente
        $pacote->observacoes = $request->observacoes;
        $pacote->ultima_acao = 'Pacote editado pelo Protocolo';
        $pacote->save();

        // Registrar as alterações no histórico
        $mensagem = $this->gerarMensagemAlteracoes($valoresAntigos, $request->all());
        $this->registrarMovimentacao(
            $pacote->id, 
            'Edição', 
            $mensagem, 
            $request->observacoes,
            $pacote->localizacao_atual,
            $pacote->estado_geral,
            $pacote->estado_glosa
        );

        return redirect()->route('pacotes.show', $pacote->id)
            ->with('success', 'Pacote atualizado com sucesso.');
    }

    /**
     * Atualização pelo setor SIRE
     */
    private function updateSire(Request $request, Pacote $pacote)
    {
        // Implementar conforme necessário para o SIRE
        return redirect()->route('pacotes.show', $pacote->id)
            ->with('success', 'Pacote atualizado com sucesso.');
    }

    /**
     * Atualização pelo setor Glosa
     */
    private function updateGlosa(Request $request, Pacote $pacote)
    {
        // Implementar conforme necessário para o Glosa
        return redirect()->route('pacotes.show', $pacote->id)
            ->with('success', 'Pacote atualizado com sucesso.');
    }

    /**
     * Atualização pelo setor Arquivo
     */
    private function updateArquivo(Request $request, Pacote $pacote)
    {
        $request->validate([
            'arquivo' => 'nullable|string|max:255',
        ]);

        // Guardar valor antigo para registrar no histórico
        $valorAntigoArquivo = $pacote->arquivo;

        // Atualizar valor
        $pacote->arquivo = $request->arquivo;
        $pacote->ultima_acao = 'Atualização de arquivo';
        $pacote->save();

        // Registrar a alteração no histórico
        $mensagem = "Arquivo: Anterior: {$valorAntigoArquivo} Novo: {$request->arquivo}";
        $this->registrarMovimentacao(
            $pacote->id, 
            'Edição', 
            $mensagem, 
            $request->observacoes,
            $pacote->localizacao_atual,
            $pacote->estado_geral,
            $pacote->estado_glosa
        );

        return redirect()->route('pacotes.show', $pacote->id)
            ->with('success', 'Pacote atualizado com sucesso.');
    }

    /**
     * Atualiza apenas a localização física de um pacote
     */
    public function atualizarLocalizacaoFisica(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar permissões (apenas admin ou equipe arquivo)
        if (!Auth::user()->isAdmin() && !Auth::user()->hasRole('arquivo')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para executar esta ação.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Você não tem permissão para executar esta ação.');
        }
        
        // Verificar se o pacote está em um estado válido para edição da localização física
        if ($pacote->localizacao_atual != 'arquivo' && $pacote->localizacao_atual != 'arquivado') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A localização física só pode ser editada quando o pacote estiver na localização "arquivo" ou "arquivado".'
                ], 422);
            }
            return redirect()->back()->with('error', 'A localização física só pode ser editada quando o pacote estiver na localização "arquivo" ou "arquivado".');
        }
        
        // Validar os dados
        $validated = $request->validate([
            'localizacao_fisica' => 'required|string|max:255',
            'observacao' => 'nullable|string|max:500',
        ]);
        
        // Atualizar a localização física do pacote
        $localizacaoAnterior = $pacote->localizacao_fisica;
        $pacote->localizacao_fisica = $request->localizacao_fisica;
        $pacote->ultima_acao = 'Localização física atualizada';
        $pacote->save();
        
        // Registrar a movimentação no histórico
        $this->registrarMovimentacao(
            $pacote->id,
            'Atualização de Localização Física',
            'Localização física atualizada de "' . ($localizacaoAnterior ?: 'Não informada') . '" para "' . $request->localizacao_fisica . '"',
            $request->observacao ?? '',
            $pacote->localizacao_atual,
            $pacote->estado_geral,
            $pacote->estado_glosa,
            Auth::id()
        );
        
        // Preparar mensagem de sucesso
        $mensagem = 'Localização física atualizada com sucesso.';
        
        // Retornar resposta adequada
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'redirect' => route('pacotes.show', $pacote->id)
            ]);
        }
        
        return redirect()->route('pacotes.show', $pacote->id)->with('success', $mensagem);
    }

    /**
     * Mover um pacote para a próxima etapa do fluxo
     */
    public function mover(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        $user = Auth::user();
        
        // Verificar permissões para mover
        $podeMover = false;
        
        if ($user->role === 'admin') {
            $podeMover = true;
        } else if ($pacote->localizacao_atual == 'protocolo' && $user->role === 'protocolo') {
            $podeMover = true;
        } else if ($pacote->localizacao_atual == 'lisura' && $user->role === 'lisura') {
            $podeMover = true;
        } else if ($pacote->localizacao_atual == 'sire' && $user->role === 'sire') {
            $podeMover = true;
        } else if ($pacote->localizacao_atual == 'glosa' && $user->role === 'glosa') {
            $podeMover = true;
        } else if ($pacote->localizacao_atual == 'arquivo' && $user->role === 'arquivo') {
            $podeMover = true;
        }
        
        if (!$podeMover) {
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Você não tem permissão para mover este pacote.');
        }
        
        // Lógica especial para pacotes na localização SIRE
        if ($pacote->localizacao_atual == 'sire') {
            // Case 1: Glosa = 0 e Pendente = 0 => Move para Arquivo
            if ($pacote->valor_glosa == 0 && $pacote->valor_pendente == 0) {
                $proximaLocalizacao = 'arquivo';
                $pacote->estado_geral = 'Normal';
            }
            // Case 2: Glosa = 0 e Pendente > 0 => Erro
            else if ($pacote->valor_glosa == 0 && $pacote->valor_pendente > 0) {
                return redirect()->route('pacotes.show', $pacote->id)
                    ->with('error', 'Não é possível mover, Valor Pendente R$ ' . number_format($pacote->valor_pendente, 2, ',', '.'));
            }
            // Case 3: Glosa > 0, Recurso = 0, Anterior = lisura, Pendente > 0 => Move para Glosa
            else if ($pacote->valor_glosa > 0 && 
                    ($pacote->valor_recurso_glosa == 0 || $pacote->valor_recurso_glosa === null) && 
                    $pacote->localizacao_anterior == 'lisura' && 
                    $pacote->valor_pendente > 0) {
                $proximaLocalizacao = 'glosa';
            }
            // Case 3.1: Glosa > 0, Recurso = 0, Anterior = lisura, Pendente = 0 => Move para Glosa
            else if ($pacote->valor_glosa > 0 && 
                    ($pacote->valor_recurso_glosa == 0 || $pacote->valor_recurso_glosa === null) && 
                    $pacote->localizacao_anterior == 'lisura' && 
                    $pacote->valor_pendente == 0) {
                $proximaLocalizacao = 'glosa';
            }
            // Case 4: Glosa > 0, Recurso > 0, Anterior = glosa, Pendente = 0 => Move para Arquivo
            else if ($pacote->valor_glosa > 0 && 
                    $pacote->valor_recurso_glosa > 0 && 
                    $pacote->localizacao_anterior == 'glosa' && 
                    $pacote->valor_pendente == 0) {
                $proximaLocalizacao = 'arquivo';
                $pacote->estado_geral = 'Normal';
            }
            // Case 5: Glosa > 0, Recurso > 0, Anterior = glosa, Pendente > 0 => Erro
            else if ($pacote->valor_glosa > 0 && 
                    $pacote->valor_recurso_glosa > 0 && 
                    $pacote->localizacao_anterior == 'glosa' && 
                    $pacote->valor_pendente > 0) {
                return redirect()->route('pacotes.show', $pacote->id)
                    ->with('error', 'Não é possível mover, Valor Pendente R$ ' . number_format($pacote->valor_pendente, 2, ',', '.'));
            }
            // Case 6: Glosa > 0, Recurso = 0, Anterior = glosa, Pendente = 0 => Move para Arquivo
            else if ($pacote->valor_glosa > 0 && 
                    ($pacote->valor_recurso_glosa == 0 || $pacote->valor_recurso_glosa === null) && 
                    $pacote->localizacao_anterior == 'glosa' && 
                    $pacote->valor_pendente == 0) {
                $proximaLocalizacao = 'arquivo';
                $pacote->estado_geral = 'Normal';
            }
            // Case 7: Glosa > 0, Recurso = 0, Anterior = glosa, Pendente > 0 => Erro
            else if ($pacote->valor_glosa > 0 && 
                    ($pacote->valor_recurso_glosa == 0 || $pacote->valor_recurso_glosa === null) && 
                    $pacote->localizacao_anterior == 'glosa' && 
                    $pacote->valor_pendente > 0) {
                return redirect()->route('pacotes.show', $pacote->id)
                    ->with('error', 'Não é possível mover, Valor Pendente R$ ' . number_format($pacote->valor_pendente, 2, ',', '.'));
            }
            // Caso não se encaixe em nenhuma regra, usa o destino padrão enviado na requisição
            else {
                $proximaLocalizacao = $request->input('destino');
            }
        } else {
            // Para outras localizações, use o comportamento padrão
            $proximaLocalizacao = $request->input('destino');
            if (empty($proximaLocalizacao)) {
                // Lógica para determinar a próxima localização com base na atual
                if ($pacote->localizacao_atual == 'protocolo') {
                    $proximaLocalizacao = 'lisura';
                } else if ($pacote->localizacao_atual == 'lisura') {
                    $proximaLocalizacao = 'sire';
                } else if ($pacote->localizacao_atual == 'sire') {
                    $proximaLocalizacao = 'glosa';
                } else if ($pacote->localizacao_atual == 'glosa') {
                    $proximaLocalizacao = 'arquivo';
                } else if ($pacote->localizacao_atual == 'arquivo') {
                    $proximaLocalizacao = 'arquivados';
                }
            }
        }
        
        if (empty($proximaLocalizacao)) {
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Não foi possível determinar o próximo destino para este pacote.');
        }
        
        // Atualizar pacote
        $localizacaoAnterior = $pacote->localizacao_atual;
        $pacote->localizacao_anterior = $localizacaoAnterior;
        $pacote->localizacao_atual = $proximaLocalizacao;
        $pacote->ultima_acao = "Movido de {$localizacaoAnterior} para {$proximaLocalizacao}";
        $pacote->save();
        
        // Registrar a movimentação no histórico
        $observacao = $request->input('observacao', '');
        $this->registrarMovimentacao(
            $pacote->id,
            'Movimentação',
            "Pacote movido de {$localizacaoAnterior} para {$proximaLocalizacao}",
            $observacao,
            $proximaLocalizacao,
            $pacote->estado_geral,
            $pacote->estado_glosa,
            Auth::id()
        );
        
        return redirect()->route('pacotes.index', ['localizacao' => $proximaLocalizacao])
            ->with('success', 'Pacote encaminhado com sucesso para ' . ucfirst($proximaLocalizacao) . '.');
    }

    /**
     * Verifica se um pacote pode ser movido de acordo com as regras de negócio
     */
    public function podeMover($id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Lógica especial para pacotes na localização SIRE
        if ($pacote->localizacao_atual == 'sire') {
            // Caso 1: Sem glosa e sem valor pendente -> deve mover para arquivo
            if ($pacote->valor_glosa == 0 && $pacote->valor_pendente == 0) {
                return response()->json([
                    'pode_mover' => true,
                    'destino_automatico' => 'arquivo',
                    'mensagem' => 'O pacote será encaminhado para Arquivo.'
                ]);
            }
            
            // Caso 2: Sem glosa, mas com valor pendente -> não permite mover
            if ($pacote->valor_glosa == 0 && $pacote->valor_pendente > 0) {
                return response()->json([
                    'pode_mover' => false,
                    'mensagem' => "Não é possível mover o pacote pois existe valor pendente de R$ " . 
                                   number_format($pacote->valor_pendente, 2, ',', '.') . 
                                   ". Informe os pagamentos antes de mover o pacote."
                ]);
            }

            // Caso 7: Com glosa, sem recurso, anterior=glosa, com valor pendente -> não permite mover
            if ($pacote->valor_glosa > 0 && 
                ($pacote->valor_recurso_glosa == 0 || $pacote->valor_recurso_glosa === null) && 
                $pacote->localizacao_anterior == 'glosa' && 
                $pacote->valor_pendente > 0) {
                return response()->json([
                    'pode_mover' => false,
                    'mensagem' => "Não é possível mover o pacote pois existe valor pendente de R$ " . 
                                   number_format($pacote->valor_pendente, 2, ',', '.') . 
                                   ". Informe os pagamentos antes de mover o pacote."
                ]);
            }
            
            // Outras lógicas para os demais casos...
        }
        
        // Para outras localizações, apenas confirmar que pode ser movido
        return response()->json([
            'pode_mover' => true,
            'mensagem' => 'Pacote pode ser movido.'
        ]);
    }

    /**
     * Exibe o histórico de movimentações de um pacote
     */
    public function movimentacoes($id)
    {
        $pacote = Pacote::findOrFail($id);
        $movimentacoes = MovimentacaoPacote::where('pacote_id', $id)
                                           ->with('usuario')
                                           ->orderBy('created_at', 'desc')
                                           ->get();
        
        return view('pacotes.movimentacoes', compact('pacote', 'movimentacoes'));
    }

    /**
     * Registra uma movimentação no histórico do pacote
     */
    private function registrarMovimentacao($pacoteId, $acao, $mensagem, $observacao, $localizacaoPosAcao, $estadoGeral, $estadoGlosa)
    {
        $movimentacao = new \App\Models\MovimentacaoPacote();
        $movimentacao->pacote_id = $pacoteId;
        $movimentacao->acao = $acao;
        $movimentacao->mensagem = $mensagem;
        $movimentacao->observacao = $observacao;
        $movimentacao->localizacao_pos_acao = $localizacaoPosAcao;
        $movimentacao->estado_geral = $estadoGeral;
        $movimentacao->estado_glosa = $estadoGlosa;
        $movimentacao->usuario_id = Auth::id();
        $movimentacao->save();
    }

    /**
     * Gera uma mensagem de alterações para o histórico
     */
    private function gerarMensagemAlteracoes($valoresAntigos, $valoresNovos)
    {
        Log::info("Iniciando geração de mensagem de alterações", [
            'valoresAntigos' => $valoresAntigos, 
            'valoresNovos' => $valoresNovos
        ]);
        
        $mensagem = 'Alterações realizadas: ';
        $alteracoesEncontradas = false;
        
        // Campo OCS/PSA
        if (isset($valoresAntigos['ocs_psa_id']) && isset($valoresNovos['ocs_psa_id']) && 
            $valoresAntigos['ocs_psa_id'] != $valoresNovos['ocs_psa_id']) {
            $nomeAntigoOcsPsa = OcsPsa::find($valoresAntigos['ocs_psa_id'])->nome ?? 'Desconhecido';
            $nomeNovoOcsPsa = OcsPsa::find($valoresNovos['ocs_psa_id'])->nome ?? 'Desconhecido';
            $mensagem .= "OCS/PSA alterado de '{$nomeAntigoOcsPsa}' para '{$nomeNovoOcsPsa}'; ";
            $alteracoesEncontradas = true;
            Log::info("Alteração OCS/PSA detectada", [
                'de' => $valoresAntigos['ocs_psa_id'] . " ($nomeAntigoOcsPsa)",
                'para' => $valoresNovos['ocs_psa_id'] . " ($nomeNovoOcsPsa)"
            ]);
        }
        
        // Outros campos
        $camposParaVerificar = [
            'numero_fatura' => 'Número da fatura',
            'valor_fatura' => 'Valor da fatura',
            'tipo_conta_id' => 'Tipo de conta',
            'valor_glosa' => 'Valor da glosa',
            'motivo_glosa_id' => 'Motivo da glosa',
            'descricao_glosa' => 'Descrição da glosa'
        ];
        
        foreach ($camposParaVerificar as $campo => $nome) {
            if (isset($valoresAntigos[$campo]) && isset($valoresNovos[$campo]) && 
                $valoresAntigos[$campo] != $valoresNovos[$campo]) {
                $mensagem .= "{$nome} alterado de '{$valoresAntigos[$campo]}' para '{$valoresNovos[$campo]}'; ";
                $alteracoesEncontradas = true;
                Log::info("Alteração $nome detectada", ['de' => $valoresAntigos[$campo], 'para' => $valoresNovos[$campo]]);
            }
        }
        
        if (!$alteracoesEncontradas) {
            $mensagem .= "Nenhuma alteração significativa detectada.";
            Log::info("Nenhuma alteração significativa detectada");
        }
        
        Log::info("Mensagem final de alterações", ['mensagem' => $mensagem]);
        return $mensagem;
    }

    /**
     * Registra um pagamento para o pacote
     */
    public function registrarPagamento(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar permissões
        if (!Auth::user()->isAdmin() && !Auth::user()->hasRole('sire')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para executar esta ação.'
            ], 403);
        }
        
        // Validar os dados
        $validated = $request->validate([
            'valor_pagamento' => 'required|string',
            'observacao' => 'nullable|string|max:500',
        ]);
        
        // Converter valor com formatação brasileira para decimal
        $valorPagamento = str_replace('.', '', $request->valor_pagamento);
        $valorPagamento = str_replace(',', '.', $valorPagamento);
        $valorPagamento = (float)$valorPagamento;
        
        // Verificar se o valor do pagamento é válido
        if ($valorPagamento <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'O valor do pagamento deve ser maior que zero.'
            ], 422);
        }
        
        // Verificar se o valor do pagamento não é maior que o valor pendente
        if ($valorPagamento > $pacote->valor_pendente) {
            return response()->json([
                'success' => false,
                'message' => 'O valor do pagamento não pode ser maior que o valor pendente.'
            ], 422);
        }
        
        // Atualizar os valores do pacote
        $pacote->valor_pago += $valorPagamento;
        $pacote->valor_pendente -= $valorPagamento;
        
        // NOVA LÓGICA: Se o valor pendente ficar em zero, alterar o estado geral para Normal
        if ($pacote->valor_pendente == 0) {
            $pacote->estado_geral = 'Normal';
        }
        
        $pacote->ultima_acao = 'Pagamento informado no valor de R$ ' . number_format($valorPagamento, 2, ',', '.');
        $pacote->save();
        
        // Registrar a movimentação no histórico
        $this->registrarMovimentacao(
            $pacote->id,
            'Implantar Pagamento',
            'Pagamento implantado no valor de R$ ' . number_format($valorPagamento, 2, ',', '.'),
            $request->observacao ?? '',
            $pacote->localizacao_atual,
            $pacote->estado_geral,  // Esta variável agora pode conter o novo estado "Normal"
            $pacote->estado_glosa,
            Auth::id()
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Pagamento registrado com sucesso.',
            'redirect' => route('pacotes.show', $pacote->id)
        ]);
    }

    /**
     * Registra que um pacote está aguardando limite de crédito
     */
    public function registrarAguardandoLimite(Request $request, $id)
    {
        // Obter o pacote
        $pacote = Pacote::findOrFail($id);
        $user = Auth::user();
        
        // Verificar permissões
        if (!($user->role === 'admin' || ($user->role === 'sire' && in_array($pacote->localizacao_atual, ['sire', 'glosa'])))) {
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Você não tem permissão para registrar que o pacote está aguardando limite de crédito.');
        }
        
        // Verificar se há valor pendente
        if ($pacote->valor_pendente <= 0) {
            return redirect()->route('pacotes.show', $pacote->id)
                ->with('error', 'Este pacote não possui valor pendente.');
        }
        
        // Atualizar o estado geral do pacote
        $pacote->estado_geral = "Aguardando Limite de Crédito";
        $pacote->ultima_acao = "Pacote marcado como Aguardando Limite de Crédito";
        $pacote->save();
        
        // Registrar a ação no histórico
        $mensagem = "Valor Pendente R$ " . number_format($pacote->valor_pendente, 2, ',', '.');
        $observacao = $request->input('observacao', '');
        
        $this->registrarMovimentacao(
            $pacote->id,
            'Aguardando Limite de Crédito',
            $mensagem,
            $observacao,
            $pacote->localizacao_atual,
            'Aguardando Limite de Crédito',
            $pacote->estado_glosa
        );
        
        return redirect()->route('pacotes.show', $pacote->id)
            ->with('success', 'Pacote marcado como Aguardando Limite de Crédito com sucesso!');
    }

    /**
     * Registra a notificação de existência de glosa para o pacote
     */
    public function notificarGlosa(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar permissões (apenas admin ou equipe glosa)
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'glosa') {
            return redirect()->back()->with('error', 'Você não tem permissão para executar esta ação.');
        }
        
        // Verificar se pacote está na localização correta (glosa)
        if ($pacote->localizacao_atual !== 'glosa') {
            return redirect()->back()->with('error', 'Esta ação só pode ser executada em pacotes na localização Glosa.');
        }
        
        // Validar formulário
        $request->validate([
            'meio_notificacao' => 'required',
            'detalhes_notificacao' => 'required',
            'data_notificacao' => 'required|date_format:d/m/Y', // Validação para o campo
            'data_limite_retirada' => 'required|date_format:d/m/Y',
        ]);
        
        // Atualizar o estado da glosa
        $pacote->estado_glosa = 'Existência de Glosa Notificada';
        
        // Registrar a data de notificação informada pelo usuário 
        // Alterado: antes usava a data de hoje, agora usa a data informada pelo usuário
        $pacote->data_notificacao_glosa = \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_notificacao)->startOfDay();
        
        // Registrar a data limite de retirada
        $pacote->data_limite_retirada = \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_limite_retirada)->startOfDay();
        
        // Atualizar a última ação
        $pacote->ultima_acao = 'Notificação de Existência de Glosa';
        
        $pacote->save();
        
        // Formatar texto para o histórico conforme especificado
        $meioNotificacao = $request->input('meio_notificacao');
        $detalhesNotificacao = $request->input('detalhes_notificacao');
        
        $mensagem = "OCS/PSA notificada de existência de glosa";
        
        // Formatar a observação com os detalhes da notificação
        $observacao = "Meio de notificação: {$meioNotificacao}. Detalhes: {$detalhesNotificacao}";
        
        // Registrar movimentação no histórico
        $this->registrarMovimentacao(
            $pacote->id,
            'Notificação de Existência de Glosa',
            $mensagem,
            $observacao,
            $pacote->localizacao_atual,
            $pacote->estado_geral,
            $pacote->estado_glosa
        );
        
        return redirect()->route('pacotes.show', $pacote->id)
            ->with('success', 'Notificação de existência de glosa registrada com sucesso.');
    }

    /**
     * Registra a retirada do ofício de glosa
     */
    public function registrarRetiradaOficio(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar permissões (apenas admin ou equipe glosa)
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'glosa') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para executar esta ação.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Você não tem permissão para executar esta ação.');
        }
        
        // Verificar se pacote está na localização correta (glosa)
        if ($pacote->localizacao_atual !== 'glosa') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta ação só pode ser executada em pacotes na localização "glosa".'
                ], 422);
            }
            return redirect()->back()->with('error', 'Esta ação só pode ser executada em pacotes na localização "glosa".');
        }
        
        // Verificar se o estado atual da glosa é "Existência de Glosa Notificada"
        if ($pacote->estado_glosa !== 'Existência de Glosa Notificada') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta ação só pode ser executada quando o estado da glosa for "Existência de Glosa Notificada".'
                ], 422);
            }
            return redirect()->back()->with('error', 'Esta ação só pode ser executada quando o estado da glosa for "Existência de Glosa Notificada".');
        }
        
        // Validar formulário
        $request->validate([
            'data_retirada_oficio' => 'required|date_format:d/m/Y',
            'observacao' => 'nullable|string|max:500',
        ]);

        // Converter a data de retirada informada pelo usuário
        $dataRetirada = \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_retirada_oficio)->startOfDay();

        // Verificar se a data de retirada é posterior ou igual à data de notificação
        // Alterado de lt (less than) para lt (less than) com data_notificacao_glosa-1 para permitir mesma data
        if ($dataRetirada->lt($pacote->data_notificacao_glosa->copy()->subDay())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A data de retirada do ofício deve ser igual ou posterior à data de notificação (' . 
                                  $pacote->data_notificacao_glosa->format('d/m/Y') . ').'
                ], 422);
            }
            
            return redirect()->back()->with('error', 'A data de retirada do ofício deve ser igual ou posterior à data de notificação (' . 
                              $pacote->data_notificacao_glosa->format('d/m/Y') . ').');
        }
        
        // 1. Mudar o Estado da Glosa para "Ofício de Glosa Retirado"
        $pacote->estado_glosa = 'Ofício de Glosa Retirado';
        
        // Registrar a data de retirada do ofício
        $pacote->data_retirada_oficio = $dataRetirada;
        
        // Atualizar a última ação
        $pacote->ultima_acao = 'Retirada de Ofício de Glosa';
        
        $pacote->save();
        
        // 2. Registrar movimentação no histórico
        $observacao = $request->input('observacao');
        
        $this->registrarMovimentacao(
            $pacote->id,
            'Retirada de Ofício de Glosa',
            'OCS/PSA retirou Ofício de Glosa',
            $observacao,
            $pacote->localizacao_atual,  // Localização permanece a mesma
            $pacote->estado_geral,       // Estado geral permanece o mesmo
            $pacote->estado_glosa        // Estado da glosa já foi atualizado para "Ofício de Glosa Retirado"
        );
        
        // 3. Mudar o Estado da Glosa para "Aguardando Recurso de Glosa"
        $pacote->estado_glosa = 'Aguardando Recurso de Glosa';
        $pacote->save();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Retirada de ofício de glosa registrada com sucesso.'
            ]);
        }
        
        return redirect()->route('pacotes.show', $pacote->id)
            ->with('success', 'Retirada de ofício de glosa registrada com sucesso.');
    }

    /**
     * Registra o recebimento do recurso de glosa
     */
    public function registrarRecebimentoRecurso(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar permissões (apenas admin ou equipe protocolo)
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'protocolo') {
            return redirect()->back()->with('error', 'Você não tem permissão para executar esta ação.');
        }
        
        // Verificar se o estado atual da glosa é "Aguardando Recurso de Glosa"
        if ($pacote->estado_glosa !== 'Aguardando Recurso de Glosa') {
            return redirect()->back()->with('error', 'Esta ação só pode ser executada em pacotes com estado de glosa "Aguardando Recurso de Glosa".');
        }
        
        // Validar formulário
        $request->validate([
            'data_recebimento_recurso' => 'required|date_format:d/m/Y',
            'observacao' => 'nullable|string|max:500',
        ]);
        
        // Converter a data de recebimento
        $dataRecebimento = \Carbon\Carbon::createFromFormat('d/m/Y', $request->data_recebimento_recurso)->startOfDay();
        
        // Verificar se a data de recebimento é posterior ou igual à data de retirada do ofício
        if ($pacote->data_retirada_oficio && $dataRecebimento->lt($pacote->data_retirada_oficio)) {
            return redirect()->back()->with('error', 'A data de recebimento do recurso deve ser igual ou posterior à data de retirada do ofício (' . 
                              $pacote->data_retirada_oficio->format('d/m/Y') . ').');
        }
        
        // Atualizar pacote
        $pacote->data_recebimento_recurso = $dataRecebimento;
        $pacote->estado_glosa = 'Recurso recebido';
        $pacote->ultima_acao = 'Recebimento de Recurso de Glosa';
        $pacote->save();
        
        // Registrar movimentação no histórico
        $observacao = $request->input('observacao');
        
        $this->registrarMovimentacao(
            $pacote->id,
            'Recebimento de Recurso de Glosa',
            'Recurso recebido',
            $observacao,
            $pacote->localizacao_atual, // Localização permanece como "glosa"
            $pacote->estado_geral,      // Estado geral permanece o mesmo
            $pacote->estado_glosa       // Estado da glosa já foi atualizado para "Recurso recebido"
        );
        
        return redirect()->route('pacotes.show', $pacote->id)
            ->with('success', 'Recebimento de recurso de glosa registrado com sucesso.');
    }

    /**
     * Registra que o recurso de glosa não foi recebido
     */
    public function registrarRecursoNaoRecebido(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar permissões (apenas admin ou equipe glosa)
        if (!Auth::user()->isAdmin() && !Auth::user()->hasRole('glosa')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para executar esta ação.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Você não tem permissão para executar esta ação.');
        }
        
        // Verificar se o estado atual da glosa é "Aguardando Recurso de Glosa"
        if ($pacote->estado_glosa !== 'Aguardando Recurso de Glosa') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta ação só pode ser executada quando o estado da glosa for "Aguardando Recurso de Glosa".'
                ], 422);
            }
            return redirect()->back()->with('error', 'Esta ação só pode ser executada quando o estado da glosa for "Aguardando Recurso de Glosa".');
        }
        
        // Determinar a próxima localização com base no valor pendente
        $proximaLocalizacao = $pacote->valor_pendente > 0 ? 'sire' : 'arquivo';
        
        // Atualizar o pacote
        $pacote->localizacao_anterior = $pacote->localizacao_atual;
        $pacote->localizacao_atual = $proximaLocalizacao;
        $pacote->estado_glosa = 'Recurso não recebido';
        $pacote->ultima_acao = 'Recurso não recebido';
        $pacote->save();
        
        // Registrar a movimentação no histórico
        $this->registrarMovimentacao(
            $pacote->id,
            'Recurso não recebido',
            'Recurso não recebido',
            $request->observacao ?? '',
            $pacote->localizacao_atual,
            $pacote->estado_geral,
            $pacote->estado_glosa,
            Auth::id()
        );
        
        // Preparar mensagem de sucesso
        $mensagem = 'Recurso não recebido registrado com sucesso. Pacote movido para ' . ucfirst($proximaLocalizacao) . '.';
        
        // Retornar resposta adequada
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'redirect' => route('pacotes.show', $pacote->id)
            ]);
        }
        
        return redirect()->route('pacotes.show', $pacote->id)->with('success', $mensagem);
    }

    /**
     * Exibe a view de Prazos e Notificações
     */
    public function prazosNotificacoes($id)
    {
        $pacote = Pacote::with(['ocsPsa', 'tipoPacote', 'tipoConta'])->findOrFail($id);
        return view('pacotes.prazos', compact('pacote'));
    }

    /**
     * Processa a análise de recurso de glosa
     */
    public function analisarRecurso(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar permissões
        if (!Auth::user()->isAdmin() && !Auth::user()->hasRole('glosa')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para executar esta ação.'
            ], 403);
        }
        
        // Validar os dados básicos
        $request->validate([
            'resultado' => 'required|in:deferido,indeferido',
            'observacao' => 'nullable|string|max:500',
        ]);
        
        // Validações adicionais apenas para resultado deferido
        if ($request->resultado === 'deferido') {
            $request->validate([
                'valor_recursado' => 'required|string',
                'valor_deferido' => 'required|string',
            ]);
            
            // Converter valores com formatação brasileira para decimal
            $valorRecursado = str_replace('.', '', $request->valor_recursado);
            $valorRecursado = str_replace(',', '.', $valorRecursado);
            
            $valorDeferido = str_replace('.', '', $request->valor_deferido);
            $valorDeferido = str_replace(',', '.', $valorDeferido);
            
            // Converter para números para a validação
            $valorRecursadoNum = (float)$valorRecursado;
            $valorDeferidoNum = (float)$valorDeferido;
            
            // Validar regras de negócio
            if ($valorRecursadoNum <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'O valor recursado deve ser maior que zero.'
                ], 422);
            }
            
            if ($valorDeferidoNum < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'O valor deferido não pode ser negativo.'
                ], 422);
            }
            
            if ($valorDeferidoNum > $valorRecursadoNum) {
                return response()->json([
                    'success' => false,
                    'message' => 'O valor deferido não pode ser maior que o valor recursado.'
                ], 422);
            }
            
            if ($valorRecursadoNum > $pacote->valor_glosa) {
                return response()->json([
                    'success' => false,
                    'message' => 'O valor recursado não pode ser maior que o valor da glosa.'
                ], 422);
            }
            
            // Atualizar os valores do pacote
            $pacote->valor_recursado = $valorRecursadoNum;
            $pacote->valor_deferido = $valorDeferidoNum;
            $pacote->valor_recurso_glosa = $valorDeferidoNum; // Usar o valor deferido como recurso de glosa
            
            // Atualizar o valor pendente (adicionar o valor deferido)
            $pacote->valor_pendente = $pacote->valor_pendente + $valorDeferidoNum;
            
            // Definir o estado da glosa
            $pacote->estado_glosa = 'Recurso Deferido';
            
            // Mudar a localização para SIRE (sempre, para deferido)
            $pacote->localizacao_anterior = $pacote->localizacao_atual;
            $pacote->localizacao_atual = 'sire';
        } else {
            // Se for indeferido
            $pacote->valor_recursado = 0;
            $pacote->valor_deferido = 0;
            $pacote->valor_recurso_glosa = 0;
            $pacote->estado_glosa = 'Recurso Indeferido';
            
            // Determinar próxima localização com base no valor pendente
            $proximaLocalizacao = ($pacote->valor_pendente > 0) ? 'sire' : 'arquivo';
            
            // Atualizar localização
            $pacote->localizacao_anterior = $pacote->localizacao_atual;
            $pacote->localizacao_atual = $proximaLocalizacao;
        }
        
        // Salvar as alterações
        $pacote->save();
        
        // Registrar a movimentação
        $mensagem = $request->resultado === 'deferido'
            ? "Recurso de Glosa DEFERIDO. Valor Recursado: R$ " . number_format($pacote->valor_recursado, 2, ',', '.') . 
              ". Valor Deferido: R$ " . number_format($pacote->valor_deferido, 2, ',', '.')
            : "Recurso de Glosa INDEFERIDO.";
        
        $this->registrarMovimentacao(
            $pacote->id,
            'Análise de Recurso de Glosa',
            $mensagem,
            $request->observacao,
            $pacote->localizacao_atual,
            $pacote->estado_geral,
            $pacote->estado_glosa
        );
        
        // Adicionar log específico para o resultado
        $this->registrarMovimentacao(
            $pacote->id,
            $request->resultado === 'deferido' ? 'Recurso deferido' : 'Recurso indeferido',
            $request->resultado === 'deferido' ? 'Recurso deferido' : 'Recurso indeferido',
            $request->observacao,
            $pacote->localizacao_atual,
            $pacote->estado_geral,
            $pacote->estado_glosa
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Análise de recurso registrada com sucesso.',
            'redirect' => route('pacotes.show', $pacote->id)
        ]);
    }

    /**
     * Arquiva um pacote, atualizando sua localização física e estado
     */
    public function arquivar(Request $request, $id)
    {
        $pacote = Pacote::findOrFail($id);
        
        // Verificar permissões (apenas admin ou equipe arquivo)
        if (!Auth::user()->isAdmin() && !Auth::user()->hasRole('arquivo')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para executar esta ação.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Você não tem permissão para executar esta ação.');
        }
        
        // Verificar se o pacote está em um estado válido para arquivamento
        if ($pacote->localizacao_atual != 'arquivo' && $pacote->localizacao_atual != 'arquivado') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pacote não pode ser arquivado no momento. O pacote deve estar na localização "arquivo".'
                ], 422);
            }
            return redirect()->back()->with('error', 'Este pacote não pode ser arquivado no momento. O pacote deve estar na localização "arquivo".');
        }
        
        // Validar os dados
        $validated = $request->validate([
            'localizacao_fisica' => 'required|string|max:255',
            'observacao' => 'nullable|string|max:500',
        ]);
        
        // Atualizar o pacote
        $pacote->localizacao_anterior = $pacote->localizacao_atual;
        $pacote->localizacao_atual = 'arquivado';
        $pacote->localizacao_fisica = $request->localizacao_fisica;
        $pacote->estado_geral = 'Arquivado';
        $pacote->ultima_acao = 'Pacote arquivado';
        $pacote->save();
        
        // Registrar a movimentação no histórico
        $this->registrarMovimentacao(
            $pacote->id,
            'Arquivo',
            'Pacote arquivado',
            'arquivado',
            'Arquivado',
            $pacote->estado_glosa,
            Auth::id()
        );
        
        // Preparar mensagem de sucesso
        $mensagem = 'Pacote arquivado com sucesso. Localização física: ' . $pacote->localizacao_fisica;
        
        // Retornar resposta adequada
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'redirect' => route('pacotes.show', $pacote->id)
            ]);
        }
        
        return redirect()->route('pacotes.show', $pacote->id)->with('success', $mensagem);
    }

    /**
     * Gera PDF do protocolo de entrega do pacote
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function imprimirProtocolo($id)
    {
        // Buscar pacote com relacionamento OCS/PSA
        $pacote = Pacote::with('ocsPsa')->findOrFail($id);
        
        // Carregar view do PDF
        $pdf = Pdf::loadView('pacotes.protocolo-pdf', compact('pacote'));
        
        // Configurar papel A4 em modo portrait (retrato)
        $pdf->setPaper('A4', 'portrait');
        
        // Retornar download do PDF
        return $pdf->download('protocolo-pacote-' . $pacote->id . '.pdf');
    }
}