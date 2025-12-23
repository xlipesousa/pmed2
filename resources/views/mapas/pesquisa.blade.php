@extends('adminlte::page')

@section('title', 'Pesquisa de Mapas de Pagamento')

@section('content_header')
    <h1>Pesquisa de Mapas de Pagamento</h1>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('mapas.buscar') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="numero_mapa">Número do Mapa</label>
                            <input type="text" name="numero_mapa" id="numero_mapa" class="form-control" value="{{ request('numero_mapa') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="data_criacao">Data de Criação</label>
                            <input type="date" name="data_criacao" id="data_criacao" class="form-control" value="{{ request('data_criacao') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="pacote_id">Fatura</label>
                            <select name="pacote_id" id="pacote_id" class="form-control select2">
                                <option value="">Selecione uma fatura</option>
                                @foreach($pacotes as $pacote)
                                    <option value="{{ $pacote->id }}" {{ request('pacote_id') == $pacote->id ? 'selected' : '' }}>
                                        {{ $pacote->numero_fatura }} - {{ $pacote->ocsPsa->nome ?? 'OCS/PSA não informada' }} - R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="empenho">Nº do Empenho</label>
                            <input type="text" name="empenho" id="empenho" class="form-control" value="{{ request('empenho') }}" placeholder="Digite o número do empenho">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nota_fiscal">Nota Fiscal</label>
                            <input type="text" name="nota_fiscal" id="nota_fiscal" class="form-control" value="{{ request('nota_fiscal') }}">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                        <a href="{{ route('mapas.pesquisa') }}" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($mapas))
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title">Resultados da Pesquisa</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabela-resultados" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Número do Mapa</th>
                            <th>Data de Criação</th>
                            <th>Qtd. Faturas</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mapas as $mapa)
                            <tr>
                                <td>{{ $mapa->numero_mapa }}</td>
                                <td>{{ \Carbon\Carbon::parse($mapa->data_criacao)->format('d/m/Y') }}</td>
                                <td>{{ $mapa->pacotes_count }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('mapas.show', $mapa->id) }}" class="btn btn-sm btn-info">Ver</a>
                                        @can('mapas-manage')
                                        <a href="{{ route('mapas.edit', $mapa->id) }}" class="btn btn-sm btn-primary">Editar</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Nenhum mapa de pagamento encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para pesquisa de mapas
            $('.select2-mapas').select2({
                theme: 'bootstrap4',
                placeholder: 'Pesquisar mapa...',
                allowClear: true,
                width: '100%'
            });
            
            // Inicializar Select2 para pesquisa de faturas
            $('.select2-faturas').select2({
                theme: 'bootstrap4',
                placeholder: 'Pesquisar fatura...',
                allowClear: true,
                width: '100%'
            });
            
            @if(isset($mapas))
            $('#tabela-resultados').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
                },
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]]
            });
            @endif
        });
    </script>
@stop