<?php $__env->startSection('title', 'Pesquisa de Fatura'); ?>

<?php $__env->startSection('content_header'); ?>
    <h1>Pesquisa de Fatura</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <style>
        .select2-selection__rendered {
            font-size: 13px !important;
        }
        .select2-results__option {
            font-size: 13px !important;
            padding: 8px 12px !important;
        }
        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
            line-height: 1.5 !important;
        }
        
        /* Estilo para linha de total */
        .total-row {
            background-color: #d1ecf1 !important;
            font-weight: bold !important;
            border-top: 2px solid #007bff !important;
        }
        
        .total-row td {
            border-top: 2px solid #007bff !important;
        }
        
        /* Destacar o valor total */
        .total-row td:nth-child(3) {
            color: #007bff !important;
            font-size: 1.1em !important;
            font-weight: bold !important;
        }
        
        /* Melhorar responsividade da tabela */
        #tabela-mapas-fatura th {
            white-space: nowrap;
        }
        
        #tabela-mapas-fatura td {
            vertical-align: middle;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body">
            <form action="<?php echo e(route('faturas.buscar')); ?>" method="GET">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pacote_id">Selecionar Fatura</label>
                            <select name="pacote_id" id="pacote_id" class="form-control select2-faturas">
                                <option value="">Selecione uma fatura</option>
                                <?php $__currentLoopData = $pacotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($p->id); ?>" <?php echo e(request('pacote_id') == $p->id ? 'selected' : ''); ?>>
                                        <?php echo e($p->numero_fatura); ?> - <?php echo e($p->ocsPsa->nome ?? 'OCS/PSA não informada'); ?> - R$ <?php echo e(number_format($p->valor_fatura, 2, ',', '.')); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="numero_fatura">Número da Fatura</label>
                            <input type="text" name="numero_fatura" id="numero_fatura" class="form-control" value="<?php echo e(request('numero_fatura')); ?>" placeholder="Digite o número da fatura">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="empenho">Nº do Empenho</label>
                            <input type="text" name="empenho" id="empenho" class="form-control" value="<?php echo e(request('empenho')); ?>" placeholder="Digite o número do empenho">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nota_fiscal">Nota Fiscal</label>
                            <input type="text" name="nota_fiscal" id="nota_fiscal" class="form-control" value="<?php echo e(request('nota_fiscal')); ?>" placeholder="Filtrar por nota fiscal">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Pesquisar</button>
                        <a href="<?php echo e(route('faturas.pesquisa')); ?>" class="btn btn-secondary">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if(isset($pacote) && $pacote): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Resultados da Pesquisa</h3>
        </div>
        <div class="card-body">
            <!-- Informações da Fatura -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <p><strong>Número da Fatura:</strong> <?php echo e($pacote->numero_fatura); ?></p>
                    <p><strong>OCS/PSA:</strong> <?php echo e($pacote->ocsPsa->nome ?? 'N/A'); ?></p>
                    <p><strong>Data de Entrada:</strong> <?php echo e($pacote->data_entrada->format('d/m/Y')); ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Valor Total:</strong> R$ <?php echo e(number_format($pacote->valor_fatura, 2, ',', '.')); ?></p>
                    <p><strong>Valor Pendente:</strong> R$ <?php echo e(number_format($pacote->valor_pendente, 2, ',', '.')); ?></p>
                </div>
                <div class="col-md-4">
                    <p class="text-primary"><strong>Valor Empenhado (Total):</strong> R$ <?php echo e(number_format($totalPago ?? 0, 2, ',', '.')); ?></p>
                    
                    <div class="mt-2">
                        <a href="<?php echo e(route('faturas.exportar', [$pacote->id, 'html'])); ?>" class="btn btn-sm btn-info" target="_blank">
                            <i class="fas fa-eye"></i> Ver Relatório
                        </a>
                        <a href="<?php echo e(route('faturas.exportar', [$pacote->id, 'pdf'])); ?>" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabela de Mapas -->
            <h5>Mapas de Pagamento Relacionados</h5>
            <div class="table-responsive">
                <table id="tabela-mapas-fatura" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nº do Mapa</th>
                            <th>Data de Liberação</th>
                            <th>Valor Empenhado</th>
                            <th>Nº do Empenho</th>
                            <th>Data do Empenho</th>
                            <th>Nota Fiscal</th>
                            <th>Data da Nota Fiscal</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pacote->mapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($mapa->numero_mapa); ?></td>
                                <td><?php echo e($mapa->data_criacao->format('d/m/Y')); ?></td>
                                <td>R$ <?php echo e(number_format($mapa->pivot->valor_parcial, 2, ',', '.')); ?></td>
                                <td><?php echo e($mapa->pivot->empenho ?: '-'); ?></td>
                                <td><?php echo e($mapa->pivot->data_empenho ? $mapa->pivot->data_empenho->format('d/m/Y') : '-'); ?></td>
                                <td><?php echo e($mapa->pivot->nota_fiscal ?: '-'); ?></td>
                                <td><?php echo e($mapa->pivot->data_nota_fiscal ? $mapa->pivot->data_nota_fiscal->format('d/m/Y') : '-'); ?></td>
                                <td>
                                    <a href="<?php echo e(route('mapas.show', $mapa->id)); ?>" class="btn btn-sm btn-info">Ver Mapa</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center">Esta fatura não está em nenhum mapa de pagamento.</td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if($pacote->mapas->count() > 0): ?>
                            <tr class="total-row table-info">
                                <td colspan="2" style="text-align: right; font-weight: bold;">Total Geral:</td>
                                <td style="font-weight: bold; color: #007bff; font-size: 1.1em;">R$ <?php echo e(number_format($totalPago ?? 0, 2, ',', '.')); ?></td>
                                <td colspan="5"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2
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

    // Inicializar DataTable se houver resultados
    <?php if(isset($pacote) && $pacote && $pacote->mapas->count() > 0): ?>
    $('#tabela-mapas-fatura').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
        },
        order: [[1, 'desc']], // Ordenar por data de liberação
        columnDefs: [
            {
                targets: [2], // Coluna Valor Empenhado
                className: 'text-right'
            },
            {
                targets: [4, 6], // Colunas Data do Empenho e Data da Nota Fiscal
                className: 'text-center'
            },
            {
                targets: [7], // Coluna Ações
                orderable: false,
                className: 'text-center'
            }
        ],
        drawCallback: function(settings) {
            // Destacar a linha de total após cada redraw
            $(this.api().table().body()).find('tr.total-row').css({
                'background-color': '#d1ecf1',
                'font-weight': 'bold',
                'border-top': '2px solid #007bff'
            });
            
            // Destacar especificamente o valor total
            $(this.api().table().body()).find('tr.total-row td:nth-child(3)').css({
                'color': '#007bff',
                'font-size': '1.1em',
                'font-weight': 'bold'
            });
        }
    });
    <?php endif; ?>
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/admin21ct/pmed2/resources/views/mapas/pesquisa-fatura.blade.php ENDPATH**/ ?>