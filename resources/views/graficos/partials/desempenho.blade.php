<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-primary">
            <span class="info-box-icon"><i class="fas fa-star"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Score Médio</span>
                <span class="info-box-number" id="kpi-desempenho-media-score">0</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Melhor Colaborador</span>
                <span class="info-box-number" id="kpi-desempenho-melhor-colaborador">-</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Movimentações</span>
                <span class="info-box-number" id="kpi-desempenho-total-movimentacoes">0</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-redo"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Retrabalho Médio</span>
                <span class="info-box-number" id="kpi-desempenho-retrabalho-medio">0%</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title"><i class="fas fa-list-ol mr-1"></i> Ranking de Score Operacional</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="desempenhoRankingChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="card-title"><i class="fas fa-layer-group mr-1"></i> Eixos do Score (Top 5)</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="desempenhoEixosChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Taxa de Retrabalho por Colaborador</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="maximize">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="desempenhoRetrabalhoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
