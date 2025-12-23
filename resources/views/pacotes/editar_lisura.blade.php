@extends('adminlte::page')

@section('title', 'Editar Pacote (Lisura)')

@section('content_header')
    <h1>
        Editar Pacote #{{ request('id', '123') }} - Lisura
        <a href="{{ route('pacotes.show', ['id' => request('id')]) }}" class="btn btn-sm btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </h1>
@stop

@section('content')
    <!-- Alerta para mensagens de debug -->
    <div id="debug-alerts" class="row mb-3">
        <div class="col-md-12">
            <div class="alert alert-info debug-message d-none">
                <h5><i class="icon fas fa-info"></i> Debug Info</h5>
                <div id="debug-content"></div>
            </div>
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Erros de validação</h5>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger">
                    <h5><i class="icon fas fa-ban"></i> Erro</h5>
                    {{ session('error') }}
                </div>
            @endif
            
            @if (session('success'))
                <div class="alert alert-success">
                    <h5><i class="icon fas fa-check"></i> Sucesso</h5>
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    <form id="form-editar-pacote-lisura" action="{{ route('pacotes.update', $pacote->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Informações de Identificação (Editáveis) -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-secondary">
                        <h3 class="card-title">Informações do Pacote</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Nº do Pacote</label>
                                    <input type="text" class="form-control" value="{{ $pacote->id }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
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
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="numero_fatura">Número da Fatura</label>
                                    <input type="text" class="form-control" id="numero_fatura" name="numero_fatura" 
                                           value="{{ $pacote->numero_fatura }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="valor_fatura">Valor Original da Fatura</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">R$</span>
                                        </div>
                                        <input type="text" class="form-control money" id="valor_fatura" 
                                               name="valor_fatura" value="{{ number_format($pacote->valor_fatura, 2, ',', '.') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Informações da Lisura -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">Informações da Lisura</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tipo_conta_id">Tipo de Conta</label>
                            <select class="form-control" id="tipo_conta_id" name="tipo_conta_id" required>
                                <option value="">Selecione...</option>
                                @foreach($tiposConta as $tipoConta)
                                    <option value="{{ $tipoConta->id }}" {{ $pacote->tipo_conta_id == $tipoConta->id ? 'selected' : '' }}>
                                        {{ $tipoConta->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_glosa">Valor da Glosa (R$)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_glosa" name="valor_glosa" 
                                       value="{{ number_format($pacote->valor_glosa, 2, ',', '.') }}">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_pos_lisura">Valor Pós Lisura (R$)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_pos_lisura" 
                                       name="valor_pos_lisura" value="{{ number_format($pacote->valor_pos_lisura, 2, ',', '.') }}" readonly>
                            </div>
                            <small class="form-text text-muted">Este valor é calculado automaticamente (Valor da Fatura - Glosa)</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title">Detalhes da Glosa</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="motivo_glosa">Motivo da Glosa</label>
                            <select class="form-control" id="motivo_glosa" name="motivo_glosa">
                                <option value="">Selecione um motivo...</option>
                                @foreach($motivosGlosa as $motivo)
                                    <option value="{{ $motivo->id }}" {{ $pacote->motivo_glosa_id == $motivo->id ? 'selected' : '' }}>
                                        {{ $motivo->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="descricao_glosa">Descrição da Glosa</label>
                            <textarea class="form-control" id="descricao_glosa" name="descricao_glosa" 
                                      rows="4">{{ $pacote->descricao_glosa ?? '' }}</textarea>
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
                        <h3 class="card-title">Observações da Lisura</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" 
                                      rows="3" placeholder="Adicione uma nova observação..."></textarea>
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
                        <a href="{{ route('pacotes.show', ['id' => request('id')]) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> Ver Detalhes
                        </a>
                        <a href="{{ route('pacotes.index') }}#lisura" class="btn btn-danger">
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
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
    <script>
        $(document).ready(function() {
            // Adicionar log quando o documento está pronto
            console.log('Documento carregado - Editar Lisura Pacote #{{ $pacote->id }}');
            
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
                console.log('Campo monetário inicializado:', $(this).attr('id'), $(this).val());
            });
            
            // Adicionar log no envio do formulário
            $('#form-editar-pacote-lisura').on('submit', function(e) {
                console.log('Formulário enviado - dados do form:', $(this).serialize());
                
                // Mostrar toast informativo
                toastr.info('Enviando formulário...');
                
                // Log dos valores principais
                console.log('OCS/PSA:', $('#ocs_psa_id').val());
                console.log('Número da Fatura:', $('#numero_fatura').val());
                console.log('Valor Fatura:', $('#valor_fatura').val());
                console.log('Tipo Conta:', $('#tipo_conta_id').val());
                console.log('Valor Glosa:', $('#valor_glosa').val());
            });
            
            // Calcular o valor pós-lisura quando o valor da glosa mudar
            $('#valor_glosa').on('keyup', function() {
                calcularValorPosLisura();
                console.log('Valor glosa alterado:', $(this).val());
            });
            
            // Função para calcular o valor pós-lisura
            function calcularValorPosLisura() {
                // Obter valor da fatura do pacote (remover 'R$' e espaços)
                var valorFatura = parseFloat('{{ $pacote->valor_fatura }}');
                
                // Obter valor da glosa
                var valorGlosa = $('#valor_glosa').maskMoney('unmasked')[0];
                
                // Calcular valor pós-lisura
                var valorPosLisura = valorFatura - valorGlosa;
                console.log('Cálculo:', valorFatura, '-', valorGlosa, '=', valorPosLisura);
                
                // Atualizar o campo
                $('#valor_pos_lisura').maskMoney('mask', valorPosLisura);
            }
        });

        // Função para mostrar informações de debug
        function showDebugInfo(message, data) {
            console.log(message, data);
            
            // Mostrar na interface
            var debugContent = $('#debug-content');
            var debugAlert = $('.debug-message');
            
            // Adicionar mensagem
            var debugMessage = '<p><strong>' + message + '</strong></p>';
            if (data) {
                debugMessage += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            }
            
            debugContent.html(debugMessage);
            debugAlert.removeClass('d-none');
        }
        
        // Verificar valores antes do envio
        $('#form-editar-pacote-lisura').on('submit', function(e) {
            // Se modo debug estiver ativado, mostrar valores
            if (window.location.search.indexOf('debug=true') !== -1) {
                e.preventDefault();
                
                var formData = $(this).serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value;
                    return obj;
                }, {});
                
                showDebugInfo('Dados do formulário (modo debug)', formData);
                
                // Confirmar envio
                if (confirm('Deseja realmente enviar o formulário?')) {
                    $(this).unbind('submit').submit();
                }
            }
        });
    </script>
@stop