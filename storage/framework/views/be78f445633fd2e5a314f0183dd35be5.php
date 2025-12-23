<?php $__env->startSection('title', 'Pesquisa Avançada'); ?>

<?php $__env->startSection('content_header'); ?>
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-search"></i> Pesquisa Avançada</h1>
        <div>
            <!-- Adicionar dropdown para pesquisas salvas -->
            <div class="btn-group mr-2">
                <button id="btn-pesquisas-salvas" type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <i class="fas fa-bookmark"></i> Pesquisas Salvas
                </button>
                <div class="dropdown-menu dropdown-menu-right" id="dropdown-pesquisas-salvas">
                    <div class="dropdown-header text-center">Carregando pesquisas...</div>
                </div>
            </div>

            <!-- Botões existentes -->
            <div class="btn-group ml-2">
                <button type="button" class="btn btn-success" id="btn-exportacao-direta">
                    <i class="fas fa-file-export"></i> Exportar
                </button>
                <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right" id="exportar-dropdown-menu">
                    <h6 class="dropdown-header">Escolher formato</h6>
                    <a href="#" class="dropdown-item exportar-formato" id="exportar-excel">
                        <i class="fas fa-file-excel text-success"></i> Exportar Excel
                    </a>
                    <a href="#" class="dropdown-item exportar-formato" id="exportar-csv">
                        <i class="fas fa-file-csv text-info"></i> Exportar CSV
                    </a>
                    <a href="#" class="dropdown-item exportar-formato" id="exportar-pdf">
                        <i class="fas fa-file-pdf text-danger"></i> Exportar PDF
                    </a>
                    <a href="#" class="dropdown-item exportar-formato" id="exportar-html">
                        <i class="fas fa-file-code text-primary"></i> Exportar HTML
                    </a>
                </div>
            </div>
            <button id="btn-salvar-pesquisa" type="button" class="btn btn-primary">
                <i class="fas fa-save"></i> Salvar Pesquisa
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtros de Pesquisa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form id="form-pesquisa" method="GET" action="<?php echo e(route('pesquisa.buscar')); ?>">
                    <!-- Filtros Básicos -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Número do Pacote</label>
                                <input type="text" class="form-control" name="numero_pacote" value="<?php echo e(request('numero_pacote')); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Número da Fatura</label>
                                <input type="text" class="form-control" name="numero_fatura" value="<?php echo e(request('numero_fatura')); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>OCS/PSA</label>
                                <select class="form-control select2" name="ocs_psa_id">
                                    <option value="">Selecione...</option>
                                    <?php $__currentLoopData = $ocsPsaList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" <?php echo e(request('ocs_psa_id') == $id ? 'selected' : ''); ?>><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo do Pacote</label>
                                <select class="form-control select2" name="tipo_id">
                                    <option value="">Selecione...</option>
                                    <?php $__currentLoopData = $tiposPacote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" <?php echo e(request('tipo_id') == $id ? 'selected' : ''); ?>><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtros de Estado e Localização -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Estado Geral</label>
                                <select class="form-control select2" name="estado_geral">
                                    <option value="">Selecione...</option>
                                    <?php $__currentLoopData = $estadosGerais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($estado); ?>" <?php echo e(request('estado_geral') == $estado ? 'selected' : ''); ?>><?php echo e($estado); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Estado da Glosa</label>
                                <select class="form-control select2" name="estado_glosa">
                                    <option value="">Selecione...</option>
                                    <?php $__currentLoopData = $estadosGlosa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($estado); ?>" <?php echo e(request('estado_glosa') == $estado ? 'selected' : ''); ?>><?php echo e($estado); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Localização Atual</label>
                                <select class="form-control select2" name="localizacao_atual">
                                    <option value="">Selecione...</option>
                                    <?php $__currentLoopData = $localizacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $localizacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($localizacao); ?>" <?php echo e(request('localizacao_atual') == $localizacao ? 'selected' : ''); ?>><?php echo e($localizacao); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tipo de Conta</label>
                                <select class="form-control select2" name="tipo_conta_id">
                                    <option value="">Selecione...</option>
                                    <?php $__currentLoopData = $tiposConta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" <?php echo e(request('tipo_conta_id') == $id ? 'selected' : ''); ?>><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtros de Valor -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Valor da Fatura (Min.)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" name="valor_fatura_min" value="<?php echo e(request('valor_fatura_min')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Valor da Fatura (Máx.)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" name="valor_fatura_max" value="<?php echo e(request('valor_fatura_max')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Valor da Glosa (Min.)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" name="valor_glosa_min" value="<?php echo e(request('valor_glosa_min')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Valor da Glosa (Máx.)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" name="valor_glosa_max" value="<?php echo e(request('valor_glosa_max')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtros de Data -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Período de Entrada</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="periodo_entrada" name="periodo_entrada" value="<?php echo e(request('periodo_entrada')); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Motivo da Glosa</label>
                                <select class="form-control select2" name="motivo_glosa_id">
                                    <option value="">Selecione...</option>
                                    <?php $__currentLoopData = $motivosGlosa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($id); ?>" <?php echo e(request('motivo_glosa_id') == $id ? 'selected' : ''); ?>><?php echo e($nome); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Termo de Busca (pesquisa geral)</label>
                                <input type="text" class="form-control" name="termo_busca" value="<?php echo e(request('termo_busca')); ?>" placeholder="Digite um termo para pesquisa...">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botões de Ação -->
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Pesquisar
                                </button>
                                <button type="reset" class="btn btn-secondary" id="btn-limpar">
                                    <i class="fas fa-eraser"></i> Limpar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php if(isset($resultados)): ?>
    <div class="col-md-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-search-plus"></i> Resultados da Pesquisa</h3>
                <div class="card-tools">
                    <span class="badge badge-info"><?php echo e($resultados->total()); ?> resultados encontrados</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>OCS/PSA</th>
                                <th>Nº Fatura</th>
                                <th>Data Entrada</th>
                                <th>Valor Fatura</th>
                                <th>Valor Glosa</th>
                                <th>Valor Implantado</th>
                                <th>Valor Pendente</th>
                                <th>Localização</th>
                                <th>Estado Geral</th>
                                <th>Estado Glosa</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($resultados->count() > 0): ?>
                                <?php 
                                    $totalFatura = 0; 
                                    $totalGlosa = 0;
                                    $totalImplantado = 0;
                                    $totalPendente = 0;
                                ?>

                                <?php $__currentLoopData = $resultados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pacote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($pacote->id); ?></td>
                                    <td><?php echo e($pacote->ocsPsa->nome ?? 'N/A'); ?></td>
                                    <td><?php echo e($pacote->numero_fatura); ?></td>
                                    <td><?php echo e($pacote->data_entrada ? $pacote->data_entrada->format('d/m/Y') : 'N/A'); ?></td>
                                    <td>R$ <?php echo e(number_format($pacote->valor_fatura, 2, ',', '.')); ?></td>
                                    <td>R$ <?php echo e(number_format($pacote->valor_glosa, 2, ',', '.')); ?></td>
                                    <td>R$ <?php echo e(number_format($pacote->valor_pago, 2, ',', '.')); ?></td>
                                    <td>R$ <?php echo e(number_format($pacote->valor_pendente, 2, ',', '.')); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($pacote->localizacao_atual == 'protocolo' ? 'primary' :
                                            ($pacote->localizacao_atual == 'lisura' ? 'success' :
                                            ($pacote->localizacao_atual == 'sire' ? 'info' :
                                            ($pacote->localizacao_atual == 'glosa' ? 'warning' :
                                            ($pacote->localizacao_atual == 'arquivo' ? 'secondary' : 'dark'))))); ?>">
                                            <?php echo e(ucfirst($pacote->localizacao_atual)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo e($pacote->estado_geral == 'Normal' ? 'success' : ($pacote->estado_geral == 'Arquivado' ? 'secondary' : 'warning')); ?>">
                                            <?php echo e($pacote->estado_geral); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo e($pacote->estado_glosa == 'Glosa não identificada' ? 'light' :
                                            ($pacote->estado_glosa == 'Glosa identificada' ? 'warning' :
                                            ($pacote->estado_glosa == 'Existência de Glosa Notificada' ? 'warning' :
                                            ($pacote->estado_glosa == 'Recurso não recebido' ? 'danger' :
                                            ($pacote->estado_glosa == 'Recurso recebido' ? 'info' :
                                            ($pacote->estado_glosa == 'Recurso indeferido' ? 'danger' :
                                            ($pacote->estado_glosa == 'Recurso deferido' ? 'success' : 'secondary'))))))); ?>">
                                            <?php echo e($pacote->estado_glosa); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('pacotes.show', $pacote->id)); ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    $totalFatura += $pacote->valor_fatura; 
                                    $totalGlosa += $pacote->valor_glosa;
                                    $totalImplantado += $pacote->valor_pago;
                                    $totalPendente += $pacote->valor_pendente;
                                ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <tr class="bg-dark text-white font-weight-bold">
                                    <td colspan="4" class="text-right">TOTAL:</td>
                                    <td>R$ <?php echo e(number_format($totalFatura, 2, ',', '.')); ?></td>
                                    <td>R$ <?php echo e(number_format($totalGlosa, 2, ',', '.')); ?></td>
                                    <td>R$ <?php echo e(number_format($totalImplantado, 2, ',', '.')); ?></td>
                                    <td>R$ <?php echo e(number_format($totalPendente, 2, ',', '.')); ?></td>
                                    <td colspan="4"></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td colspan="12" class="text-center">Nenhum resultado encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <?php echo e($resultados->links()); ?>

                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Modal para Salvar Pesquisa -->
    <div class="modal fade" id="modal-salvar-pesquisa" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Salvar Pesquisa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo e(route('pesquisa.salvar')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nome_pesquisa">Nome da Pesquisa</label>
                            <input type="text" class="form-control" id="nome_pesquisa" name="nome_pesquisa" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                        </div>
                        <input type="hidden" id="filtros_json" name="filtros_json" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<style>
    .select2-container--bootstrap4.select2-container--focus .select2-selection {
        box-shadow: none;
    }
    .badge {
        font-size: 90%;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .pagination {
        justify-content: center;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://cdn.jsdelivr.net/npm/moment/min/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/inputmask@5.0.6/dist/jquery.inputmask.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
<script>
// Solução para o botão de exportação
$(document).ready(function() {
    console.log('Inicializando página de pesquisa...');
    
    // Função para exportar com URL direta
    function exportarResultados(formato) {
        console.log('Função exportarResultados chamada com formato: ' + formato);
        
        // Construir URL
        var baseUrl = "<?php echo e(url('/pesquisa/exportar')); ?>/" + formato;
        var queryString = window.location.search;
        var fullUrl = baseUrl + queryString;
        
        console.log('URL de exportação: ' + fullUrl);
        
        // Abrir em nova janela para evitar interrupções na página atual
        window.open(fullUrl, '_blank');
    }
    
    // SOLUÇÃO: Botão de exportação rápida
    $('#btn-exportacao-direta').on('click', function(e) {
        console.log('Botão de exportação direta clicado');
        e.preventDefault();
        // Exportar Excel por padrão no botão principal
        exportarResultados('excel');
    });
    
    // Formato específico - links no dropdown
    $('.exportar-formato').on('click', function(e) {
        console.log('Link de formato clicado');
        e.preventDefault();
        
        // Obter formato do ID
        var id = $(this).attr('id');
        var formato = id.replace('exportar-', '');
        
        console.log('Formato selecionado: ' + formato);
        exportarResultados(formato);
    });
    
    // Inicialização do DateRangePicker
    $('#periodo_entrada').daterangepicker({
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'De',
            toLabel: 'Até',
            customRangeLabel: 'Personalizado',
            daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
            firstDay: 1
        },
        autoUpdateInput: false
    });
    
    $('#periodo_entrada').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });
    
    $('#periodo_entrada').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
    
    // Inicialização do Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: "Selecione uma opção",
        allowClear: true
    });
    
    // Inicialização de máscaras para campos numéricos
    $('.money').maskMoney({
        prefix: '',
        thousands: '.',
        decimal: ',',
        allowZero: true,
        allowNegative: false
    });
    
    // Botão Limpar filtros
    $('#btn-limpar').click(function(e) {
        e.preventDefault();
        window.location.href = "<?php echo e(route('pesquisa.index')); ?>";
    });
    
    // Abrir modal para salvar pesquisa
    $('#btn-salvar-pesquisa').on('click', function() {
        // Serializar os filtros do formulário
        var filtrosJson = $('#form-pesquisa').serialize();
        $('#filtros_json').val(filtrosJson);
        
        $('#modal-salvar-pesquisa').modal('show');
    });

    // Carregar pesquisas salvas quando o dropdown for aberto
    $('#btn-pesquisas-salvas').on('click', function() {
        carregarPesquisasSalvas();
    });

    function carregarPesquisasSalvas() {
        $.ajax({
            url: "<?php echo e(route('pesquisa.listar')); ?>",
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                var dropdown = $('#dropdown-pesquisas-salvas');
                dropdown.empty();
                
                if (response.length > 0) {
                    response.forEach(function(pesquisa) {
                        dropdown.append(
                            '<a class="dropdown-item" href="<?php echo e(url("pesquisa/carregar")); ?>/' + pesquisa.id + '">' +
                            '<div class="d-flex justify-content-between align-items-center">' +
                            '<span>' + pesquisa.nome + '</span>' +
                            '<button type="button" class="btn btn-xs btn-danger excluir-pesquisa" ' +
                            'data-id="' + pesquisa.id + '" data-toggle="tooltip" title="Excluir">' +
                            '<i class="fas fa-trash"></i></button>' +
                            '</div>' +
                            (pesquisa.descricao ? '<small class="text-muted d-block">' + pesquisa.descricao + '</small>' : '') +
                            '</a>'
                        );
                    });
                } else {
                    dropdown.append('<div class="dropdown-header">Nenhuma pesquisa salva</div>');
                }
                
                // Adicionar opção para gerenciar todas as pesquisas
                dropdown.append('<div class="dropdown-divider"></div>');
                dropdown.append('<a class="dropdown-item text-center" href="<?php echo e(route("pesquisa.gerenciar")); ?>">' +
                               '<i class="fas fa-cog"></i> Gerenciar Pesquisas</a>');
                
                // Handler para exclusão
                $('.excluir-pesquisa').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var pesquisaId = $(this).data('id');
                    if (confirm('Tem certeza que deseja excluir esta pesquisa salva?')) {
                        excluirPesquisaSalva(pesquisaId);
                    }
                });
            },
            error: function() {
                $('#dropdown-pesquisas-salvas').html(
                    '<div class="dropdown-header text-danger">Erro ao carregar pesquisas</div>'
                );
            }
        });
    }

    function excluirPesquisaSalva(id) {
        $.ajax({
            url: "<?php echo e(url('pesquisa/excluir')); ?>/" + id,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                toastr.success('Pesquisa excluída com sucesso');
                carregarPesquisasSalvas();
            },
            error: function() {
                toastr.error('Erro ao excluir a pesquisa');
            }
        });
    }
    
    console.log('Inicialização da página concluída.');
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/admin21ct/pmed2/resources/views/pesquisa/index.blade.php ENDPATH**/ ?>