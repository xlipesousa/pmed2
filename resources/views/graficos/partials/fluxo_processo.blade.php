<div class="row">
    <!-- KPIs de Fluxo -->
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-inbox"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Em Protocolo</span>
                <span class="info-box-number" id="kpi-protocolo">0</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Em Lisura</span>
                <span class="info-box-number" id="kpi-lisura">0</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-cog"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Em SIRE</span>
                <span class="info-box-number" id="kpi-sire">0</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-exclamation-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Em Glosa</span>
                <span class="info-box-number" id="kpi-glosa">0</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tempo Médio por Etapa -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-clock mr-1"></i> Tempo Médio por Etapa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="tempoEtapaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Volume por Etapa -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-1"></i> Volume por Etapa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="volumeEtapaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Diagrama de Fluxo -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-project-diagram mr-1"></i> Diagrama de Fluxo de Pacotes</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" id="fluxo-diagrama" style="height: 400px;">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                            </div>
                            <div class="mt-2">Carregando diagrama de fluxo...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>