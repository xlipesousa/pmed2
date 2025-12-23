@extends('adminlte::page')

@section('title', 'Detalhes do Mapa de Pagamento')

@section('content_header')
    <h1>Detalhes do Mapa de Pagamento</h1>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informações do Mapa</h3>
            <div class="float-right">
                <div class="btn-group mr-2">
                    <a href="{{ route('mapas.exportar', [$mapa->id, 'html']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-alt"></i> Exportar HTML
                    </a>
                    <a href="{{ route('mapas.exportar', [$mapa->id, 'pdf']) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
                @can('mapas-manage')
                <a href="{{ route('mapas.edit', $mapa->id) }}" class="btn btn-sm btn-primary">Editar Mapa</a>
                @endcan
                <a href="{{ route('mapas.index') }}" class="btn btn-sm btn-secondary">Voltar para Lista</a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número do Mapa:</strong> {{ $mapa->numero_mapa }}</p>
                    <p><strong>Data de Liberação:</strong> {{ $mapa->data_criacao->format('d/m/Y') }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total de Faturas:</strong> {{ $mapa->pacotes->count() }}</p>
                    <p><strong>Valor Total:</strong> R$ {{ number_format($mapa->valorTotal, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>
    
    @can('mapas-manage')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Adicionar Fatura ao Mapa</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('mapas.adicionar-fatura', $mapa->id) }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pacote_id">Selecione uma fatura</label>
                            <select name="pacote_id" id="pacote_id" class="form-control select2-faturas @error('pacote_id') is-invalid @enderror" required>
                                <option value="">Selecione...</option>
                                @foreach($pacotes as $pacote)
                                    <option value="{{ $pacote->id }}" {{ old('pacote_id') == $pacote->id ? 'selected' : '' }}>
                                        {{ $pacote->numero_fatura }} - {{ $pacote->ocsPsa->nome ?? 'OCS/PSA não informada' }} - R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }} - Implantado: R$ {{ number_format($pacote->valor_pendente, 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pacote_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_parcial">Valor Empenhado</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="number" step="0.01" name="valor_parcial" id="valor_parcial" class="form-control @error('valor_parcial') is-invalid @enderror" placeholder="0,00" required>
                                @error('valor_parcial')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="empenho">Nº do Empenho</label>
                            <input type="text" name="empenho" id="empenho" class="form-control @error('empenho') is-invalid @enderror">
                            @error('empenho')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="data_empenho">Data do Empenho</label>
                            <input type="date" name="data_empenho" id="data_empenho" class="form-control @error('data_empenho') is-invalid @enderror">
                            @error('data_empenho')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nota_fiscal">Nota Fiscal</label>
                            <input type="text" name="nota_fiscal" id="nota_fiscal" class="form-control @error('nota_fiscal') is-invalid @enderror">
                            @error('nota_fiscal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="data_nota_fiscal">Data da Nota Fiscal</label>
                            <input type="date" name="data_nota_fiscal" id="data_nota_fiscal" class="form-control @error('data_nota_fiscal') is-invalid @enderror">
                            @error('data_nota_fiscal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Adicionar Fatura</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Faturas no Mapa</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabela-faturas" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nº da Fatura</th>
                            <th>Valor Implantado</th>
                            <th>Valor Empenhado</th>
                            <th>Nº do Empenho</th>
                            <th>Nota Fiscal</th>
                            @can('mapas-manage')
                            <th>Ações</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mapa->pacotes as $pacote)
                            <tr>
                                <td>{{ $pacote->numero_fatura }}</td>
                                <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                                <td>R$ {{ number_format($pacote->pivot->valor_parcial, 2, ',', '.') }}</td>
                                <td>{{ $pacote->pivot->empenho ?: '-' }}</td>
                                <td>{{ $pacote->pivot->nota_fiscal ?: '-' }}</td>
                                @can('mapas-manage')
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('mapas.editar-fatura', [$mapa->id, $pacote->id]) }}" class="btn btn-sm btn-primary">Editar</a>
                                        <form action="{{ route('mapas.remover-fatura', [$mapa->id, $pacote->id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta fatura do mapa?');" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                                        </form>
                                    </div>
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ Auth::user()->can('mapas-manage') ? '6' : '5' }}" class="text-center">Nenhuma fatura adicionada a este mapa.</td>
                            </tr>
                        @endforelse
                        
                        @if($mapa->pacotes->count() > 0)
                            <tr>
                                <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                                <td><strong>R$ {{ number_format($totalPago, 2, ',', '.') }}</strong></td>
                                <td colspan="{{ Auth::user()->can('mapas-manage') ? '3' : '2' }}"></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para pesquisa de faturas
            $('.select2-faturas').select2({
                theme: 'bootstrap4',
                placeholder: 'Digite para pesquisar por número da fatura ou OCS/PSA...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Nenhuma fatura encontrada";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
            
            // Inicializar DataTable
            $('#tabela-faturas').DataTable({
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