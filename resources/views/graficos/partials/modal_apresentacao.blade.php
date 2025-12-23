<div class="modal fade" id="modal-apresentacao" tabindex="-1" role="dialog" aria-labelledby="modalApresentacaoLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title" id="modalApresentacaoLabel">
                    <i class="fas fa-tv mr-2"></i> Modo Apresentação
                </h5>
                <div class="ml-auto mr-3">
                    <button type="button" class="btn btn-outline-light btn-sm" id="prev-slide">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-outline-light btn-sm ml-2" id="next-slide">
                        Próximo <i class="fas fa-chevron-right"></i>
                    </button>
                    <span class="text-white ml-3" id="slide-counter">1/5</span>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="presentation-slides">
                    <!-- Slide 1: KPIs -->
                    <div class="slide slide-active" id="slide-kpis">
                        <div class="slide-header">
                            <h2>Indicadores Chave de Performance</h2>
                        </div>
                        <div class="slide-content">
                            <div class="row">
                                <div class="col-md-6 col-lg-3">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-box"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total de Pacotes</span>
                                            <span class="info-box-number presentation-kpi-total-pacotes">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Valor Total</span>
                                            <span class="info-box-number presentation-kpi-valor-total">R$ 0,00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Taxa Média de Glosa</span>
                                            <span class="info-box-number presentation-kpi-taxa-glosa">0%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="info-box bg-danger">
                                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tempo Médio</span>
                                            <span class="info-box-number presentation-kpi-tempo-medio">0 dias</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="chart-container presentation-chart">
                                        <canvas id="presentation-status-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide 2: Fluxo de Processo -->
                    <div class="slide" id="slide-fluxo">
                        <div class="slide-header">
                            <h2>Análise de Fluxo de Processo</h2>
                        </div>
                        <div class="slide-content">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="chart-container presentation-chart">
                                        <canvas id="presentation-fluxo-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide 3: Financeiro -->
                    <div class="slide" id="slide-financeiro">
                        <div class="slide-header">
                            <h2>Análise Financeira</h2>
                        </div>
                        <div class="slide-content">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="chart-container presentation-chart">
                                        <canvas id="presentation-financeiro-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide 4: Glosas -->
                    <div class="slide" id="slide-glosas">
                        <div class="slide-header">
                            <h2>Análise de Glosas</h2>
                        </div>
                        <div class="slide-content">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="chart-container presentation-chart">
                                        <canvas id="presentation-glosas-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slide 5: Performance -->
                    <div class="slide" id="slide-performance">
                        <div class="slide-header">
                            <h2>Métricas de Performance</h2>
                        </div>
                        <div class="slide-content">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="chart-container presentation-chart">
                                        <canvas id="presentation-performance-chart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modal-fullscreen {
    width: 100%;
    max-width: 100%;
    margin: 0;
    height: 100%;
}

.modal-fullscreen .modal-content {
    height: 100%;
    border: 0;
    border-radius: 0;
}

.presentation-slides {
    width: 100%;
    height: 100%;
    position: relative;
}

.slide {
    display: none;
    width: 100%;
    height: 100%;
    padding: 20px;
}

.slide-active {
    display: block;
}

.slide-header {
    text-align: center;
    margin-bottom: 30px;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 15px;
}

.presentation-chart {
    height: 70vh !important;
}

.slide-content {
    height: calc(100% - 80px);
    overflow-y: auto;
}
</style>