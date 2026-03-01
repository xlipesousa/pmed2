<script>
// Funções para exportar dados
$(document).ready(function() {
    // Exportar para Excel
    $('.export-all[data-type="excel"]').on('click', function(e) {
        e.preventDefault();
        exportarDados('excel');
    });
    
    // Exportar para PDF
    $('.export-all[data-type="pdf"]').on('click', function(e) {
        e.preventDefault();
        exportarDados('pdf');
    });
    
    // Exportar para CSV
    $('.export-all[data-type="csv"]').on('click', function(e) {
        e.preventDefault();
        exportarDados('csv');
    });
    
    // Controladores da apresentação
    $('#modal-apresentacao').on('show.bs.modal', function() {
        iniciarApresentacao();
    });
    
    $('#prev-slide').on('click', function() {
        mudaSlide(-1);
    });
    
    $('#next-slide').on('click', function() {
        mudaSlide(1);
    });
    
    // Suporte a teclas para navegação nos slides
    $(document).on('keydown', function(e) {
        if ($('#modal-apresentacao').hasClass('show')) {
            if (e.key === 'ArrowLeft') {
                mudaSlide(-1);
            } else if (e.key === 'ArrowRight') {
                mudaSlide(1);
            } else if (e.key === 'Escape') {
                $('#modal-apresentacao').modal('hide');
            }
        }
    });
});

// Função para exportar dados
function exportarDados(tipo) {
    const filtros = obterFiltros();
    
    Swal.fire({
        title: 'Exportando dados...',
        text: 'Por favor, aguarde enquanto preparamos seu arquivo.',
        didOpen: () => {
            Swal.showLoading();
            
            // Requisição Ajax para exportação
            $.ajax({
                url: `{{ url('/graficos/exportar') }}/${tipo}`,
                method: 'GET',
                data: filtros,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    const blob = new Blob([response]);
                    const fileName = extrairNomeArquivo(xhr) || `dashboard-${moment().format('YYYYMMDD')}.${tipo}`;
                    
                    // Criar link para download
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = fileName;
                    link.click();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Exportação concluída!',
                        text: `Os dados foram exportados para o formato ${tipo.toUpperCase()} com sucesso.`
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro na exportação',
                        text: 'Não foi possível exportar os dados. Tente novamente mais tarde.'
                    });
                }
            });
        }
    });
}

// Função para extrair nome do arquivo do cabeçalho da resposta
function extrairNomeArquivo(xhr) {
    const header = xhr.getResponseHeader('Content-Disposition');
    if (header) {
        const match = /filename="?([^"]*)"?/.exec(header);
        if (match && match[1]) {
            return match[1];
        }
    }
    return null;
}

// Funções para controle de slides
let slideAtual = 0;
const totalSlides = 5;

function mudaSlide(direcao) {
    // Esconder slide atual
    $('.slide-active').removeClass('slide-active');
    
    // Calcular próximo slide
    slideAtual = (slideAtual + direcao + totalSlides) % totalSlides;
    
    // Mostrar novo slide
    $('.slide').eq(slideAtual).addClass('slide-active');
    
    // Atualizar contador
    $('#slide-counter').text(`${slideAtual + 1}/${totalSlides}`);
    
    // Carregar gráfico específico do slide se necessário
    carregarGraficoSlide(slideAtual);
}

function carregarGraficoSlide(index) {
    console.log(`Carregando gráfico para o slide ${index}`);
    
    switch(index) {
        case 0: // Slide KPIs
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
                            labels: {
                                fontSize: 16
                            }
                        },
                        animation: {
                            duration: 1000
                        }
                    }
                });
            }
            break;
            
        case 1: // Slide de Fluxo
            if (charts.tempoEtapaChart) {
                const ctx = document.getElementById('presentation-fluxo-chart').getContext('2d');
                const config = charts.tempoEtapaChart.config;
                
                if (charts.presentationFluxoChart) {
                    charts.presentationFluxoChart.destroy();
                }
                
                charts.presentationFluxoChart = new Chart(ctx, {
                    type: config.type,
                    data: config.data,
                    options: {
                        ...config.options,
                        legend: {
                            labels: {
                                fontSize: 16
                            }
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    fontSize: 14
                                }
                            }],
                            xAxes: [{
                                ticks: {
                                    fontSize: 14
                                }
                            }]
                        },
                        animation: {
                            duration: 1000
                        }
                    }
                });
            }
            break;
            
        case 2: // Slide Financeiro
            if (charts.composicaoValorTotalChart) {
                const ctx = document.getElementById('presentation-financeiro-chart').getContext('2d');
                const config = charts.composicaoValorTotalChart.config;
                
                if (charts.presentationFinanceiroChart) {
                    charts.presentationFinanceiroChart.destroy();
                }
                
                charts.presentationFinanceiroChart = new Chart(ctx, {
                    type: config.type,
                    data: config.data,
                    options: {
                        ...config.options,
                        legend: {
                            labels: {
                                fontSize: 16
                            }
                        },
                        animation: {
                            duration: 1000
                        }
                    }
                });
            }
            break;
            
        case 3: // Slide Glosas
            if (charts.motivosGlosaChart) {
                const ctx = document.getElementById('presentation-glosas-chart').getContext('2d');
                const config = charts.motivosGlosaChart.config;
                
                if (charts.presentationGlosasChart) {
                    charts.presentationGlosasChart.destroy();
                }
                
                charts.presentationGlosasChart = new Chart(ctx, {
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
                        animation: {
                            duration: 1000
                        }
                    }
                });
            }
            break;
            
        case 4: // Slide Performance
            if (charts.tempoMedioPorTipoChart) {
                const ctx = document.getElementById('presentation-performance-chart').getContext('2d');
                const config = charts.tempoMedioPorTipoChart.config;
                
                if (charts.presentationPerformanceChart) {
                    charts.presentationPerformanceChart.destroy();
                }
                
                charts.presentationPerformanceChart = new Chart(ctx, {
                    type: config.type,
                    data: config.data,
                    options: {
                        ...config.options,
                        legend: {
                            labels: {
                                fontSize: 16
                            }
                        },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    fontSize: 14
                                }
                            }],
                            xAxes: [{
                                ticks: {
                                    fontSize: 14
                                }
                            }]
                        },
                        animation: {
                            duration: 1000
                        }
                    }
                });
            }
            break;
    }
}

// Funções para carregar dados financeiros
function carregarDadosFinanceiros(filtros) {
    $.ajax({
        url: '{{ route("graficos.financeiro") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            // Atualizar KPIs financeiros
            $('#kpi-valor-implantado').text(formatarValor(response.kpis.valor_implantado));
            $('#kpi-valor-pendente').text(formatarValor(response.kpis.valor_pendente));
            $('#kpi-valor-recursado').text(formatarValor(response.kpis.valor_recursado));
            $('#kpi-valor-glosado-final').text(formatarValor(response.kpis.valor_glosado_final));
            
            // Gráfico de composição do valor total
            renderizarComposicaoValorTotal(response.composicao);
            
            // Gráfico de evolução dos valores mensais
            renderizarEvolucaoValoresMensais(response.evolucao);
            
            // Gráfico de Top 5 OCS/PSA por valor
            renderizarTopOcsPsaValor(response.top_ocspsa);
            
            // Gráfico de distribuição por tipo de conta
            renderizarDistribuicaoTipoConta(response.tipo_conta);
        },
        error: function() {
            console.error('Erro ao carregar dados financeiros');
        }
    });
}

// Funções para carregar dados de glosas
function carregarDadosGlosas(filtros) {
    $.ajax({
        url: '{{ route("graficos.glosas") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            // Atualizar KPIs de glosas
            $('#kpi-valor-glosado').text(formatarValor(response.kpis.valor_glosado));
            $('#kpi-pacotes-glosados').text(response.kpis.pacotes_glosados.toLocaleString('pt-BR'));
            $('#kpi-taxa-recuperacao').text(response.kpis.taxa_recuperacao.toLocaleString('pt-BR', {maximumFractionDigits: 2}) + '%');
            $('#kpi-valor-irrecuperavel').text(formatarValor(response.kpis.valor_irrecuperavel));
            
            // Gráfico de motivos de glosa
            renderizarMotivosGlosa(response.motivos_glosa);
            
            // Gráfico de status dos recursos de glosa
            renderizarStatusRecursosGlosa(response.status_recursos);
            
            // Gráfico de OCS/PSA com maior taxa de glosa
            renderizarOcsTaxaGlosa(response.ocs_taxa_glosa);
            
            // Gráfico de tendência de glosas
            renderizarTendenciaGlosa(response.tendencia_glosa);
        },
        error: function() {
            console.error('Erro ao carregar dados de glosas');
        }
    });
}

// Funções para carregar dados de performance
function carregarDadosPerformance(filtros) {
    $.ajax({
        url: '{{ route("graficos.performance") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            // Atualizar KPIs de performance
            $('#kpi-tempo-medio-total').text(response.kpis.tempo_medio.toLocaleString('pt-BR', {maximumFractionDigits: 1}) + ' dias');
            $('#kpi-pacotes-finalizados').text(response.kpis.pacotes_finalizados.toLocaleString('pt-BR'));
            $('#kpi-pacotes-andamento').text(response.kpis.pacotes_andamento.toLocaleString('pt-BR'));
            $('#kpi-pacotes-atrasados').text(response.kpis.pacotes_atrasados.toLocaleString('pt-BR'));
            
            // Gráfico de tempo médio por tipo de pacote
            renderizarTempoMedioPorTipo(response.tempo_tipo);
            
            // Gráfico de performance por OCS/PSA
            renderizarPerformanceOcsPsa(response.performance_ocspsa);
            
            // Gráfico de tendência de tempo de processamento
            renderizarTendenciaTempo(response.tendencia_tempo);
            
            // Gráfico de meta vs realizado
            renderizarMetaRealizado(response.meta_realizado);
        },
        error: function() {
            console.error('Erro ao carregar dados de performance');
        }
    });
}

// Funções para carregar dados de desempenho operacional
function carregarDadosDesempenho(filtros) {
    $.ajax({
        url: '{{ route("graficos.desempenho") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            var melhorColaborador = '-';
            if (response.kpis && response.kpis.melhor_colaborador && response.kpis.melhor_colaborador.nome) {
                melhorColaborador = response.kpis.melhor_colaborador.nome;
            }

            $('#kpi-desempenho-media-score').text((response.kpis.media_score || 0).toLocaleString('pt-BR', {maximumFractionDigits: 1}));
            $('#kpi-desempenho-melhor-colaborador').text(melhorColaborador);
            $('#kpi-desempenho-total-movimentacoes').text((response.kpis.total_movimentacoes || 0).toLocaleString('pt-BR'));
            $('#kpi-desempenho-retrabalho-medio').text((response.kpis.retrabalho_medio || 0).toLocaleString('pt-BR', {maximumFractionDigits: 1}) + '%');

            renderizarDesempenhoRanking(response.ranking || { labels: [], values: [] });
            renderizarDesempenhoEixos(response.eixos || { labels: [], volume: [], tempo: [], qualidade: [], retrabalho: [] });
            renderizarDesempenhoRetrabalho(response.retrabalho || { labels: [], values: [] });
            renderizarDesempenhoHistorico(response.historico_mensal || { labels: [], valores: [] });
        },
        error: function() {
            console.error('Erro ao carregar dados de desempenho operacional');
        }
    });
}

$(document).on('click', '#btn-exportar-desempenho-csv', function(e) {
    e.preventDefault();
    var filtros = $('#form-filtros').serialize();
    var url = '{{ route("graficos.desempenho.exportar", ["tipo" => "csv"]) }}';
    if (filtros) {
        url += '?' + filtros;
    }
    window.open(url, '_blank');
});

$(document).on('click', '#btn-exportar-desempenho-pdf', function(e) {
    e.preventDefault();
    var filtros = $('#form-filtros').serialize();
    var url = '{{ route("graficos.desempenho.exportar", ["tipo" => "pdf"]) }}';
    if (filtros) {
        url += '?' + filtros;
    }
    window.open(url, '_blank');
});
</script>