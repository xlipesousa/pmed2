@extends('adminlte::page')

@section('title', 'Pacotes')

@section('content_header')
    <h1>Gerenciamento de Pacotes</h1>
@stop

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
        <li class="breadcrumb-item active">Pacotes</li>
    </ol>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="pacotesTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $localizacaoAtiva == 'protocolo' ? 'active' : '' }}" id="protocolo-tab" data-toggle="tab" href="#protocolo" role="tab" 
                       aria-controls="protocolo" aria-selected="{{ $localizacaoAtiva == 'protocolo' ? 'true' : 'false' }}">Protocolo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $localizacaoAtiva == 'lisura' ? 'active' : '' }}" id="lisura-tab" data-toggle="tab" href="#lisura" role="tab" 
                       aria-controls="lisura" aria-selected="{{ $localizacaoAtiva == 'lisura' ? 'true' : 'false' }}">Lisura</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $localizacaoAtiva == 'sire' ? 'active' : '' }}" id="sire-tab" data-toggle="tab" href="#sire" role="tab" 
                       aria-controls="sire" aria-selected="{{ $localizacaoAtiva == 'sire' ? 'true' : 'false' }}">SIRE</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $localizacaoAtiva == 'glosa' ? 'active' : '' }}" id="glosa-tab" data-toggle="tab" href="#glosa" role="tab" 
                       aria-controls="glosa" aria-selected="{{ $localizacaoAtiva == 'glosa' ? 'true' : 'false' }}">Glosa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $localizacaoAtiva == 'arquivo' ? 'active' : '' }}" id="arquivo-tab" data-toggle="tab" href="#arquivo" role="tab" 
                       aria-controls="arquivo" aria-selected="{{ $localizacaoAtiva == 'arquivo' ? 'true' : 'false' }}">Arquivo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $localizacaoAtiva == 'arquivados' ? 'active' : '' }}" id="arquivados-tab" data-toggle="tab" href="#arquivados" role="tab" 
                       aria-controls="arquivados" aria-selected="{{ $localizacaoAtiva == 'arquivados' ? 'true' : 'false' }}">Arquivados</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="pacotesTabContent">
                <!-- TAB PROTOCOLO -->
                <div class="tab-pane fade {{ $localizacaoAtiva == 'protocolo' ? 'show active' : '' }}" id="protocolo" role="tabpanel" aria-labelledby="protocolo-tab">
                    <div class="mb-3">
                        <button id="receber-pacote" class="btn btn-primary mr-2">
                            <i class="fas fa-plus-circle"></i> Receber Pacote
                        </button>
                        <button id="mover-lote-lisura" class="btn btn-secondary">
                            <i class="fas fa-exchange-alt"></i> Mover em Lote
                        </button>
                    </div>
                    <table id="tabela-protocolo" class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="check-all-protocolo">
                                </th>
                                <th width="80">ID</th>
                                <th>Fatura</th>
                                <th>OCS/PSA</th>
                                <th>Data Entrada</th>
                                <th>Valor</th>
                                <th>Tipo</th><!-- Nova coluna adicionada -->
                                <th>Status</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pacotes as $pacote)
                                @if($pacote->localizacao_atual == 'protocolo' || 
                                    ($pacote->localizacao_atual == 'glosa' && $pacote->estado_glosa == 'Aguardando Recurso de Glosa'))
                                    <tr class="{{ $pacote->localizacao_atual == 'glosa' ? 'aguardando-recurso' : '' }}">
                                        <td>
                                            @if($pacote->localizacao_atual == 'protocolo')
                                                <input type="checkbox" class="check-item-protocolo" value="{{ $pacote->id }}">
                                            @else
                                                <!-- Pacotes aguardando recurso não podem ser selecionados para movimentação em lote -->
                                                <i class="fas fa-exclamation-circle text-info" title="Pacote aguardando recurso de glosa"></i>
                                            @endif
                                        </td>
                                        <td>{{ $pacote->id }}</td>
                                        <td>{{ $pacote->numero_fatura }}</td>
                                        <td>{{ optional($pacote->ocsPsa)->nome }}</td>
                                        <td>{{ date('d/m/Y', strtotime($pacote->data_entrada)) }}</td>
                                        <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                                        <td>{{ optional($pacote->tipoPacote)->nome }}</td><!-- Nova coluna adicionada -->
                                        <td>
                                            @if($pacote->localizacao_atual == 'glosa')
                                                <span class="badge badge-info">Aguardando Recurso</span>
                                            @else
                                                <span class="badge badge-{{ $pacote->estado_geral == 'Normal' ? 'success' : 'warning' }}">
                                                    {{ $pacote->estado_geral }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if((Auth::user()->role == 'admin' || Auth::user()->role == 'protocolo') && $pacote->localizacao_atual == 'protocolo')
                                                    <a href="{{ route('pacotes.edit', $pacote->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                @endif
                                                @if($pacote->localizacao_atual == 'protocolo')
                                                    <a href="#" class="btn btn-sm btn-success btn-mover-pacote" 
                                                       data-id="{{ $pacote->id }}" data-localizacao="{{ $pacote->localizacao_atual }}">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('pacotes.movimentacoes', $pacote->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                                <a href="{{ route('pacotes.protocolo', $pacote->id) }}" class="btn btn-sm btn-warning" target="_blank" title="Imprimir Protocolo">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(count($pacotes->where('localizacao_atual', 'protocolo')) == 0 && 
                        count($pacotes->where('localizacao_atual', 'glosa')->where('estado_glosa', 'Aguardando Recurso de Glosa')) == 0)
                        <div class="alert alert-info">
                            Nenhum pacote no Protocolo no momento.
                        </div>
                    @endif
                </div>
                
                <!-- TAB LISURA -->
                <div class="tab-pane fade {{ $localizacaoAtiva == 'lisura' ? 'show active' : '' }}" id="lisura" role="tabpanel" aria-labelledby="lisura-tab">
                    <div class="mb-3">
                        <button id="mover-lote-lisura" class="btn btn-secondary">
                            <i class="fas fa-exchange-alt"></i> Mover em Lote
                        </button>
                    </div>
                    <table id="tabela-lisura" class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th width="30px"><input type="checkbox" id="check-all-lisura"></th>
                                <th width="60px">Nº do Pacote</th>
                                <th>Nº da Fatura</th>
                                <th>OCS/PSA</th>
                                <th>Data Protocolo</th>
                                <th>Valor Fatura</th>
                                <th>Valor Glosa</th>
                                <th>Tipo</th><!-- Alterado de "Valor Pendente" para "Tipo" -->
                                <th>Status</th>
                                <th width="150px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pacotes as $pacote)
                                @if($pacote->localizacao_atual == 'lisura')
                                    <tr>
                                        <td><input type="checkbox" class="check-item-lisura" value="{{ $pacote->id }}"></td>
                                        <td>{{ $pacote->id }}</td>
                                        <td>{{ $pacote->numero_fatura }}</td>
                                        <td>{{ optional($pacote->ocsPsa)->nome }}</td>
                                        <td>{{ date('d/m/Y', strtotime($pacote->data_entrada)) }}</td>
                                        <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</td>
                                        <td>{{ optional($pacote->tipoPacote)->nome }}</td><!-- Alterado: exibe o tipo do pacote ao invés do valor pendente -->
                                        <td>
                                            <span class="badge badge-{{ $pacote->estado_geral == 'Normal' ? 'success' : 'warning' }}">
                                                {{ $pacote->estado_geral }}
                                            </span>
                                            <br>
                                            <span class="badge badge-{{ $pacote->estado_glosa == 'Não identificada' ? 'secondary' : 'danger' }}">
                                                {{ $pacote->estado_glosa }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'lisura')
                                                    <a href="{{ route('pacotes.edit', $pacote->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-success btn-mover-pacote" 
                                                       data-id="{{ $pacote->id }}" 
                                                       data-localizacao="{{ $pacote->localizacao_atual }}"
                                                       data-destino="sire">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('pacotes.movimentacoes', $pacote->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(count($pacotes->where('localizacao_atual', 'lisura')) == 0)
                        <div class="alert alert-info mt-3">
                            Nenhum pacote encontrado para esta localização.
                        </div>
                    @endif
                </div>
                
                <!-- TAB SIRE -->
                <div class="tab-pane fade {{ $localizacaoAtiva == 'sire' ? 'show active' : '' }}" id="sire" role="tabpanel" aria-labelledby="sire-tab">
                    <div class="mb-3">
                        <button id="mover-lote-sire" class="btn btn-secondary">
                            <i class="fas fa-exchange-alt"></i> Mover em Lote
                        </button>
                    </div>
                    <table id="tabela-sire" class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th width="30px"><input type="checkbox" id="check-all-sire"></th>
                                <th width="60px">Nº do Pacote</th>
                                <th>Nº da Fatura</th>
                                <th>OCS/PSA</th>
                                <th>Data Protocolo</th>
                                <th>Valor Fatura</th>
                                <th>Valor Glosa</th>
                                <th>Valor Pendente</th>
                                <th>Status</th>
                                <th width="150px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pacotes as $pacote)
                                @if($pacote->localizacao_atual == 'sire' || ($pacote->localizacao_atual == 'glosa' && $pacote->valor_pendente > 0))
                                    <tr class="{{ $pacote->localizacao_atual == 'glosa' ? 'bg-warning-light' : '' }}">
                                        <td><input type="checkbox" class="check-item-sire" value="{{ $pacote->id }}"></td>
                                        <td>{{ $pacote->id }}</td>
                                        <td>{{ $pacote->numero_fatura }}</td>
                                        <td>{{ optional($pacote->ocsPsa)->nome }}</td>
                                        <td>{{ date('d/m/Y', strtotime($pacote->data_entrada)) }}</td>
                                        <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($pacote->valor_pendente, 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $pacote->estado_geral == 'Normal' ? 'success' : 'warning' }}">
                                                {{ $pacote->estado_geral }}
                                            </span>
                                            <br>
                                            <span class="badge badge-{{ $pacote->estado_glosa == 'Não identificada' ? 'secondary' : 'danger' }}">
                                                {{ $pacote->estado_glosa }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if(Auth::user()->role == 'admin' || Auth::user()->role == $pacote->localizacao_atual)
                                                    <a href="#" class="btn btn-sm btn-success btn-mover-pacote" 
                                                       data-id="{{ $pacote->id }}" 
                                                       data-localizacao="{{ $pacote->localizacao_atual }}"
                                                       data-destino="glosa">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('pacotes.movimentacoes', $pacote->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(count($pacotes->where('localizacao_atual', 'sire')) == 0 && count($pacotes->where('localizacao_atual', 'glosa')->where('valor_pendente', '>', 0)) == 0)
                        <div class="alert alert-info mt-3">
                            Nenhum pacote encontrado para esta localização.
                        </div>
                    @endif
                </div>
                
                <!-- TAB GLOSA -->
                <div class="tab-pane fade {{ $localizacaoAtiva == 'glosa' ? 'show active' : '' }}" id="glosa" role="tabpanel" aria-labelledby="glosa-tab">
                    <table id="tabela-glosa" class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="check-all-glosa">
                                </th>
                                <th width="80">ID</th>
                                <th>Fatura</th>
                                <th>OCS/PSA</th>
                                <th>Data Entrada</th>
                                <th>Valor Fatura</th>
                                <th>Valor Glosa</th>
                                <th>Valor Pendente</th>
                                <th>Status</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pacotes as $pacote)
                                @if($pacote->localizacao_atual == 'glosa')
                                    <tr class="{{ $pacote->estado_glosa == 'Aguardando Recurso de Glosa' ? 'aguardando-recurso' : '' }}">
                                        <td>
                                            <input type="checkbox" class="check-item-glosa" value="{{ $pacote->id }}">
                                        </td>
                                        <td>{{ $pacote->id }}</td>
                                        <td>{{ $pacote->numero_fatura }}</td>
                                        <td>{{ optional($pacote->ocsPsa)->nome }}</td>
                                        <td>{{ date('d/m/Y', strtotime($pacote->data_entrada)) }}</td>
                                        <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</td>
                                        <td>R$ {{ number_format($pacote->valor_pendente, 2, ',', '.') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $pacote->estado_geral == 'Normal' ? 'success' : 'warning' }}">
                                                {{ $pacote->estado_geral }}
                                            </span>
                                            <br>
                                            <span class="badge badge-{{ $pacote->estado_glosa == 'Não identificada' ? 'secondary' : 'danger' }}">
                                                {{ $pacote->estado_glosa }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($pacote->localizacao_atual == 'protocolo')
                                                    <a href="#" class="btn btn-sm btn-success btn-mover-pacote" 
                                                       data-id="{{ $pacote->id }}" data-localizacao="{{ $pacote->localizacao_atual }}">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ route('pacotes.movimentacoes', $pacote->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(count($pacotes->where('localizacao_atual', 'glosa')) == 0)
                        <div class="alert alert-info">
                            Nenhum pacote na Glosa no momento.
                        </div>
                    @endif
                </div>
                
                <!-- TAB ARQUIVO -->
                <div class="tab-pane fade {{ $localizacaoAtiva == 'arquivo' ? 'show active' : '' }}" id="arquivo" role="tabpanel" aria-labelledby="arquivo-tab">
                    <div class="mb-3">
                        <button id="mover-lote-arquivo" class="btn btn-secondary">
                            <i class="fas fa-exchange-alt"></i> Mover em Lote
                        </button>
                    </div>
                    <table id="tabela-arquivo" class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th width="30px"><input type="checkbox" id="check-all-arquivo"></th>
                                <th width="60px">Nº do Pacote</th>
                                <th>Nº da Fatura</th>
                                <th>OCS/PSA</th>
                                <th>Data Protocolo</th>
                                <th>Valor Fatura</th>
                                <th>Valor Glosa</th>
                                <th>Valor Pendente</th>
                                <th>Status</th>
                                <th width="150px">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pacotes->where('localizacao_atual', 'arquivo') as $pacote)
                                <tr>
                                    <td><input type="checkbox" class="check-item-arquivo" value="{{ $pacote->id }}"></td>
                                    <td>{{ $pacote->id }}</td>
                                    <td>{{ $pacote->numero_fatura }}</td>
                                    <td>{{ optional($pacote->ocsPsa)->nome }}</td>
                                    <td>{{ date('d/m/Y', strtotime($pacote->data_entrada)) }}</td>
                                    <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($pacote->valor_pendente, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $pacote->estado_geral == 'Normal' ? 'success' : 'warning' }}">
                                            {{ $pacote->estado_geral }}
                                        </span>
                                        <br>
                                        <span class="badge badge-{{ $pacote->estado_glosa == 'Não identificada' ? 'secondary' : 'danger' }}">
                                            {{ $pacote->estado_glosa }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->role == 'admin' || Auth::user()->role == 'arquivo')
                                            <a href="#" class="btn btn-sm btn-primary btn-arquivar" 
                                               data-toggle="modal" data-target="#modalArquivar"
                                               data-id="{{ $pacote->id }}" data-localizacao-fisica="{{ $pacote->localizacao_fisica ?? '' }}">
                                                <i class="fas fa-archive"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(count($pacotes->where('localizacao_atual', 'arquivo')) == 0)
                        <div class="alert alert-info mt-3">
                            Nenhum pacote encontrado para esta localização.
                        </div>
                    @endif
                </div>
                
                <!-- TAB ARQUIVADOS -->
                <div class="tab-pane fade {{ $localizacaoAtiva == 'arquivados' ? 'show active' : '' }}" id="arquivados" role="tabpanel" aria-labelledby="arquivados-tab">
                    <table id="tabela-arquivados" class="table table-bordered table-striped dataTable">
                        <thead>
                            <tr>
                                <th width="30px"><input type="checkbox" id="check-all-arquivados"></th>
                                <th width="60px">Nº do Pacote</th>
                                <th>Nº da Fatura</th>
                                <th>OCS/PSA</th>
                                <th>Valor da Fatura</th>
                                <th>Valor Pós-Lisura</th>
                                <th>Valor Glosa</th>
                                <th>Estado Glosa</th>
                                <th>Localização Física</th>
                                <th width="150">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pacotes->where('localizacao_atual', 'arquivado') as $pacote)
                                <tr>
                                    <td><input type="checkbox" class="check-item-arquivados" value="{{ $pacote->id }}"></td>
                                    <td>{{ $pacote->id }}</td>
                                    <td>{{ $pacote->numero_fatura }}</td>
                                    <td>{{ optional($pacote->ocsPsa)->nome }}</td>
                                    <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($pacote->valor_pos_lisura, 2, ',', '.') }}</td>
                                    <td>R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</td>
                                    <td>{{ $pacote->estado_glosa }}</td>
                                    <td>{{ $pacote->localizacao_fisica ?? 'Não informada' }}</td>
                                    <td>
                                        <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->role == 'admin' || Auth::user()->role == 'arquivo')
                                        <a href="#" class="btn btn-sm btn-info btn-editar-localizacao" 
                                           data-toggle="modal" data-target="#modalEditarLocalizacaoFisica"
                                           data-id="{{ $pacote->id }}" data-localizacao-fisica="{{ $pacote->localizacao_fisica ?? '' }}">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(count($pacotes->where('localizacao_atual', 'arquivado')) == 0)
                        <div class="alert alert-info mt-3">
                            Nenhum pacote encontrado para esta localização.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->role == 'admin' || Auth::user()->role == 'protocolo')
        <div class="modal fade" id="modalReceberPacote" tabindex="-1" role="dialog" aria-labelledby="modalReceberPacoteLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalReceberPacoteLabel">Receber Pacote</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Selecione o tipo de recebimento:</p>
                        <div class="d-flex flex-wrap mb-3">
                            <button type="button" class="btn btn-primary mr-2 mb-2" id="btn-novo-pacote">
                                <i class="fas fa-plus-circle"></i> Novo Pacote
                            </button>
                            <button type="button" class="btn btn-outline-primary mb-2" id="btn-recurso-glosa">
                                <i class="fas fa-file-import"></i> Recurso de Glosa
                            </button>
                        </div>

                        <div id="recurso-glosa-fields" class="d-none">
                            <div class="form-group">
                                <label for="numero_fatura_recurso">Numero da Fatura</label>
                                <input type="text" class="form-control" id="numero_fatura_recurso" placeholder="Ex: FT-1234">
                            </div>
                            <div class="form-group">
                                <label for="ocs_psa_id_recurso">OCS/PSA</label>
                                <select class="form-control" id="ocs_psa_id_recurso">
                                    <option value="">Selecione...</option>
                                    @foreach($ocsPsaList as $ocsPsa)
                                        <option value="{{ $ocsPsa->id }}">{{ $ocsPsa->nome }} {{ $ocsPsa->codigo_interno ? '(' . $ocsPsa->codigo_interno . ')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button" class="btn btn-success" id="btn-pesquisar-recurso">
                                <i class="fas fa-search"></i> Pesquisar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Confirmação para Mover Pacote -->
    <div class="modal fade" id="modalMoverPacote" tabindex="-1" role="dialog" aria-labelledby="modalMoverPacoteLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMoverPacoteLabel">Confirmar Encaminhamento do Pacote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Você está movendo o pacote que está em <strong><span id="localAtual"></span></strong> para <strong><span id="localDestino"></span></strong>.</p>
                    
                    <form id="formMoverPacote" action="" method="POST">
                        @csrf
                        <input type="hidden" id="modalDestino" name="destino">
                        <div class="form-group">
                            <label for="observacao">Observação (opcional)</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="formMoverPacote">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Arquivar -->
    <div class="modal fade" id="modalArquivar" tabindex="-1" role="dialog" aria-labelledby="modalArquivarLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalArquivarLabel">Arquivar Pacote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formArquivar" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Informe a localização física onde o pacote será arquivado:</p>
                        
                        <div class="form-group">
                            <label for="localizacao_fisica">Localização Física</label>
                            <input type="text" class="form-control" id="localizacao_fisica" name="localizacao_fisica" 
                                   required placeholder="Ex: Armário 3, Prateleira B, Caixa 15">
                        </div>
                        
                        <div class="form-group">
                            <label for="observacao">Observação (opcional)</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"
                                      placeholder="Informações adicionais sobre o arquivamento..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Arquivar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Editar Localização Física -->
    <div class="modal fade" id="modalEditarLocalizacaoFisica" tabindex="-1" role="dialog" aria-labelledby="modalEditarLocalizacaoFisicaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLocalizacaoFisicaLabel">Editar Localização Física</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditarLocalizacaoFisica" action="#" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Informe a nova localização física do pacote:</p>
                        
                        <div class="form-group">
                            <label for="localizacao_fisica">Localização Física</label>
                            <input type="text" class="form-control" id="localizacao_fisica" name="localizacao_fisica" 
                                   required placeholder="Ex: Armário 3, Prateleira B, Caixa 15">
                        </div>
                        
                        <div class="form-group">
                            <label for="observacao">Observação (opcional)</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"
                                      placeholder="Informações adicionais sobre a alteração..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        .dataTable th {
            vertical-align: middle !important;
        }
        
        .card-header-tabs {
            margin-bottom: -0.75rem;
        }
        
        /* Nova classe para destacar pacotes de glosa na aba SIRE */
        .bg-warning-light {
            background-color: #fff3cd !important;
        }

        /* Nova classe para destacar pacotes aguardando recurso de glosa */
        .aguardando-recurso {
            background-color: #d1ecf1 !important; /* Azul claro */
        }
    </style>
@stop

@section('js')
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Configuração do Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            // Exibir mensagens usando Toastr
            @if(session('success'))
                toastr.success("{{ session('success') }}");
            @endif
            
            @if(session('error'))
                toastr.error("{{ session('error') }}");
            @endif
            
            // Inicialização de todas as DataTables
            $('#tabela-protocolo, #tabela-lisura, #tabela-sire, #tabela-glosa, #tabela-arquivo, #tabela-arquivados').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
                }
            });
            
            // Função para marcar/desmarcar todos os checkboxes
            $("#check-all-protocolo").click(function() {
                $(".check-item-protocolo").prop("checked", $(this).prop("checked"));
            });
            
            $("#check-all-lisura").click(function() {
                $(".check-item-lisura").prop("checked", $(this).prop("checked"));
            });
            
            $("#check-all-sire").click(function() {
                $(".check-item-sire").prop("checked", $(this).prop("checked"));
            });
            
            $("#check-all-glosa").click(function() {
                $(".check-item-glosa").prop("checked", $(this).prop("checked"));
            });
            
            $("#check-all-arquivo").click(function() {
                $(".check-item-arquivo").prop("checked", $(this).prop("checked"));
            });
            
            $("#check-all-arquivados").click(function() {
                $(".check-item-arquivados").prop("checked", $(this).prop("checked"));
            });
            
            // Manter a aba ativa após recarregar a página
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                localStorage.setItem('ultimaTabPacotes', $(e.target).attr('href'));
            });
            
            // Verificar se há um hash na URL para ativar a aba correspondente
            var hash = window.location.hash;
            if (hash) {
                $('a[href="' + hash + '"]').tab('show');
            } else {
                // Se não há hash, usar a última aba salva
                var ultimaTab = localStorage.getItem('ultimaTabPacotes');
                if (ultimaTab) {
                    $('a[href="' + ultimaTab + '"]').tab('show');
                }
            }
            
            // Ações em lote
            $('#mover-lote-protocolo').click(function() {
                let selecionados = $('.check-item-protocolo:checked').length;
                if (selecionados > 0) {
                    alert(`Ação de mover ${selecionados} pacotes para Lisura seria executada aqui!`);
                } else {
                    alert('Selecione pelo menos um pacote para mover!');
                }
            });
            
            // Receber pacote (novo ou recurso de glosa)
            $('#receber-pacote').click(function() {
                resetReceberPacoteModal();
                $('#modalReceberPacote').modal('show');
            });

            $('#btn-novo-pacote').click(function() {
                window.location.href = '{{ route("pacotes.create") }}';
            });

            $('#btn-recurso-glosa').click(function() {
                $('#recurso-glosa-fields').removeClass('d-none');
                $('#numero_fatura_recurso').focus();
            });

            $('#btn-pesquisar-recurso').click(function() {
                var numeroFatura = $('#numero_fatura_recurso').val().trim();
                var ocsPsaId = $('#ocs_psa_id_recurso').val();

                if (!numeroFatura || !ocsPsaId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Campos obrigatorios',
                            text: 'Informe o numero da fatura e a OCS/PSA para pesquisar o recurso.'
                        });
                    } else {
                        alert('Informe o numero da fatura e a OCS/PSA para pesquisar o recurso.');
                    }
                    return;
                }

                var url = '{{ route("pesquisa.buscar") }}' +
                    '?numero_fatura=' + encodeURIComponent(numeroFatura) +
                    '&ocs_psa_id=' + encodeURIComponent(ocsPsaId);

                window.location.href = url;
            });

            function resetReceberPacoteModal() {
                $('#recurso-glosa-fields').addClass('d-none');
                $('#numero_fatura_recurso').val('');
                $('#ocs_psa_id_recurso').val('');
            }

            // Modificar a função que verifica se o pacote pode ser movido
            $('.btn-mover-pacote').click(function(e) {
                e.preventDefault();
                e.stopPropagation(); // Impedir a propagação do evento
                
                var pacoteId = $(this).data('id');
                var localizacao = $(this).data('localizacao');
                
                // Verificação especial para pacotes do SIRE
                if (localizacao === 'sire') {
                    $.ajax({
                        url: '{{ url("pacotes") }}/' + pacoteId + '/pode-mover',
                        type: 'GET',
                        dataType: 'json',
                        async: false, // Para garantir que temos o destino antes de mostrar o modal
                        success: function(response) {
                            if (response.pode_mover) {
                                // Guardar o destino recomendado pelo backend
                                var destinoBackend = response.destino_recomendado;
                                
                                // Usar a lógica completa apenas para determinar o que a modal exibe
                                var valorGlosa = response.pacote_info.valor_glosa || 0;
                                var valorPendente = response.pacote_info.valor_pendente || 0;
                                var valorRecursoGlosa = response.pacote_info.valor_recurso_glosa || 0;
                                var localizacaoAnterior = response.pacote_info.localizacao_anterior || '';
                                
                                if (valorGlosa == 0 && valorPendente == 0) {
                                    destino = 'arquivo';
                                } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'lisura' && valorPendente > 0) {
                                    destino = 'glosa';
                                } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'lisura' && valorPendente == 0) {
                                    destino = 'glosa';
                                } else if (valorGlosa > 0 && valorRecursoGlosa > 0 && localizacaoAnterior === 'glosa' && valorPendente == 0) {
                                    destino = 'arquivo';
                                } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'glosa' && valorPendente == 0) {
                                    destino = 'arquivo';
                                } else {
                                    destino = 'sire';
                                }
                            }
                        }
                    });
                } else {
                    // Para outras localizações, exibe o modal normalmente
                    configurarModal(pacoteId, localizacao);
                }
            });
            
            // Função para configurar o modal
            function configurarModal(pacoteId, localizacao, destinoForcado = '') {
                var destino = '';
                
                // Determinar próximo destino com base na localização atual
                if (destinoForcado) {
                    destino = destinoForcado;
                } else {
                    switch(localizacao) {
                        case 'protocolo':
                            destino = 'lisura';
                            break;
                        case 'lisura':
                            destino = 'sire';
                            break;
                        case 'sire':
                            // Para o SIRE, primeiro fazemos a requisição AJAX ao backend
                            $.ajax({
                                url: '{{ url("pacotes") }}/' + pacoteId + '/pode-mover',
                                type: 'GET',
                                dataType: 'json',
                                async: false, // Para garantir que temos o destino antes de mostrar o modal
                                success: function(response) {
                                    if (response.pode_mover) {
                                        // Guardar o destino recomendado pelo backend
                                        var destinoBackend = response.destino_recomendado;
                                        
                                        // Usar a lógica completa apenas para determinar o que a modal exibe
                                        var valorGlosa = response.pacote_info.valor_glosa || 0;
                                        var valorPendente = response.pacote_info.valor_pendente || 0;
                                        var valorRecursoGlosa = response.pacote_info.valor_recurso_glosa || 0;
                                        var localizacaoAnterior = response.pacote_info.localizacao_anterior || '';
                                        
                                        if (valorGlosa == 0 && valorPendente == 0) {
                                            destino = 'arquivo';
                                        } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'lisura' && valorPendente > 0) {
                                            destino = 'glosa';
                                        } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'lisura' && valorPendente == 0) {
                                            destino = 'glosa';
                                        } else if (valorGlosa > 0 && valorRecursoGlosa > 0 && localizacaoAnterior === 'glosa' && valorPendente == 0) {
                                            destino = 'arquivo';
                                        } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'glosa' && valorPendente == 0) {
                                            destino = 'arquivo';
                                        } else {
                                            destino = 'sire';
                                        }
                                    }
                                }
                            });
                            break;
                        case 'glosa':
                            destino = 'sire';
                            break;
                        case 'arquivo':
                            destino = 'arquivado';
                            break;
                        default:
                            destino = '';
                    }
                }
                
                // Atualizar o modal
                $('#localAtual').text(localizacao.charAt(0).toUpperCase() + localizacao.slice(1));
                $('#localDestino').text(destino.toUpperCase());
                $('#modalDestino').val(destino);
                $('#formMoverPacote').attr('action', '{{ url("pacotes") }}/' + pacoteId + '/mover');
                
                // Exibir o modal
                $('#modalMoverPacote').modal('show');
            }
            
            // Configurar o modal de arquivamento
            $('#modalArquivar').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget);
                var pacoteId = button.data('id');
                var localizacaoFisica = button.data('localizacao-fisica') || '';
                
                // Definir a ação do formulário
                $('#formArquivar').attr('action', '{{ url("pacotes") }}/' + pacoteId + '/arquivar');
                
                // Preencher o campo de localização física se existir
                $('#localizacao_fisica').val(localizacaoFisica);
            });

            // Envio do formulário de arquivamento via AJAX
            $('#formArquivar').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modalArquivar').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: response.message,
                                showConfirmButton: true
                            }).then((result) => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Ocorreu um erro ao processar a solicitação.'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Ocorreu um erro ao processar a solicitação.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('\n');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: errorMsg
                        });
                    }
                });
            });

            // Configurar o modal de edição de localização física
            $('#modalEditarLocalizacaoFisica').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget);
                var pacoteId = button.data('id');
                var localizacaoFisica = button.data('localizacao-fisica') || '';
                
                // Definir a ação do formulário
                $('#formEditarLocalizacaoFisica').attr('action', '{{ url("pacotes") }}/' + pacoteId + '/atualizar-localizacao-fisica');
                
                // Preencher o campo de localização física se existir
                $('#localizacao_fisica').val(localizacaoFisica);
            });

            // Envio do formulário de edição de localização física via AJAX
            $('#formEditarLocalizacaoFisica').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modalEditarLocalizacaoFisica').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: response.message,
                                showConfirmButton: true
                            }).then((result) => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Ocorreu um erro ao processar a solicitação.'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Ocorreu um erro ao processar a solicitação.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('\n');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: errorMsg
                        });
                    }
                });
            });
        });
    </script>
@stop