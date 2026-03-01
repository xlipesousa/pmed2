<script>
// Modificar a configuração global do Chart.js no início do arquivo

// Configuração global do Chart.js
Chart.defaults.global.defaultFontFamily = "'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif";
Chart.defaults.global.responsive = true;
Chart.defaults.global.maintainAspectRatio = false;

// Evitar o uso de eval() no Chart.js
Chart.helpers.extend(Chart.helpers, {
    // Substituir qualquer método que use eval por implementações seguras
    retinaScale: function(chart, forceRatio) {
        var pixelRatio = forceRatio || window.devicePixelRatio || 1;
        var context = chart.ctx;
        var canvas = chart.canvas;
        var width = canvas.width;
        var height = canvas.height;

        if (pixelRatio !== 1) {
            canvas.width = width * pixelRatio;
            canvas.height = height * pixelRatio;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';
            context.scale(pixelRatio, pixelRatio);
            return true;
        }
        return false;
    }
});

// Configurações específicas para evitar uso de eval() no Chart.js
Chart.defaults.global.tooltips.custom = function(tooltip) {
    // Implementação personalizada que não usa eval()
    if (!tooltip) return;
    
    var tooltipEl = document.getElementById('chartjs-tooltip');
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'chartjs-tooltip';
        tooltipEl.innerHTML = "<table></table>";
        document.body.appendChild(tooltipEl);
    }
    
    // Ocultar caso não haja tooltip
    if (tooltip.opacity === 0) {
        tooltipEl.style.opacity = 0;
        return;
    }
    
    // Definir cabeçalho do tooltip se existe
    var tableRoot = tooltipEl.querySelector('table');
    tableRoot.innerHTML = '';
    
    function getBody(bodyItem) {
        return bodyItem.lines;
    }
    
    // Adicionar conteúdo
    if (tooltip.body) {
        var titleLines = tooltip.title || [];
        var bodyLines = tooltip.body.map(getBody);
        
        var innerHtml = '<thead>';
        titleLines.forEach(function(title) {
            innerHtml += '<tr><th>' + title + '</th></tr>';
        });
        innerHtml += '</thead><tbody>';
        
        bodyLines.forEach(function(body, i) {
            var colors = tooltip.labelColors[i];
            var style = 'background:' + colors.backgroundColor;
            style += '; border-color:' + colors.borderColor;
            style += '; border-width: 2px';
            var span = '<span class="chartjs-tooltip-key" style="' + style + '"></span>';
            innerHtml += '<tr><td>' + span + body + '</td></tr>';
        });
        innerHtml += '</tbody>';
        
        tableRoot.innerHTML = innerHtml;
    }
    
    // Posicionamento
    var position = this._chart.canvas.getBoundingClientRect();
    tooltipEl.style.opacity = 1;
    tooltipEl.style.position = 'absolute';
    tooltipEl.style.left = position.left + tooltip.caretX + 'px';
    tooltipEl.style.top = position.top + tooltip.caretY + 'px';
    tooltipEl.style.fontFamily = tooltip._fontFamily;
    tooltipEl.style.fontSize = tooltip.fontSize;
    tooltipEl.style.fontStyle = tooltip._fontStyle;
    tooltipEl.style.padding = tooltip.yPadding + 'px ' + tooltip.xPadding + 'px';
};

// Implementações alternativas seguras
Chart.helpers.safeCallback = function(callback, context, args) {
    if (typeof callback === 'function') {
        return callback.apply(context, args);
    }
    return undefined;
};

// Substituições para métodos que possam usar eval()
if (Chart.helpers && Chart.helpers.getValueOrDefault) {
    const originalGetValueOrDefault = Chart.helpers.getValueOrDefault;
    Chart.helpers.getValueOrDefault = function(value, defaultValue) {
        if (typeof value === 'string' && value.indexOf('function') === 0) {
            console.warn('Tentativa de usar string como função - bloqueada por CSP');
            return defaultValue;
        }
        return originalGetValueOrDefault(value, defaultValue);
    };
}

// Desativar tooltips que possam usar eval
Chart.defaults.global.tooltips.custom = function(tooltip) {
    // Implementação segura de tooltip customizado
    if (!tooltip) return;
    
    // Restante da implementação...
}

// Cores padrão para gráficos
const defaultColors = [
    '#3498db', // azul
    '#2ecc71', // verde
    '#e74c3c', // vermelho
    '#f39c12', // laranja
    '#9b59b6', // roxo
    '#1abc9c', // verde-água
    '#34495e', // azul escuro
    '#d35400', // laranja escuro
    '#95a5a6', // cinza
    '#7f8c8d'  // cinza escuro
];

// Função para carregar dados da visão geral
function carregarDadosVisaoGeral(filtros) {
    // Carregar distribuição por status
    $.ajax({
        url: '{{ route("graficos.status") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            console.log("Dados de status recebidos:", response);
            renderizarGraficoStatus(response);
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar dados de status:', error);
            console.error('Resposta do servidor:', xhr.responseText);
        }
    });
    
    // Carregar tendência de entrada/saída
    $.ajax({
        url: '{{ route("graficos.tendencia") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            console.log("Dados de tendência recebidos:", response);
            renderizarGraficoTendencia(response);
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar dados de tendência:', error);
            console.error('Resposta do servidor:', xhr.responseText);
        }
    });
    
    // Carregar volume por OCS/PSA
    $.ajax({
        url: '{{ route("graficos.volume") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            renderizarGraficoVolume(response);
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar dados de volume:', error);
        }
    });
    
    // Carregar distribuição por tipo
    $.ajax({
        url: '{{ route("graficos.tipo") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            renderizarGraficoTipo(response);
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar dados de tipo:', error);
        }
    });
}

// Função para carregar dados do fluxo de processo
function carregarDadosFluxo(filtros) {
    $.ajax({
        url: '{{ route("graficos.fluxo") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            console.log("Dados de fluxo recebidos:", response);
            
            // Atualizar KPIs de fluxo
            $('#kpi-protocolo').text(response.protocolo || 0);
            $('#kpi-lisura').text(response.lisura || 0);
            $('#kpi-sire').text(response.sire || 0);
            $('#kpi-glosa').text(response.glosa || 0);
            
            // Verificar se os dados tempo_etapas existem
            if (response.tempo_etapas) {
                console.log("Dados tempo_etapas:", response.tempo_etapas);
                console.log("Dados de tempo corretos:", 
                    response.tempo_etapas.labels, 
                    response.tempo_etapas.values
                );
                
                // Renderizar gráfico de tempo por etapa
                renderizarGraficoTempoEtapa(response.tempo_etapas);
            } else {
                console.error("Dados de tempo_etapas não encontrados na resposta");
            }
            
            // Verificar se os dados volume_etapas existem
            if (response.volume_etapas) {
                console.log("Dados volume_etapas:", response.volume_etapas);
                console.log("Dados de volume corretos:", 
                    response.volume_etapas.labels, 
                    response.volume_etapas.values
                );
                
                // Renderizar gráfico de volume por etapa
                renderizarGraficoVolumeEtapa(response.volume_etapas);
            } else {
                console.error("Dados de volume_etapas não encontrados na resposta");
            }
            
            // Renderizar diagrama de fluxo
            renderizarDiagramaFluxo({
                counts: {
                    protocolo: response.protocolo || 0,
                    lisura: response.lisura || 0,
                    sire: response.sire || 0,
                    glosa: response.glosa || 0,
                    arquivo: response.arquivo || 0
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar dados de fluxo:', error);
            console.error('Resposta do servidor:', xhr.responseText);
        }
    });
}

// Renderizar gráfico de status
function renderizarGraficoStatus(data) {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    if (charts.statusChart) {
        charts.statusChart.destroy();
    }
    
    // Verificar se temos dados
    if (!data || !data.labels || !data.values) {
        console.error('Dados inválidos para renderização do gráfico de status');
        return;
    }
    
    // Usar cores fornecidas pela API, ou gerar cores se não fornecidas
    let backgroundColors = data.colors || [];
    
    // Se não temos cores suficientes da API, gerar cores adicionais
    if (!backgroundColors || backgroundColors.length < data.labels.length) {
        // Definição de cores específicas para cada status comum
        const coresPorStatus = {
            'Protocolo': '#3498db',    // azul
            'Lisura': '#2ecc71',       // verde
            'SIRE': '#f39c12',         // laranja
            'Glosa': '#e74c3c',        // vermelho
            'Arquivo': '#9b59b6',      // roxo
            'Arquivado': '#1abc9c',    // verde-água
        };
        
        // Redefinir array de cores
        backgroundColors = [];
        
        // Atribuir cores específicas para cada status se disponível, ou usar cor padrão
        data.labels.forEach(label => {
            const statusLowerCase = label.toLowerCase();
            
            // Verificar se existe uma correspondência exata ou parcial nas cores definidas
            let corEncontrada = false;
            
            Object.keys(coresPorStatus).forEach(status => {
                if (statusLowerCase.includes(status.toLowerCase()) || status.toLowerCase().includes(statusLowerCase)) {
                    backgroundColors.push(coresPorStatus[status]);
                    corEncontrada = true;
                }
            });
            
            // Se não encontrou cor específica, usa da paleta padrão
            if (!corEncontrada) {
                const indice = backgroundColors.length % defaultColors.length;
                backgroundColors.push(defaultColors[indice]);
            }
        });
    }
    
    // Log para depuração
    console.log("Cores do gráfico de status:", backgroundColors);
    
    charts.statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: backgroundColors, // Usar as cores definidas
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 12
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const total = dataset.data.reduce((acc, val) => acc + val, 0);
                        const value = dataset.data[tooltipItem.index];
                        const percentual = Math.round((value / total) * 100);
                        return `${data.labels[tooltipItem.index]}: ${value} (${percentual}%)`;
                    }
                }
            }
        }
    });
}

// Renderizar gráfico de tendência
function renderizarGraficoTendencia(data) {
    const canvas = document.getElementById('tendenciaChart');
    
    if (!canvas) {
        console.warn("Elemento tendenciaChart não encontrado na página atual.");
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (charts.tendenciaChart) {
        charts.tendenciaChart.destroy();
    }
    
    // Verificar se temos dados válidos
    if (!data || !data.labels || !data.entradas || !data.saidas) {
        console.error('Dados inválidos para renderização do gráfico de tendência:', data);
        return;
    }
    
    console.log("Renderizando gráfico de tendência com dados:", data);
    
    charts.tendenciaChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Entradas',
                    data: data.entradas,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#4CAF50',
                    tension: 0.2,
                    fill: true
                },
                {
                    label: 'Saídas',
                    data: data.saidas,
                    borderColor: '#F44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    pointBackgroundColor: '#F44336',
                    tension: 0.2,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    gridLines: {
                        display: false
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    },
                    gridLines: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }]
            },
            legend: {
                position: 'bottom',
                labels: {
                    boxWidth: 12
                }
            },
            tooltips: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const value = dataset.data[tooltipItem.index];
                        return `${dataset.label}: ${value}`;
                    }
                }
            }
        }
    });
}

// Renderizar gráfico de volume
function renderizarGraficoVolume(data) {
    const ctx = document.getElementById('volumeChart').getContext('2d');
    
    if (charts.volumeChart) {
        charts.volumeChart.destroy();
    }
    
    charts.volumeChart = new Chart(ctx, {
        type: 'horizontalBar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Quantidade de Pacotes',
                data: data.values,
                backgroundColor: '#3498db',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                xAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            },
            legend: {
                display: false
            }
        }
    });
}

// Renderizar gráfico de tipo
function renderizarGraficoTipo(data) {
    const ctx = document.getElementById('tipoChart').getContext('2d');
    
    if (charts.tipoChart) {
        charts.tipoChart.destroy();
    }
    
    charts.tipoChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: data.colors || defaultColors,
                borderWidth: 1
            }]
        },
        options: {
            legend: {
                position: 'right'
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const total = dataset.data.reduce((previousValue, currentValue) => previousValue + currentValue);
                        const currentValue = dataset.data[tooltipItem.index];
                        const percentage = Math.floor(((currentValue/total) * 100)+0.5);
                        
                        return `${data.labels[tooltipItem.index]}: ${currentValue} (${percentage}%)`;
                    }
                }
            }
        }
    });
}

// Funções para a apresentação em tela cheia
function iniciarApresentacao() {
    // Copiar dados dos KPIs para o modo apresentação
    $('.presentation-kpi-total-pacotes').text($('#kpi-total-pacotes').text());
    $('.presentation-kpi-valor-total').text($('#kpi-valor-total-faturas').text());
    $('.presentation-kpi-taxa-glosa').text($('#kpi-taxa-media-glosa').text());
    $('.presentation-kpi-tempo-medio').text($('#kpi-tempo-medio-dias').text() + ' dias');
    
    // Preparar gráficos para apresentação
    prepararGraficosApresentacao();
}

function prepararGraficosApresentacao() {
    // Copiar gráficos do dashboard para o modo apresentação
    if (charts.statusChart) {
        const ctx = document.getElementById('presentation-status-chart').getContext('2d');
        const config = charts.statusChart.config;
        
        if (charts.presentationStatusChart) {
            charts.presentationStatusChart.destroy();
        }
        
        charts.presentationStatusChart = new Chart(ctx, {
            type: config.type,
            data: config.data,
            options: {
                ...config.options,
                legend: {
                    position: 'right',
                    labels: {
                        fontSize: 16
                    }
                },
                tooltips: {
                    bodyFontSize: 16,
                    titleFontSize: 18
                }
            }
        });
    }
    
    // Outros gráficos serão preparados quando o slide for exibido
}

// Funções para renderizar gráficos financeiros
function renderizarComposicaoValorTotal(data) {
    const ctx = document.getElementById('composicaoValorTotalChart').getContext('2d');
    
    if (charts.composicaoValorTotalChart) {
        charts.composicaoValorTotalChart.destroy();
    }
    
    charts.composicaoValorTotalChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: data.colors || defaultColors,
                borderWidth: 1
            }]
        },
        options: {
            legend: {
                position: 'right'
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const total = dataset.data.reduce((previousValue, currentValue) => previousValue + currentValue);
                        const currentValue = dataset.data[tooltipItem.index];
                        const percentage = Math.floor(((currentValue/total) * 100)+0.5);
                        
                        return `${data.labels[tooltipItem.index]}: ${formatarValor(currentValue)} (${percentage}%)`;
                    }
                }
            }
        }
    });
}

function renderizarEvolucaoValoresMensais(data) {
    const ctx = document.getElementById('evolucaoValoresMensaisChart').getContext('2d');
    
    if (charts.evolucaoValoresMensaisChart) {
        charts.evolucaoValoresMensaisChart.destroy();
    }
    
    charts.evolucaoValoresMensaisChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Valor Total',
                data: data.valores,
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return formatarValor(value);
                        }
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem) {
                        return formatarValor(tooltipItem.yLabel);
                    }
                }
            }
        }
    });
}

// Adicionar função faltante de renderização
function renderizarTempoMedioPorTipo(data) {
    const ctx = document.getElementById('tempoMedioPorTipoChart');
    
    // Verificar se o elemento existe no DOM
    if (!ctx) {
        console.error("Elemento tempoMedioPorTipoChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.tempoMedioPorTipoChart) {
        charts.tempoMedioPorTipoChart.destroy();
    }
    
    charts.tempoMedioPorTipoChart = new Chart(context, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Tempo Médio (dias)',
                data: data.values || [],
                backgroundColor: '#3498db',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
}

// Renderizar gráfico de motivos de glosa
function renderizarMotivosGlosa(data) {
    const ctx = document.getElementById('motivosGlosaChart');
    
    // Verificar se o elemento existe no DOM
    if (!ctx) {
        console.error("Elemento motivosGlosaChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.motivosGlosaChart) {
        charts.motivosGlosaChart.destroy();
    }
    
    charts.motivosGlosaChart = new Chart(context, {
        type: 'pie',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: data.colors || defaultColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'right',
                labels: {
                    padding: 20
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const total = dataset.data.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
                        const currentValue = dataset.data[tooltipItem.index];
                        const percentage = Math.floor(((currentValue/total) * 100)+0.5);
                        
                        return `${data.labels[tooltipItem.index]}: ${currentValue} (${percentage}%)`;
                    }
                }
            }
        }
    });
}

// Renderizar gráfico de performance por OCS/PSA
function renderizarPerformanceOcsPsa(data) {
    const ctx = document.getElementById('performanceOcsPsaChart');
    
    // Verificar se o elemento existe no DOM
    if (!ctx) {
        console.error("Elemento performanceOcsPsaChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.performanceOcsPsaChart) {
        charts.performanceOcsPsaChart.destroy();
    }
    
    charts.performanceOcsPsaChart = new Chart(context, {
        type: 'horizontalBar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Tempo Médio de Processamento (dias)',
                data: data.values || [],
                backgroundColor: '#3498db',
                borderColor: '#2980b9',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
}

// Renderizar gráfico de top OCS/PSA por valor
function renderizarTopOcsPsaValor(data) {
    const ctx = document.getElementById('topOcsPsaValorChart');
    
    // Verificar se o elemento existe no DOM
    if (!ctx) {
        console.error("Elemento topOcsPsaValorChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.topOcsPsaValorChart) {
        charts.topOcsPsaValorChart.destroy();
    }
    
    charts.topOcsPsaValorChart = new Chart(context, {
        type: 'horizontalBar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Valor Total (R$)',
                data: data.values || [],
                backgroundColor: '#2ecc71',
                borderColor: '#27ae60',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return 'R$ ' + tooltipItem.xLabel.toLocaleString('pt-BR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            }
        }
    });
}

// Renderizar gráfico de distribuição por tipo de conta
function renderizarDistribuicaoTipoConta(data) {
    const ctx = document.getElementById('tipoContaChart');
    
    // Verificar se o elemento existe no DOM
    if (!ctx) {
        console.error("Elemento tipoContaChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.tipoContaChart) {
        charts.tipoContaChart.destroy();
    }
    
    charts.tipoContaChart = new Chart(context, {
        type: 'pie',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: data.colors || defaultColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'right'
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const total = dataset.data.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
                        const currentValue = dataset.data[tooltipItem.index];
                        const percentage = Math.floor(((currentValue/total) * 100)+0.5);
                        
                        return `${data.labels[tooltipItem.index]}: ${percentage}%`;
                    }
                }
            }
        }
    });
}

// Renderizar gráfico de tendência de glosas
function renderizarTendenciaGlosa(data) {
    const ctx = document.getElementById('tendenciaGlosaChart');
    
    // Verificar se o elemento existe no DOM
    if (!ctx) {
        console.error("Elemento tendenciaGlosaChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.tendenciaGlosaChart) {
        charts.tendenciaGlosaChart.destroy();
    }
    
    charts.tendenciaGlosaChart = new Chart(context, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Valor Glosado (R$)',
                data: data.valores || [],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }]
            }
        }
    });
}

// Renderizar gráfico de Meta vs Realizado
function renderizarMetaRealizado(data) {
    const ctx = document.getElementById('metaRealizadoChart');
    if (!ctx) {
        console.error("Elemento metaRealizadoChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.metaRealizadoChart) {
        charts.metaRealizadoChart.destroy();
    }
    
    charts.metaRealizadoChart = new Chart(context, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'Meta',
                    data: data.meta || [],
                    borderColor: '#3498db',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 3
                },
                {
                    label: 'Realizado',
                    data: data.realizado || [],
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.1)',
                    borderWidth: 2,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Dias'
                    }
                }]
            }
        }
    });
}

function renderizarDesempenhoRanking(data) {
    const ctx = document.getElementById('desempenhoRankingChart');
    if (!ctx) {
        console.error('Elemento desempenhoRankingChart não encontrado!');
        return;
    }

    const context = ctx.getContext('2d');

    if (charts.desempenhoRankingChart) {
        charts.desempenhoRankingChart.destroy();
    }

    charts.desempenhoRankingChart = new Chart(context, {
        type: 'horizontalBar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Score Operacional',
                data: data.values || [],
                backgroundColor: '#3498db',
                borderColor: '#2c80b4',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100
                    }
                }]
            }
        }
    });
}

function renderizarDesempenhoEixos(data) {
    const ctx = document.getElementById('desempenhoEixosChart');
    if (!ctx) {
        console.error('Elemento desempenhoEixosChart não encontrado!');
        return;
    }

    const context = ctx.getContext('2d');

    if (charts.desempenhoEixosChart) {
        charts.desempenhoEixosChart.destroy();
    }

    charts.desempenhoEixosChart = new Chart(context, {
        type: 'bar',
        data: {
            labels: data.labels || [],
            datasets: [
                {
                    label: 'Volume',
                    data: data.volume || [],
                    backgroundColor: '#3498db'
                },
                {
                    label: 'Tempo',
                    data: data.tempo || [],
                    backgroundColor: '#2ecc71'
                },
                {
                    label: 'Qualidade',
                    data: data.qualidade || [],
                    backgroundColor: '#f39c12'
                },
                {
                    label: 'Retrabalho',
                    data: data.retrabalho || [],
                    backgroundColor: '#9b59b6'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100
                    }
                }]
            }
        }
    });
}

function renderizarDesempenhoRetrabalho(data) {
    const ctx = document.getElementById('desempenhoRetrabalhoChart');
    if (!ctx) {
        console.error('Elemento desempenhoRetrabalhoChart não encontrado!');
        return;
    }

    const context = ctx.getContext('2d');

    if (charts.desempenhoRetrabalhoChart) {
        charts.desempenhoRetrabalhoChart.destroy();
    }

    charts.desempenhoRetrabalhoChart = new Chart(context, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Taxa de Retrabalho (%)',
                data: data.values || [],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100,
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }]
            }
        }
    });
}

function renderizarDesempenhoHistorico(data) {
    const ctx = document.getElementById('desempenhoHistoricoChart');
    if (!ctx) {
        console.error('Elemento desempenhoHistoricoChart não encontrado!');
        return;
    }

    const context = ctx.getContext('2d');

    if (charts.desempenhoHistoricoChart) {
        charts.desempenhoHistoricoChart.destroy();
    }

    charts.desempenhoHistoricoChart = new Chart(context, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Score Médio Mensal',
                data: data.valores || [],
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100
                    }
                }]
            }
        }
    });
}

// Adicionar as funções de renderização de gráficos de fluxo
function renderizarGraficoTempoEtapa(data) {
    const canvas = document.getElementById('tempoEtapaChart');
    
    if (!canvas) {
        console.warn("Elemento tempoEtapaChart não encontrado na página atual.");
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (charts.tempoEtapaChart) {
        charts.tempoEtapaChart.destroy();
    }
    
    charts.tempoEtapaChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Tempo Médio (dias)',
                data: data.values,
                backgroundColor: 'rgba(52, 152, 219, 0.7)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return value + ' dias';
                        }
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Tempo médio (dias)'
                    }
                }],
                xAxes: [{
                    maxBarThickness: 50
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return tooltipItem.value + ' dias';
                    }
                }
            }
        }
    });
}

// Renderizar gráfico de volume por etapa
function renderizarGraficoVolumeEtapa(data) {
    const canvas = document.getElementById('volumeEtapaChart');
    
    if (!canvas) {
        console.warn("Elemento volumeEtapaChart não encontrado na página atual.");
        return;
    }
    
    const ctx = canvas.getContext('2d');
    
    if (charts.volumeEtapaChart) {
        charts.volumeEtapaChart.destroy();
    }
    
    // Definir cores específicas para cada etapa
    const backgroundColors = [
        '#3498db', // Azul para Protocolo
        '#2ecc71', // Verde para Lisura
        '#f1c40f', // Amarelo para SIRE
        '#e74c3c', // Vermelho para Glosa
        '#9b59b6'  // Roxo para Arquivo
    ];
    
    charts.volumeEtapaChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: backgroundColors,
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 12
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const total = dataset.data.reduce((acc, val) => acc + val, 0);
                        const value = dataset.data[tooltipItem.index];
                        const percentual = Math.round((value / total) * 100);
                        return `${data.labels[tooltipItem.index]}: ${value} (${percentual}%)`;
                    }
                }
            }
        }
    });
}

function atualizarTabelaGargalos(gargalos) {
    const tabela = $('#gargalos-table');
    tabela.empty();
    
    // Adicionar cabeçalho
    const thead = $('<thead>').append(
        $('<tr>').append(
            $('<th>').text('Etapa'),
            $('<th>').text('Problema'),
            $('<th>').text('Quantidade'),
            $('<th>').text('Impacto')
        )
    );
    
    // Adicionar linhas
    const tbody = $('<tbody>');
    gargalos.forEach(gargalo => {
        tbody.append(
            $('<tr>').append(
                $('<td>').text(gargalo.etapa),
                $('<td>').text(gargalo.problema),
                $('<td>').text(gargalo.quantidade),
                $('<td>').html(`<div class="progress">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: ${gargalo.impacto}%" aria-valuenow="${gargalo.impacto}" aria-valuemin="0" aria-valuemax="100">${gargalo.impacto}%</div>
                </div>`)
            )
        );
    });
    
    tabela.append(thead).append(tbody);
}

// Adicionar a função de renderização do gráfico Status dos Recursos de Glosa
function renderizarStatusRecursosGlosa(data) {
    const ctx = document.getElementById('statusRecursosGlosaChart');
    if (!ctx) {
        console.error("Elemento statusRecursosGlosaChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.statusRecursosGlosaChart) {
        charts.statusRecursosGlosaChart.destroy();
    }
    
    charts.statusRecursosGlosaChart = new Chart(context, {
        type: 'pie',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: data.colors || defaultColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'right',
                labels: {
                    padding: 20
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        const dataset = data.datasets[tooltipItem.datasetIndex];
                        const total = dataset.data.reduce((previousValue, currentValue) => previousValue + currentValue, 0);
                        const currentValue = dataset.data[tooltipItem.index];
                        const percentage = Math.floor(((currentValue/total) * 100)+0.5);
                        
                        return `${data.labels[tooltipItem.index]}: ${currentValue} (${percentage}%)`;
                    }
                }
            }
        }
    });
}

// Adicionar a função de renderização do gráfico OCS/PSA com Maior Taxa de Glosa
function renderizarOcsTaxaGlosa(data) {
    const ctx = document.getElementById('ocsTaxaGlosaChart');
    if (!ctx) {
        console.error("Elemento ocsTaxaGlosaChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.ocsTaxaGlosaChart) {
        charts.ocsTaxaGlosaChart.destroy();
    }
    
    charts.ocsTaxaGlosaChart = new Chart(context, {
        type: 'horizontalBar',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Taxa de Glosa (%)',
                data: data.values || [],
                backgroundColor: '#e74c3c',
                borderColor: '#c0392b',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Taxa (%)'
                    }
                }
            }
        }
    });
}

// Adicionar a função de renderização do gráfico Tendência de Tempo de Processamento
function renderizarTendenciaTempo(data) {
    const ctx = document.getElementById('tendenciaTempoChart');
    if (!ctx) {
        console.error("Elemento tendenciaTempoChart não encontrado!");
        return;
    }
    
    const context = ctx.getContext('2d');
    
    if (charts.tendenciaTempoChart) {
        charts.tendenciaTempoChart.destroy();
    }
    
    charts.tendenciaTempoChart = new Chart(context, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Tempo Médio de Processamento (dias)',
                data: data.valores || [],
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Dias'
                    }
                }]
            }
        }
    });
}

// Função para renderizar o diagrama de fluxo de pacotes
function renderizarDiagramaFluxo(data) {
    const container = document.getElementById('fluxo-diagrama');
    
    if (!container) {
        console.warn("Elemento fluxo-diagrama não encontrado na página atual.");
        return;
    }
    
    // Limpar o conteúdo anterior
    container.innerHTML = '';
    
    // Se não temos dados, exibir mensagem
    if (!data || !data.counts) {
        container.innerHTML = '<div class="alert alert-info">Não há dados de fluxo disponíveis para o período selecionado.</div>';
        return;
    }
    
    // Implementação do diagrama de fluxo
    let html = `
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="flow-step text-center p-3 m-2 bg-info">
                <i class="fas fa-inbox fa-2x"></i>
                <p class="mt-2">Protocolo</p>
                <span class="badge badge-light">${data.counts?.protocolo || 0}</span>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            
            <div class="flow-step text-center p-3 m-2 bg-success">
                <i class="fas fa-check-circle fa-2x"></i>
                <p class="mt-2">Lisura</p>
                <span class="badge badge-light">${data.counts?.lisura || 0}</span>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            
            <div class="flow-step text-center p-3 m-2 bg-warning">
                <i class="fas fa-cog fa-2x"></i>
                <p class="mt-2">SIRE</p>
                <span class="badge badge-light">${data.counts?.sire || 0}</span>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            
            <div class="flow-step text-center p-3 m-2 bg-danger">
                <i class="fas fa-exclamation-circle fa-2x"></i>
                <p class="mt-2">Glosa</p>
                <span class="badge badge-light">${data.counts?.glosa || 0}</span>
            </div>
            <div class="flow-arrow"><i class="fas fa-arrow-right"></i></div>
            
            <div class="flow-step text-center p-3 m-2 bg-primary">
                <i class="fas fa-archive fa-2x"></i>
                <p class="mt-2">Arquivo</p>
                <span class="badge badge-light">${data.counts?.arquivo || 0}</span>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
    
    // Adicionar estilos específicos
    const styleId = 'flow-diagram-style';
    if (!document.getElementById(styleId)) {
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
            .flow-step {
                min-width: 120px;
                border-radius: 8px;
                color: #fff;
                box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            }
            .flow-arrow {
                font-size: 24px;
                color: #aaa;
                display: flex;
                align-items: center;
            }
            @media (max-width: 768px) {
                .flow-arrow {
                    transform: rotate(90deg);
                    margin: 10px 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
}
</script>