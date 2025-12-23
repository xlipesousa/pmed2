<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>
    <h1>
        <i class="fas fa-tachometer-alt"></i> Dashboard PMED 2.0
        <small>Visão Geral do Sistema</small>
    </h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <!-- Cards de contagem de pacotes por localização -->
    <div class="row">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3><?php echo e(App\Models\Pacote::where('localizacao_atual', 'Protocolo')->count()); ?></h3>
                    <p>Protocolo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <a href="<?php echo e(route('pacotes.index')); ?>?aba=protocolo#protocolo" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?php echo e(App\Models\Pacote::where('localizacao_atual', 'Lisura')->count()); ?></h3>
                    <p>Lisura</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <a href="<?php echo e(route('pacotes.index')); ?>?aba=lisura#lisura" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><?php echo e(App\Models\Pacote::where('localizacao_atual', 'SIRE')->count()); ?></h3>
                    <p>SIRE</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-alt"></i>
                </div>
                <a href="<?php echo e(route('pacotes.index')); ?>?aba=sire#sire" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><?php echo e(App\Models\Pacote::where('localizacao_atual', 'Glosa')->count()); ?></h3>
                    <p>Glosa</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="<?php echo e(route('pacotes.index')); ?>?aba=glosa#glosa" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?php echo e(App\Models\Pacote::where('localizacao_atual', 'Arquivo')->count()); ?></h3>
                    <p>Arquivo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-archive"></i>
                </div>
                <a href="<?php echo e(route('pacotes.index')); ?>?aba=arquivo#arquivo" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3><?php echo e(App\Models\Pacote::where('localizacao_atual', 'arquivado')->count()); ?></h3>
                    <p>Arquivados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-archive"></i>
                </div>
                <a href="<?php echo e(route('pacotes.index')); ?>?aba=arquivado#arquivado" class="small-box-footer">
                    Ver detalhes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Métricas financeiras -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor Total de Faturas</span>
                    <span class="info-box-number">R$ <?php echo e(number_format(App\Models\Pacote::sum('valor_fatura'), 2, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor Implantado</span>
                    <span class="info-box-number">R$ <?php echo e(number_format(App\Models\Pacote::sum('valor_pago'), 2, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor em Glosa</span>
                    <span class="info-box-number">R$ <?php echo e(number_format(App\Models\Pacote::sum('valor_glosa'), 2, ',', '.')); ?></span>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-6">
            <div class="info-box bg-gradient-danger">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor Pendente</span>
                    <span class="info-box-number">R$ <?php echo e(number_format(App\Models\Pacote::whereRaw('valor_fatura > IFNULL(valor_pago, 0) + IFNULL(valor_glosa, 0)')->sum(DB::raw('valor_fatura - IFNULL(valor_pago, 0) - IFNULL(valor_glosa, 0)')), 2, ',', '.')); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Gráfico de distribuição de pacotes -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Distribuição de Pacotes por Status</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="graficoDistribuicao" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de valores financeiros -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Valores por Etapa (R$)</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="graficoValores" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Alertas de pacotes críticos -->
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Alertas de Pacotes</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>OCS/PSA</th>
                                    <th>Status</th>
                                    <th>Alerta</th>
                                    <th>Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $pacotesAlerta = App\Models\Pacote::whereNotNull('data_limite_retirada')
                                        ->whereRaw('data_limite_retirada >= CURDATE()')
                                        ->whereRaw('DATEDIFF(data_limite_retirada, CURDATE()) <= 5')
                                        ->with('ocsPsa')
                                        ->limit(5)
                                        ->get();
                                ?>
                                
                                <?php $__empty_1 = true; $__currentLoopData = $pacotesAlerta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pacote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><a href="<?php echo e(route('pacotes.show', $pacote->id)); ?>"><?php echo e($pacote->id); ?></a></td>
                                    <td><?php echo e($pacote->ocsPsa->nome ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-warning"><?php echo e($pacote->localizacao_atual); ?></span>
                                    </td>
                                    <td>
                                        <small class="badge badge-danger">
                                            <i class="far fa-clock"></i> 
                                            <?php echo e((int)$pacote->diasRetiradaRestantes()); ?> dias restantes
                                        </small>
                                    </td>
                                    <td>
                                        <a href="<?php echo e(route('pacotes.prazos', $pacote->id)); ?>" class="btn btn-xs btn-info">
                                            <i class="fas fa-eye"></i> Ver prazos
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center">Não há pacotes com alertas de prazo.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="<?php echo e(route('relatorios.glosas')); ?>" class="btn btn-sm btn-danger">
                        Ver todos os alertas <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Top OCS/PSA -->
        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Top 5 OCS/PSA por Volume</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th>OCS/PSA</th>
                                    <th>Qtde. Pacotes</th>
                                    <th>Valor Total</th>
                                    <th>Taxa de Glosa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $topOcsPsa = DB::table('pacotes')
                                        ->join('ocs_psa', 'pacotes.ocs_psa_id', '=', 'ocs_psa.id')
                                        ->select('ocs_psa.id', 'ocs_psa.nome', 
                                                DB::raw('COUNT(*) as total_pacotes'),
                                                DB::raw('SUM(pacotes.valor_fatura) as valor_total'),
                                                DB::raw('SUM(pacotes.valor_glosa) as valor_glosa'))
                                        ->groupBy('ocs_psa.id', 'ocs_psa.nome')
                                        ->orderBy('total_pacotes', 'desc')
                                        ->limit(5)
                                        ->get();
                                ?>
                                
                                <?php $__empty_1 = true; $__currentLoopData = $topOcsPsa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ocs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($ocs->nome); ?></td>
                                    <td><?php echo e($ocs->total_pacotes); ?></td>
                                    <td>R$ <?php echo e(number_format($ocs->valor_total, 2, ',', '.')); ?></td>
                                    <td>
                                        <?php 
                                            $taxaGlosa = $ocs->valor_total > 0 ? 
                                                ($ocs->valor_glosa / $ocs->valor_total) * 100 : 0;
                                        ?>
                                        <?php echo e(number_format($taxaGlosa, 1)); ?>%
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Não há dados disponíveis.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="<?php echo e(route('relatorios.ocspsa')); ?>" class="btn btn-sm btn-info">
                        Ver todos os prestadores <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Atalhos rápidos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Acesso Rápido</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <a href="<?php echo e(route('pacotes.create')); ?>" class="btn btn-app bg-primary" style="width: 100%;">
                                <i class="fas fa-plus-circle"></i> Novo Pacote
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="<?php echo e(route('relatorios.status-pacotes')); ?>" class="btn btn-app bg-info" style="width: 100%;">
                                <i class="fas fa-chart-pie"></i> Status de Pacotes
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="<?php echo e(route('relatorios.financeiro')); ?>" class="btn btn-app bg-success" style="width: 100%;">
                                <i class="fas fa-dollar-sign"></i> Relatório Financeiro
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <a href="<?php echo e(route('relatorios.glosas')); ?>" class="btn btn-app bg-warning" style="width: 100%;">
                                <i class="fas fa-exclamation-triangle"></i> Gestão de Glosas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .small-box {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .btn-app {
            height: 80px;
            min-width: 100%;
            font-size: 1rem;
            padding: 15px 5px;
        }
        .btn-app i {
            font-size: 24px;
            margin-bottom: 8px;
        }
        .info-box {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .badge {
            font-size: 85%;
            font-weight: 500;
        }
        .card-danger .table th {
            border-top: 0;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            // Dados para o gráfico de distribuição
            const localizacoes = ['Protocolo', 'Lisura', 'SIRE', 'Glosa', 'Arquivo', 'Arquivados'];
            const counts = [
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'Protocolo')->count()); ?>,
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'Lisura')->count()); ?>,
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'SIRE')->count()); ?>,
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'Glosa')->count()); ?>,
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'Arquivo')->count()); ?>,
                <?php echo e(App\Models\Pacote::onlyTrashed()->count()); ?>

            ];
            
            // Configuração do gráfico de distribuição
            const distribuicaoCtx = document.getElementById('graficoDistribuicao').getContext('2d');
            const chartDistribuicao = new Chart(distribuicaoCtx, {
                type: 'doughnut',
                data: {
                    labels: localizacoes,
                    datasets: [{
                        data: counts,
                        backgroundColor: [
                            '#17a2b8', // Info
                            '#28a745', // Success
                            '#ffc107', // Warning
                            '#dc3545', // Danger
                            '#007bff', // Primary
                            '#6c757d'  // Secondary
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Dados para o gráfico de valores
            const valores = [
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'Protocolo')->sum('valor_fatura')); ?>,
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'Lisura')->sum('valor_fatura')); ?>,
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'SIRE')->sum('valor_fatura')); ?>,
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'Glosa')->sum('valor_fatura')); ?>,
                <?php echo e(App\Models\Pacote::where('localizacao_atual', 'Arquivo')->sum('valor_fatura')); ?>

            ];
            
            // Configuração do gráfico de valores
            const valoresCtx = document.getElementById('graficoValores').getContext('2d');
            const chartValores = new Chart(valoresCtx, {
                type: 'bar',
                data: {
                    labels: localizacoes.slice(0, 5), // Excluir "Arquivados"
                    datasets: [{
                        label: 'Valor Total (R$)',
                        data: valores,
                        backgroundColor: [
                            'rgba(23, 162, 184, 0.6)', // Info
                            'rgba(40, 167, 69, 0.6)',  // Success
                            'rgba(255, 193, 7, 0.6)',  // Warning
                            'rgba(220, 53, 69, 0.6)',  // Danger
                            'rgba(0, 123, 255, 0.6)'   // Primary
                        ],
                        borderColor: [
                            'rgb(23, 162, 184)',
                            'rgb(40, 167, 69)',
                            'rgb(255, 193, 7)',
                            'rgb(220, 53, 69)',
                            'rgb(0, 123, 255)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.dataset.label || '';
                                    const value = context.raw || 0;
                                    return label + ': R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/admin21ct/pmed2/resources/views/dashboard.blade.php ENDPATH**/ ?>