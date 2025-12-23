<div class="row">
    <!-- KPIs de Performance -->
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tempo Médio Total</span>
                <span class="info-box-number" id="kpi-tempo-medio-total">0 dias</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-tachometer-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pacotes Finalizados</span>
                <span class="info-box-number" id="kpi-pacotes-finalizados">0</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pacotes em Andamento</span>
                <span class="info-box-number" id="kpi-pacotes-andamento">0</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pacotes Atrasados</span>
                <span class="info-box-number" id="kpi-pacotes-atrasados">0</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tempo Médio por Tipo de Pacote -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-clock mr-1"></i> Tempo Médio por Tipo de Pacote</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="tempoMedioPorTipoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance por OCS/PSA -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-hospital mr-1"></i> Performance por OCS/PSA</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="performanceOcsPsaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tendência de Tempo de Processamento -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Tendência de Tempo de Processamento</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="tendenciaTempoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Comparativo Meta vs Realizado -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-bullseye mr-1"></i> Meta vs Realizado</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="metaRealizadoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>