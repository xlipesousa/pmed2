<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\PacotesController;
use App\Http\Controllers\OcsPsaController;
use App\Http\Controllers\ConfiguracoesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MapaController;
use App\Models\Pacote;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página inicial redireciona para login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'env' => app()->environment(),
        'time' => now()->toIso8601String(),
    ], 200);
});

// Rotas de autenticação
Auth::routes();

// Rota do dashboard protegida por autenticação
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        // REVERTER: Código original do dashboard
        return view('dashboard');
    })->name('dashboard');

    // Rotas de Pacotes
    Route::prefix('pacotes')->middleware(['auth'])->group(function () {
        Route::get('/', [PacotesController::class, 'index'])->name('pacotes.index');
        Route::get('/criar', [PacotesController::class, 'create'])->name('pacotes.create');
        Route::post('/', [PacotesController::class, 'store'])->name('pacotes.store');
        Route::get('/{id}', [PacotesController::class, 'show'])->name('pacotes.show');
        Route::get('/{id}/editar', [PacotesController::class, 'edit'])->name('pacotes.edit');
        Route::put('/{id}', [PacotesController::class, 'update'])->name('pacotes.update');
        Route::post('/{id}/mover', [PacotesController::class, 'mover'])->name('pacotes.mover');
        Route::get('/{id}/movimentacoes', [PacotesController::class, 'movimentacoes'])->name('pacotes.movimentacoes');
        Route::get('/{id}/protocolo', [PacotesController::class, 'imprimirProtocolo'])->name('pacotes.protocolo');
        // Rota para verificar se um pacote pode ser movido (verificação prévia)
        Route::get('/{id}/pode-mover', [PacotesController::class, 'podeMover'])->name('pacotes.pode-mover');
        // Adicionar esta nova rota para processar o pagamento
        Route::post('/{id}/pagamento', [PacotesController::class, 'registrarPagamento'])->name('pacotes.pagamento');
        // Rota para a ação "Aguardando Limite de Crédito"
        Route::post('/{id}/aguardar-limite', [PacotesController::class, 'registrarAguardandoLimite'])->name('pacotes.aguardar-limite');
        // Rota para a ação "Notificação de Existência de Glosa"
        Route::post('/{id}/notificar-glosa', [PacotesController::class, 'notificarGlosa'])->name('pacotes.notificar-glosa');
        // Rota para a ação "Retirada de Ofício de Glosa"
        Route::post('/pacotes/{id}/retirada-oficio-glosa', [PacotesController::class, 'registrarRetiradaOficio'])->name('pacotes.retirada-oficio-glosa');
        // Rota para a view de Prazos e Notificações
        Route::get('/pacotes/{id}/prazos', [PacotesController::class, 'prazosNotificacoes'])->name('pacotes.prazos');
        // Rota para a ação "Recebimento de Recurso de Glosa"
        Route::post('/{id}/recebimento-recurso', [PacotesController::class, 'registrarRecebimentoRecurso'])->name('pacotes.recebimento-recurso');
        // Rota para a ação "Recurso Não Recebido"
        Route::post('/{id}/recurso-nao-recebido', [PacotesController::class, 'registrarRecursoNaoRecebido'])->name('pacotes.recurso-nao-recebido');
        // Rota para a ação "Análise de Recurso de Glosa"
        Route::post('/{id}/analise-recurso', [PacotesController::class, 'analisarRecurso'])->name('pacotes.analise-recurso');
        // Rota para a ação "Arquivar Pacote"
        Route::post('/{id}/arquivar', [PacotesController::class, 'arquivar'])->name('pacotes.arquivar');
        // Adicionar na seção de rotas de pacotes
        Route::post('/{id}/atualizar-localizacao-fisica', [PacotesController::class, 'atualizarLocalizacaoFisica'])->name('pacotes.atualizar-localizacao-fisica');
    });

    // Rotas para perfil do usuário (dentro do grupo auth)
    Route::middleware(['auth'])->group(function () {
        Route::get('/perfil', [UserController::class, 'perfil'])->name('perfil');
        Route::put('/perfil/atualizar', [UserController::class, 'atualizarPerfil'])->name('perfil.atualizar');
        Route::put('/perfil/senha', [UserController::class, 'atualizarSenha'])->name('perfil.senha');
    });

    // Rotas protegidas apenas para administradores
    Route::middleware(['can:admin'])->group(function () {
        // Configurações
        Route::get('/configuracoes/sistema', [ConfiguracoesController::class, 'sistema'])->name('configuracoes.sistema');
        Route::get('/configuracoes/upgrade', [ConfiguracoesController::class, 'upgrade'])->name('configuracoes.upgrade');
        Route::post('/configuracoes/upgrade/verificar', [ConfiguracoesController::class, 'upgradeVerificar'])->name('configuracoes.upgrade.verificar');
        Route::post('/configuracoes/upgrade/executar', [ConfiguracoesController::class, 'upgradeExecutar'])->name('configuracoes.upgrade.executar');
        Route::post('/configuracoes/upgrade/worker', [ConfiguracoesController::class, 'upgradeWorker'])->name('configuracoes.upgrade.worker');
        Route::post('/configuracoes/sistema/salvar', [ConfiguracoesController::class, 'sistemasSalvar'])->name('configuracoes.sistema.salvar');
        
        // Gerenciamento de Tipos de Pacote
        Route::post('/configuracoes/sistema/tipos-pacote', [ConfiguracoesController::class, 'adicionarTipoPacote'])->name('configuracoes.tipos-pacote.adicionar');
        Route::put('/configuracoes/sistema/tipos-pacote/{id}', [ConfiguracoesController::class, 'editarTipoPacote'])->name('configuracoes.tipos-pacote.editar');
        Route::delete('/configuracoes/sistema/tipos-pacote/{id}', [ConfiguracoesController::class, 'excluirTipoPacote'])->name('configuracoes.tipos-pacote.excluir');
        
        // Gerenciamento de Tipos de Conta
        Route::post('/configuracoes/sistema/tipos-conta', [ConfiguracoesController::class, 'adicionarTipoConta'])->name('configuracoes.tipos-conta.adicionar');
        Route::put('/configuracoes/sistema/tipos-conta/{id}', [ConfiguracoesController::class, 'editarTipoConta'])->name('configuracoes.tipos-conta.editar');
        Route::delete('/configuracoes/sistema/tipos-conta/{id}', [ConfiguracoesController::class, 'excluirTipoConta'])->name('configuracoes.tipos-conta.excluir');
        
        // Gerenciamento de Motivos de Glosa
        Route::post('/configuracoes/sistema/motivos-glosa', [ConfiguracoesController::class, 'adicionarMotivoGlosa'])->name('configuracoes.motivos-glosa.adicionar');
        Route::put('/configuracoes/sistema/motivos-glosa/{id}', [ConfiguracoesController::class, 'editarMotivoGlosa'])->name('configuracoes.motivos-glosa.editar');
        Route::delete('/configuracoes/sistema/motivos-glosa/{id}', [ConfiguracoesController::class, 'excluirMotivoGlosa'])->name('configuracoes.motivos-glosa.excluir');
        
        // OCS/PSA
        Route::get('/configuracoes/ocspsa', [OcsPsaController::class, 'index'])->name('configuracoes.ocspsa');
        Route::post('/configuracoes/ocspsa', [OcsPsaController::class, 'store'])->name('configuracoes.ocspsa.store');
        Route::get('/configuracoes/ocspsa/{id}/editar', [OcsPsaController::class, 'edit'])->name('configuracoes.ocspsa.editar');
        Route::put('/configuracoes/ocspsa/{id}', [OcsPsaController::class, 'update'])->name('configuracoes.ocspsa.update');
        Route::delete('/configuracoes/ocspsa/{id}', [OcsPsaController::class, 'destroy'])->name('configuracoes.ocspsa.destroy');
        Route::post('/configuracoes/ocspsa/criar', [OcsPsaController::class, 'store'])->name('configuracoes.ocspsa.criar');
        Route::delete('/configuracoes/ocspsa/{id}/excluir', [OcsPsaController::class, 'destroy'])->name('configuracoes.ocspsa.excluir');
        Route::post('/configuracoes/ocspsa/{id}/toggle-status', [OcsPsaController::class, 'toggleStatus'])->name('configuracoes.ocspsa.toggle-status');
        
        // Usuários
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/criar', [UserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{id}', [UserController::class, 'show'])->name('usuarios.show');
        Route::get('/usuarios/{id}/editar', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
        Route::post('/usuarios/{id}/reset-password', [UserController::class, 'resetPassword'])->name('usuarios.reset-password');
        Route::post('/usuarios/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('usuarios.toggle-status');
    });

    // Rotas para Relatórios, Pesquisa e Gráficos
    Route::prefix('relatorios')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\RelatorioController::class, 'index'])->name('relatorios.index');
        Route::get('/status-pacotes', [App\Http\Controllers\RelatorioController::class, 'statusPacotes'])->name('relatorios.status-pacotes');
        Route::get('/performance', [App\Http\Controllers\RelatorioController::class, 'performance'])->name('relatorios.performance');
        Route::get('/glosas', [App\Http\Controllers\RelatorioController::class, 'glosas'])->name('relatorios.glosas');
        Route::get('/financeiro', [App\Http\Controllers\RelatorioController::class, 'financeiro'])->name('relatorios.financeiro');
        Route::get('/ocspsa', [App\Http\Controllers\RelatorioController::class, 'ocspsa'])->name('relatorios.ocspsa');
    });
    
    Route::get('/pesquisa', [App\Http\Controllers\PesquisaController::class, 'index'])->name('pesquisa.index');
    Route::get('/pesquisa/buscar', [App\Http\Controllers\PesquisaController::class, 'buscar'])->name('pesquisa.buscar');
    Route::get('/pesquisa/detalhe/{id}', [App\Http\Controllers\PesquisaController::class, 'detalhe'])->name('pesquisa.detalhe');
    Route::get('/pesquisa/exportar/{formato}', [App\Http\Controllers\PesquisaController::class, 'exportar'])->name('pesquisa.exportar');
    Route::post('/pesquisa/salvar', [App\Http\Controllers\PesquisaController::class, 'salvarPesquisa'])->name('pesquisa.salvar');
    Route::get('/pesquisa/listar', [App\Http\Controllers\PesquisaController::class, 'listarPesquisas'])->name('pesquisa.listar');
    Route::get('/pesquisa/carregar/{id}', [App\Http\Controllers\PesquisaController::class, 'carregarPesquisa'])->name('pesquisa.carregar');
    Route::delete('/pesquisa/excluir/{id}', [App\Http\Controllers\PesquisaController::class, 'excluirPesquisa'])->name('pesquisa.excluir');
    Route::get('/pesquisa/gerenciar', [App\Http\Controllers\PesquisaController::class, 'gerenciarPesquisas'])->name('pesquisa.gerenciar');
    
    Route::prefix('graficos')->middleware(['auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\GraficoController::class, 'index'])->name('graficos.index');
        Route::get('/kpis', [App\Http\Controllers\GraficoController::class, 'kpis'])->name('graficos.kpis');
        Route::get('/fluxo', [App\Http\Controllers\GraficoController::class, 'fluxo'])->name('graficos.fluxo');
        Route::get('/status', [App\Http\Controllers\GraficoController::class, 'status'])->name('graficos.status');
        Route::get('/tendencia', [App\Http\Controllers\GraficoController::class, 'tendencia'])->name('graficos.tendencia');
        Route::get('/volume', [App\Http\Controllers\GraficoController::class, 'volume'])->name('graficos.volume');
        Route::get('/tipo', [App\Http\Controllers\GraficoController::class, 'tipo'])->name('graficos.tipo');
        Route::get('/financeiro', [App\Http\Controllers\GraficoController::class, 'financeiro'])->name('graficos.financeiro');
        Route::get('/glosas', [App\Http\Controllers\GraficoController::class, 'glosas'])->name('graficos.glosas');
        Route::get('/performance', [App\Http\Controllers\GraficoController::class, 'performance'])->name('graficos.performance');
        Route::get('/desempenho', [App\Http\Controllers\GraficoController::class, 'desempenho'])->name('graficos.desempenho');
        Route::get('/desempenho/exportar/{tipo}', [App\Http\Controllers\GraficoController::class, 'exportarDesempenho'])->name('graficos.desempenho.exportar');
        Route::get('/exportar/{tipo}', [App\Http\Controllers\GraficoController::class, 'exportar'])->name('graficos.exportar');
    });

    // Rotas para Mapas de Pagamento - SEPARADAS POR PERMISSÃO
    // Rotas de visualização (admin, pagamento e auditor)
    Route::group(['middleware' => ['auth', 'can:mapas-view']], function () {
        // Rotas de visualização
        Route::get('/mapas', [MapaController::class, 'index'])->name('mapas.index');
        Route::get('/mapas/pesquisa', [MapaController::class, 'pesquisa'])->name('mapas.pesquisa');
        Route::get('/mapas/buscar', [MapaController::class, 'buscar'])->name('mapas.buscar');
        Route::get('/mapas/{id}', [MapaController::class, 'show'])
            ->where('id', '[0-9]+') // Adiciona restrição para aceitar apenas números
            ->name('mapas.show');
        Route::get('/mapas/{id}/exportar/{formato?}', [MapaController::class, 'exportar'])
            ->where('id', '[0-9]+')
            ->name('mapas.exportar');
        Route::get('/pacotes/{id}/mapas', [MapaController::class, 'visualizarPacote'])->name('mapas.visualizar-pacote');
        
        // Rota para faturas (somente visualização)
        Route::get('/faturas/pesquisa', [MapaController::class, 'pesquisaFatura'])->name('faturas.pesquisa');
        Route::get('/faturas/buscar', [MapaController::class, 'buscarFatura'])->name('faturas.buscar');
        Route::get('/faturas/{id}/exportar/{formato?}', [MapaController::class, 'exportarFatura'])->name('faturas.exportar');
    });

    // Rotas de gerenciamento (admin e pagamento)
    Route::group(['middleware' => ['auth', 'can:mapas-manage']], function () {
        // Rotas de criação, edição e exclusão
        Route::get('/mapas/criar', [MapaController::class, 'create'])->name('mapas.create');
        Route::post('/mapas', [MapaController::class, 'store'])->name('mapas.store');
        Route::get('/mapas/{id}/editar', [MapaController::class, 'edit'])->name('mapas.edit');
        Route::put('/mapas/{id}', [MapaController::class, 'update'])->name('mapas.update');
        Route::delete('/mapas/{id}', [MapaController::class, 'destroy'])->name('mapas.destroy');
        
        // Rotas para gerenciar faturas nos mapas
        Route::post('/mapas/{id}/adicionar-fatura', [MapaController::class, 'adicionarFatura'])->name('mapas.adicionar-fatura');
        Route::delete('/mapas/{mapaId}/remover-fatura/{pacoteId}', [MapaController::class, 'removerFatura'])->name('mapas.remover-fatura');
        Route::get('/mapas/{mapaId}/editar-fatura/{pacoteId}', [MapaController::class, 'editarFatura'])->name('mapas.editar-fatura');
        Route::put('/mapas/{mapaId}/atualizar-fatura/{pacoteId}', [MapaController::class, 'atualizarFatura'])->name('mapas.atualizar-fatura');
    });

    // Rotas de Anulação - CORRIGIR NOMES
    Route::middleware(['auth'])->group(function () {
        // Anulação de Pacotes
        Route::prefix('configuracoes')->name('configuracoes.')->group(function () {
            Route::get('/anulacao', [ConfiguracoesController::class, 'anulacao'])->name('anulacao');
            
            // CORRIGIR: Mudar de 'anulacao.buscar-pacote' para 'anulacao.buscar'
            Route::get('/anulacao/buscar-pacote/{id}', [ConfiguracoesController::class, 'buscarPacote'])
                ->name('anulacao.buscar')
                ->middleware('can:anular-pacotes');
            
            // CORRIGIR: Mudar de 'anulacao.anular-pacote' para 'anulacao.anular'
            Route::post('/anulacao/anular-pacote', [ConfiguracoesController::class, 'anularPacote'])
                ->name('anulacao.anular')
                ->middleware('can:anular-pacotes');
            
            Route::get('/anulacao/listar', [ConfiguracoesController::class, 'listarPacotesAnulados'])
                ->name('anulacao.listar')
                ->middleware('can:anular-pacotes');
        });
    });

    // Rota para ver detalhes do pacote anulado
    Route::get('/configuracoes/anulacao/ver/{id}', [ConfiguracoesController::class, 'verPacoteAnulado'])
        ->name('anulacao.ver');
});

// Redirecionar para login se tentar acessar rotas protegidas sem estar logado
Route::fallback(function () {
    return redirect()->route('login');
});
