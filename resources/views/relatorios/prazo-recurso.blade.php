@extends('adminlte::page')

@section('title', 'Prazo de Recurso de Glosa')

@section('content_header')
    <h1>
        <i class="far fa-clock"></i> Prazo de Recurso de Glosa
        <small>Pacotes aguardando recurso há mais de {{ $dias }} dias</small>
    </h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Este relatório é <strong>aviso, não ação</strong> — nenhum pacote é movido
            automaticamente. Cada linha permite registrar manualmente o "recurso não
            recebido", quando confirmado que a OCS/PSA não apresentou recurso dentro do
            prazo.
        </div>
    </div>

    <div class="col-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ $pacotes->count() }} pacote(s) com prazo vencido
                </h3>
                <div class="card-tools">
                    <form action="{{ route('relatorios.prazo-recurso') }}" method="GET" class="form-inline">
                        <label for="dias" class="mr-2">Prazo (dias):</label>
                        <input type="number" name="dias" id="dias" min="1" value="{{ $dias }}"
                               class="form-control form-control-sm mr-2" style="width: 80px;">
                        <button type="submit" class="btn btn-sm btn-default">Filtrar</button>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pacote</th>
                            <th>Fatura</th>
                            <th>OCS/PSA</th>
                            <th>Ofício retirado em</th>
                            <th>Dias decorridos</th>
                            <th class="text-right">Valor da glosa</th>
                            <th class="text-right">Valor pendente</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pacotes as $pacote)
                        <tr>
                            <td><a href="{{ route('pacotes.show', $pacote->id) }}">#{{ $pacote->id }}</a></td>
                            <td>{{ $pacote->numero_fatura }}</td>
                            <td>{{ $pacote->ocsPsa->nome ?? 'N/A' }}</td>
                            <td>{{ $pacote->data_retirada_oficio->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge badge-danger">
                                    {{ $pacote->diasDesdeRetiradaOficio() }} dias
                                </span>
                            </td>
                            <td class="text-right">R$ {{ number_format($pacote->valor_glosa, 2, ',', '.') }}</td>
                            <td class="text-right">R$ {{ number_format($pacote->valor_pendente, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'glosa')
                                <button type="button" class="btn btn-xs btn-danger"
                                        data-toggle="modal" data-target="#modalRecursoNaoRecebido{{ $pacote->id }}">
                                    <i class="fas fa-times-circle"></i> Recurso não recebido
                                </button>
                                @endif
                            </td>
                        </tr>
                        @if(Auth::user()->role == 'admin' || Auth::user()->role == 'glosa')
                        <div class="modal fade" id="modalRecursoNaoRecebido{{ $pacote->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirmar Recurso Não Recebido — Pacote #{{ $pacote->id }}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('pacotes.recurso-nao-recebido', $pacote->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <p>
                                                O Ofício de Glosa foi retirado em
                                                <strong>{{ $pacote->data_retirada_oficio->format('d/m/Y') }}</strong>
                                                ({{ $pacote->diasDesdeRetiradaOficio() }} dias atrás).
                                            </p>
                                            <p>Confirmar que o recurso <strong>não foi recebido</strong> dentro do prazo?</p>
                                            <ul>
                                                <li>Altera o estado da glosa para "Recurso não recebido"</li>
                                                @if($pacote->valor_pendente > 0)
                                                    <li>Move o pacote para o setor SIRE (há valor pendente)</li>
                                                @else
                                                    <li>Move o pacote para o Arquivo</li>
                                                @endif
                                            </ul>
                                            <div class="form-group">
                                                <label for="observacao{{ $pacote->id }}">Observação (opcional):</label>
                                                <textarea class="form-control" id="observacao{{ $pacote->id }}"
                                                          name="observacao" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger">Confirmar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-check-circle text-success"></i>
                                Nenhum pacote com prazo de recurso vencido.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop
