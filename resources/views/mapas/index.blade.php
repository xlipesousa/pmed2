@extends('adminlte::page')

@section('title', 'Mapas de Pagamento')

@section('content_header')
    <h1>Mapas de Pagamento</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                @can('mapas-manage')
                <a href="{{ route('mapas.create') }}" class="btn btn-primary">Novo Mapa</a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table id="tabela-mapas" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Número do Mapa</th>
                            <th>Data de Liberação</th>
                            <th>Total de Faturas</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mapas as $mapa)
                            <tr>
                                <td>{{ $mapa->numero_mapa }}</td>
                                <td>{{ \Carbon\Carbon::parse($mapa->data_criacao)->format('d/m/Y') }}</td>
                                <td>{{ $mapa->pacotes_count }}</td>
                                <td>R$ {{ number_format($mapa->valorTotal, 2, ',', '.') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('mapas.show', $mapa->id) }}" class="btn btn-sm btn-info">Ver</a>
                                        @can('mapas-manage')
                                        <a href="{{ route('mapas.edit', $mapa->id) }}" class="btn btn-sm btn-primary">Editar</a>
                                        <form action="{{ route('mapas.destroy', $mapa->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este mapa?');" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Nenhum mapa de pagamento encontrado.</td>
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
            $('#tabela-mapas').DataTable({
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