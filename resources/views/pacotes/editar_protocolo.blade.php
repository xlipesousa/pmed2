@extends('adminlte::page')

@section('title', 'Editar Pacote (Protocolo)')

@section('content_header')
    <h1>
        Editar Pacote #{{ $pacote->id }} - Protocolo
        <a href="{{ route('pacotes.index') }}" class="btn btn-sm btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </h1>
@stop

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('/') }}">Início</a></li>
        <li class="breadcrumb-item"><a href="{{ route('pacotes.index') }}">Pacotes</a></li>
        <li class="breadcrumb-item active">Editar Pacote #{{ $pacote->id }}</li>
    </ol>

    <form id="form-editar-pacote" action="{{ route('pacotes.update', $pacote->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Informações básicas -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">Informações Básicas</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="ocs_psa_id">OCS/PSA</label>
                            <select class="form-control" id="ocs_psa_id" name="ocs_psa_id" required>
                                <option value="">Selecione...</option>
                                @foreach($ocsPsaList as $ocsPsa)
                                    <option value="{{ $ocsPsa->id }}" {{ $pacote->ocs_psa_id == $ocsPsa->id ? 'selected' : '' }}>
                                        {{ $ocsPsa->nome }} {{ $ocsPsa->codigo_interno ? '('.$ocsPsa->codigo_interno.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero_fatura">Número da Fatura</label>
                            <input type="text" class="form-control" id="numero_fatura" name="numero_fatura" 
                                   value="{{ $pacote->numero_fatura }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="data_entrada">Data de Entrada no Protocolo</label>
                            <div class="input-group date" id="data_entrada_div" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" id="data_entrada" 
                                       name="data_entrada" data-target="#data_entrada_div" required
                                       value="{{ \Carbon\Carbon::parse($pacote->data_entrada)->format('d/m/Y') }}">
                                <div class="input-group-append" data-target="#data_entrada_div" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Valores e tipos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title">Valores e Tipos</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="valor_fatura">Valor da Fatura (R$)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_fatura" 
                                       name="valor_fatura" value="{{ number_format($pacote->valor_fatura, 2, ',', '.') }}" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo_id">Tipo</label>
                            <select class="form-control" id="tipo_id" name="tipo_id" required>
                                <option value="">Selecione...</option>
                                @foreach($tiposPacote as $tipo)
                                    <option value="{{ $tipo->id }}" {{ $pacote->tipo_id == $tipo->id ? 'selected' : '' }}>
                                        {{ $tipo->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Observações -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title">Observações</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3" 
                                      placeholder="Informe detalhes adicionais sobre o pacote...">{{ $pacote->observacoes }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botões de ação -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-eraser"></i> Restaurar Valores
                        </button>
                        <a href="{{ route('pacotes.show', $pacote->id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> Ver Detalhes
                        </a>
                        <a href="{{ route('pacotes.index') }}" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar select2 para OCS/PSA com filtragem
            $('#ocs_psa_id').select2({
                placeholder: "Selecione ou digite para buscar...",
                allowClear: true,
                theme: "bootstrap"
            });
            
            // Inicializar datepicker com restrição para datas futuras
            $('#data_entrada_div').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br',
                maxDate: moment().endOf('day') // Limita até hoje (fim do dia atual)
            });
            
            // Inicializar máscara para valores monetários
            $('.money').maskMoney({
                prefix: '',
                thousands: '.',
                decimal: ',',
                allowZero: true
            });
            
            // Configurar os valores monetários existentes
            $('.money').each(function() {
                $(this).maskMoney('mask', $(this).val());
            });
        });
    </script>
@stop