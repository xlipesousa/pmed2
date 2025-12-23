<div class="row">
    <!-- KPIs Financeiros -->
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Implantado</span>
                <span class="info-box-number" id="kpi-valor-implantado">R$ 0,00</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Pendente</span>
                <span class="info-box-number" id="kpi-valor-pendente">R$ 0,00</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-sync-alt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Recursado</span>
                <span class="info-box-number" id="kpi-valor-recursado">R$ 0,00</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Glosado Final</span>
                <span class="info-box-number" id="kpi-valor-glosado-final">R$ 0,00</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Composição do Valor Total -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-1"></i> Composição do Valor Total</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="composicaoValorTotalChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Evolução dos Valores Mensais -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success">
                <h3 class="card-title"><i class="fas fa-chart-line mr-1"></i> Evolução dos Valores Mensais</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="evolucaoValoresMensaisChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top 5 OCS/PSA por Valor -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-hospital mr-1"></i> Top 5 OCS/PSA por Valor</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="topOcsPsaValorChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Distribuição por Tipo de Conta -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Distribuição por Tipo de Conta</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="tipoContaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>