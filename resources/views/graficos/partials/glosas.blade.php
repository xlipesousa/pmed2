<div class="row">
    <!-- KPIs de Glosas -->
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Total Glosado</span>
                <span class="info-box-number" id="kpi-valor-glosado">R$ 0,00</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-hashtag"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pacotes com Glosa</span>
                <span class="info-box-number" id="kpi-pacotes-glosados">0</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-chart-pie"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Taxa de Recuperação</span>
                <span class="info-box-number" id="kpi-taxa-recuperacao">0%</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Irrecuperável</span>
                <span class="info-box-number" id="kpi-valor-irrecuperavel">R$ 0,00</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Motivos de Glosa -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Motivos de Glosa Mais Frequentes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="motivosGlosaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status de Recursos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Status dos Recursos de Glosa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="statusRecursosGlosaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- OCS/PSA com Maior Taxa de Glosa -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-hospital mr-1"></i> OCS/PSA com Maior Taxa de Glosa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="ocsTaxaGlosaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tendência de Glosas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Tendência de Glosas</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="tendenciaGlosaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>