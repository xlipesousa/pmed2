@extends('adminlte::page')

@section('title', 'Visualizar Fatura')

@section('content_header')
    <h1>Mapas de Pagamento para a Fatura</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informações da Fatura</h3>
            <div class="float-right">
                <a href="{{ route('mapas.pesquisa') }}" class="btn btn-sm btn-secondary">Voltar para Pesquisa</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número da Fatura:</strong> {{ $pacote->numero_fatura }}</p>
                    <p><strong>Data de Entrada:</strong> {{ $pacote->data_entrada->format('d/m/Y') }}</p>
                    <p><strong>OCS/PSA:</strong> {{ $pacote->ocsPsa->nome ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Valor Total:</strong> R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</p>
                    <p><strong>Valor Pago:</strong> R$ {{ number_format($pacote->valor_pago, 2, ',', '.') }}</p>
                    <p><strong>Valor Implantado:</strong> R$ {{ number_format($pacote->valor_pendente, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Mapas de Pagamento</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabela-mapas-fatura" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Número do Mapa</th>
                            <th>Data de Liberação</th>
                            <th>Valor Empenhado</th>
                            <th>Nº do Empenho</th>
                            <th>Nota Fiscal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pacote->mapas as $mapa)
                            <tr>
                                <td>{{ $mapa->numero_mapa }}</td>
                                <td>{{ $mapa->data_criacao->format('d/m/Y') }}</td>
                                <td>R$ {{ number_format($mapa->pivot->valor_parcial, 2, ',', '.') }}</td>
                                <td>{{ $mapa->pivot->empenho ?: '-' }}</td>
                                <td>{{ $mapa->pivot->nota_fiscal ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('mapas.show', $mapa->id) }}" class="btn btn-sm btn-info">Ver Mapa</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Esta fatura não está em nenhum mapa de pagamento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#tabela-mapas-fatura').DataTable({
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
        });
    </script>
@stop