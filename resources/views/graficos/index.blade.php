@extends('adminlte::page')

@section('title', 'Dashboard Analítico Estratégico')

@section('content_header')
    <h1>Dashboard Analítico Estratégico</h1>
@stop

@section('content')
    <!-- Conteúdo do dashboard -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Visão Geral</h3>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-primary" id="btn-atualizar-dados">
                            <i class="fas fa-sync-alt"></i> Atualizar Dados
                        </button>
                        
                        <div class="btn-group ml-2">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-download"></i> Exportar
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item export-all" href="#" data-type="excel">
                                    <i class="fas fa-file-excel text-success mr-2"></i> Excel
                                </a>
                                <a class="dropdown-item export-all" href="#" data-type="pdf">
                                    <i class="fas fa-file-pdf text-danger mr-2"></i> PDF
                                </a>
                                <a class="dropdown-item export-all" href="#" data-type="csv">
                                    <i class="fas fa-file-csv text-info mr-2"></i> CSV
                                </a>
                            </div>
                        </div>
                        
                        <button class="btn btn-sm btn-info ml-2" id="btn-apresentacao">
                            <i class="fas fa-play-circle"></i> Modo Apresentação
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    @include('graficos.partials.filtros')
                    
                    <!-- KPIs Principais -->
                    @include('graficos.partials.kpis')
                    
                    <!-- Abas de Conteúdo -->
                    <div class="card card-primary card-outline card-tabs">
                        <div class="card-header p-0 pt-1 border-bottom-0">
                            <ul class="nav nav-tabs" id="dashboard-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-visao-geral" data-toggle="pill" href="#visao-geral" role="tab" aria-controls="visao-geral" aria-selected="true">
                                        <i class="fas fa-chart-pie mr-1"></i> Visão Geral
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-fluxo" data-toggle="pill" href="#fluxo" role="tab" aria-controls="fluxo" aria-selected="false">
                                        <i class="fas fa-project-diagram mr-1"></i> Fluxo de Processo
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-financeiro" data-toggle="pill" href="#financeiro" role="tab" aria-controls="financeiro" aria-selected="false">
                                        <i class="fas fa-dollar-sign mr-1"></i> Financeiro
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-glosa" data-toggle="pill" href="#glosa" role="tab" aria-controls="glosa" aria-selected="false">
                                        <i class="fas fa-exclamation-triangle mr-1"></i> Glosas
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-performance" data-toggle="pill" href="#performance" role="tab" aria-controls="performance" aria-selected="false">
                                        <i class="fas fa-tachometer-alt mr-1"></i> Performance
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-desempenho" data-toggle="pill" href="#desempenho" role="tab" aria-controls="desempenho" aria-selected="false">
                                        <i class="fas fa-users mr-1"></i> Desempenho
                                    </a>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Visão Geral -->
                                <div class="tab-pane fade show active" id="visao-geral" role="tabpanel">
                                    @include('graficos.partials.visao_geral')
                                </div>
                                
                                <!-- Fluxo de Processo -->
                                <div class="tab-pane fade" id="fluxo" role="tabpanel">
                                    @include('graficos.partials.fluxo_processo')
                                </div>
                                
                                <!-- Financeiro -->
                                <div class="tab-pane fade" id="financeiro" role="tabpanel">
                                    @include('graficos.partials.financeiro')
                                </div>
                                
                                <!-- Glosas -->
                                <div class="tab-pane fade" id="glosa" role="tabpanel">
                                    @include('graficos.partials.glosas')
                                </div>
                                
                                <!-- Performance -->
                                <div class="tab-pane fade" id="performance" role="tabpanel">
                                    @include('graficos.partials.performance')
                                </div>

                                <div class="tab-pane fade" id="desempenho" role="tabpanel">
                                    @include('graficos.partials.desempenho')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Apresentação -->
    @include('graficos.partials.modal_apresentacao')
@stop

@section('css')
    <!-- Atualizar para CDNs mais recentes e confiáveis -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
    
    <style>
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        .presentation-slide {
            display: none;
        }
        
        .slide-active {
            display: block;
        }
        
        /* Estilos adicionais para o diagrama de fluxo */
        #fluxo-diagrama {
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #chartjs-tooltip {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border-radius: 3px;
            pointer-events: none;
            z-index: 1000;
        }

        .chartjs-tooltip-key {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 10px;
            border-radius: 50%;
        }
        
        /* Estilos para corrigir a aparência do Select2 */
        .select2-container--bootstrap4 .select2-selection {
            height: calc(2.25rem + 2px) !important;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            padding-left: 0;
            height: auto;
            margin-top: -3px;
        }
        
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            position: absolute;
            top: 50%;
            right: 3px;
            width: 20px;
            height: 20px;
            margin-top: -10px;
        }
        
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b {
            border-color: #888 transparent transparent transparent;
            border-style: solid;
            border-width: 5px 4px 0 4px;
            height: 0;
            left: 50%;
            margin-left: -4px;
            margin-top: -2px;
            position: absolute;
            top: 50%;
            width: 0;
        }
        
        .select2-container--bootstrap4.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #888 transparent;
            border-width: 0 4px 5px 4px;
        }
        
        .select2-container--bootstrap4 .select2-selection--multiple {
            min-height: calc(2.25rem + 2px) !important;
        }
        
        .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff;
        }
    </style>
@stop

@section('js')
    <!-- Primeiro jQuery (certifique-se que há apenas uma versão) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS (necessário para modais e outros componentes) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Moment.js antes do DateRangePicker (essencial para funcionamento) -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/locale/pt-br.js"></script>
    
    <!-- DateRangePicker após Moment -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    
    <!-- Select2 antes do Chart.js para garantir que seja carregado corretamente -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Demais bibliotecas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Scripts da aplicação por último -->
    @include('graficos.partials.js.charts')
    @include('graficos.partials.js.dashboard')
    @include('graficos.partials.js.exportar')

    <!-- Script de inicialização direto -->
    <script>
        $(document).ready(function() {
            // Select2 inicialização - certifique-se que é executado antes de qualquer outra inicialização
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Selecione...',
                allowClear: true,
                width: '100%'
            });
            
            // DateRangePicker inicialização
            try {
                $('#filtro-periodo').daterangepicker({
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment(),
                    ranges: {
                       'Hoje': [moment(), moment()],
                       'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                       'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
                       'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
                       'Este mês': [moment().startOf('month'), moment().endOf('month')],
                       'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    locale: {
                        format: 'DD/MM/YYYY',
                        applyLabel: 'Aplicar',
                        cancelLabel: 'Cancelar',
                        fromLabel: 'De',
                        toLabel: 'Até',
                        customRangeLabel: 'Período personalizado',
                        weekLabel: 'S',
                        daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                        firstDay: 1
                    }
                });
                console.log('DateRangePicker inicializado com sucesso.');
            } catch (e) {
                console.error('Erro ao inicializar daterangepicker:', e);
            }
        });
    </script>

    <script>
        // Correção do botão de exportação de gráficos
        $(document).ready(function() {
            console.log('Inicializando handlers para botões de exportação de gráficos...');
            
            // Vinculação direta usando delegação de eventos (mais confiável)
            $(document).on('click', '.export-all', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var tipo = $(this).data('type');
                console.log('Botão de exportação de gráficos clicado: ' + tipo);
                
                // Construir URL de exportação 
                var url = "{{ route('graficos.exportar', ['tipo' => ':tipo']) }}";
                url = url.replace(':tipo', tipo);
                
                // Adicionar parâmetros dos filtros
                var filtros = $('#form-filtros').serialize();
                if (filtros) {
                    url += '?' + filtros;
                }
                
                console.log('URL de exportação de gráficos: ' + url);
                
                // Abrir em nova aba
                window.open(url, '_blank');
            });
            
            // Verificação visual da presença dos elementos
            setTimeout(function() {
                console.log('Verificando elementos de exportação:');
                console.log('- Botão dropdown existente: ' + ($('.dropdown-toggle:contains("Exportar")').length > 0));
                console.log('- Links de exportação encontrados: ' + $('.export-all').length);
                $('.export-all').each(function(i) {
                    console.log('  - Link ' + (i+1) + ': ' + $(this).data('type'));
                });
            }, 500);
        });
    </script>
@stop