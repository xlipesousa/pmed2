@extends('configuracoes.layout')

@section('configuracoes_content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Upgrade do Sistema</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Informacoes da versao atual do sistema (commit e data). Nesta fase, a pagina e somente leitura.
            </p>

            <dl class="row">
                <dt class="col-sm-4">Commit atual</dt>
                <dd class="col-sm-8">{{ session('upgrade_commit', $commitHash) }}</dd>

                <dt class="col-sm-4">Data do commit</dt>
                <dd class="col-sm-8">{{ session('upgrade_commit_date', $commitDate) }}</dd>
            </dl>

            <form action="{{ route('configuracoes.upgrade.verificar') }}" method="POST" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync"></i> Verificar atualizacoes
                </button>
            </form>

            <form action="{{ route('configuracoes.upgrade.worker') }}" method="POST" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-server"></i> Verificar status do worker
                </button>
            </form>

            <form action="{{ route('configuracoes.upgrade.executar') }}" method="POST" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-warning" onclick="return confirm('Deseja executar o upgrade agora?');">
                    <i class="fas fa-arrow-up"></i> Executar upgrade
                </button>
            </form>

            @if(session('upgrade_status') === 'atualizado')
                <div class="alert alert-success">
                    Sistema atualizado. Nenhuma atualizacao pendente.
                </div>
            @elseif(session('upgrade_status') === 'atualizacao_disponivel')
                <div class="alert alert-warning">
                    Ha atualizacoes disponiveis no repositorio oficial.
                </div>
            @elseif(session('upgrade_status') === 'indisponivel')
                <div class="alert alert-danger">
                    Nao foi possivel verificar atualizacoes.
                </div>
            @endif

            @if(session('upgrade_message'))
                <div class="alert alert-secondary">
                    {{ session('upgrade_message') }}
                </div>
            @endif

            @if(session('upgrade_execucao') === 'em_execucao')
                <div class="alert alert-info">
                    Upgrade iniciado. Acompanhe o andamento em storage/logs/upgrade.log.
                </div>
            @elseif(session('upgrade_execucao') === 'sucesso')
                <div class="alert alert-success">
                    Upgrade executado com sucesso.
                </div>
            @elseif(session('upgrade_execucao') === 'erro')
                <div class="alert alert-danger">
                    Falha ao executar upgrade. Verifique o log em storage/logs/upgrade.log.
                </div>
            @endif

            @if($upgradeStatus['status'] ?? null)
                <div class="alert alert-secondary">
                    Status atual: {{ $upgradeStatus['status'] }}
                    @if(!empty($upgradeStatus['updated_at']))
                        | Atualizado em: {{ $upgradeStatus['updated_at'] }}
                    @endif
                </div>
            @endif

            @if(session('upgrade_worker'))
                <div class="alert alert-{{ session('upgrade_worker') === 'ativo' ? 'success' : 'danger' }}">
                    Worker: {{ session('upgrade_worker') }}
                    @if(session('upgrade_queue'))
                        | Queue: {{ session('upgrade_queue') }}
                    @endif
                </div>
            @endif

            <div class="alert alert-warning">
                <strong>Atencao:</strong> O upgrade pode levar alguns minutos. Se o worker de fila nao estiver ativo,
                a execucao pode ocorrer no request e demorar.
                <div class="mt-2">
                    Acoes recomendadas:
                    <ul>
                        <li>Garantir que o worker de fila esteja ativo antes de executar o upgrade.</li>
                        <li>O repositorio precisa existir em {{ base_path() }} para checagens e upgrade.</li>
                        <li>Acompanhar o log em <code>storage/logs/upgrade.log</code>.</li>
                        <li>Se houver falha, revise o log e tente novamente.</li>
                    </ul>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                A verificacao de atualizacoes e a execucao do upgrade serao habilitadas nas proximas fases.
            </div>
        </div>
    </div>
@stop
