<script>
// Objetos globais
let charts = {};

$(document).ready(function() {
    // Inicializar datepicker - Verificar se está sendo carregado corretamente
    if ($.fn.daterangepicker) {
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
    } else {
        console.error('DateRangePicker não está disponível');
    }

    // Inicializar select2 com configurações corretas
    try {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Selecione...',
            allowClear: true,
            width: '100%' // Garante que o select2 tenha a largura correta
        });
        
        // Força a recriação dos elementos Select2 caso necessário
        $('.select2').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
            $(this).select2({
                theme: 'bootstrap4',
                placeholder: 'Selecione...',
                allowClear: true,
                width: '100%'
            });
        });
    } catch (e) {
        console.error('Erro ao inicializar select2:', e);
    }

    // Carregar dados iniciais
    atualizarDashboard();

    // Event listeners
    $('#btn-atualizar-dados').on('click', function() {
        atualizarDashboard();
    });

    $('#btn-aplicar-filtros').on('click', function(e) {
        e.preventDefault();
        atualizarDashboard();
    });

    $('#btn-limpar-filtros').on('click', function(e) {
        e.preventDefault();
        $('#form-filtros')[0].reset();
        
        // Verificar se daterangepicker está disponível antes de usar
        const $periodo = $('#filtro-periodo');
        if ($periodo.length && $periodo.data('daterangepicker')) {
            $periodo.data('daterangepicker').setStartDate(moment().subtract(29, 'days'));
            $periodo.data('daterangepicker').setEndDate(moment());
        }
        
        // Verificar se select2 está disponível
        if ($.fn.select2) {
            $('.select2').val('todos').trigger('change');
        }
        
        atualizarDashboard();
    });

    // Navegação por abas - melhorado
    $('#dashboard-tabs a').on('shown.bs.tab', function(e) {
        const targetTab = $(e.target).attr("href").substring(1);
        console.log("Aba ativada: " + targetTab);
        
        // Verificar se há elementos específicos da aba que precisamos verificar
        let elementosNecessarios = [];
        
        switch (targetTab) {
            case 'fluxo':
                elementosNecessarios = ['tempoEtapaChart', 'volumeEtapaChart', 'fluxo-diagrama'];
                break;
            case 'financeiro':
                elementosNecessarios = ['composicaoValorTotalChart', 'evolucaoValoresMensaisChart', 'topOcsPsaValorChart', 'tipoContaChart'];
                break;
            // Adicione outras abas conforme necessário
        }
        
        // Verificar se os elementos necessários existem
        let elementosFaltantes = elementosNecessarios.filter(id => !document.getElementById(id));
        if (elementosFaltantes.length > 0) {
            console.warn(`Atenção: Os seguintes elementos estão faltando na aba ${targetTab}: ${elementosFaltantes.join(', ')}`);
        }
        
        atualizarAba(targetTab);
    });

    // Modo apresentação
    $('#btn-apresentacao').on('click', function() {
        // Verificar se o modal está disponível
        if ($.fn.modal) {
            $('#modal-apresentacao').modal('show');
        } else {
            console.error('Modal não está disponível. Bootstrap JS não foi carregado corretamente.');
            alert('Não foi possível abrir o modo de apresentação. Tente recarregar a página.');
        }
    });
});

// Função para ativar uma aba programaticamente
function activateTab(tabName) {
    $('#tab-' + tabName).tab('show');
}

// Função para obter os filtros do formulário
function obterFiltros() {
    return {
        periodo: $('#filtro-periodo').val(),
        ocs_psa_id: $('#filtro-ocspsa').val(),
        tipo_id: $('#filtro-tipo').val(),
        tipo_conta_id: $('#filtro-tipo-conta').val(),
        estado_glosa: $('#filtro-estado-glosa').val()
    };
}

// Função para atualizar o texto de filtros ativos
function atualizarTextoFiltros(filtros) {
    let textoFiltros = [];
    
    if (filtros.periodo) {
        textoFiltros.push(`Período: ${filtros.periodo}`);
    }
    
    if (filtros.ocs_psa_id && filtros.ocs_psa_id !== 'todos') {
        const text = $('#filtro-ocspsa option:selected').text();
        textoFiltros.push(`OCS/PSA: ${text}`);
    }
    
    if (filtros.tipo_id && filtros.tipo_id !== 'todos') {
        const text = $('#filtro-tipo option:selected').text();
        textoFiltros.push(`Tipo: ${text}`);
    }
    
    if (filtros.tipo_conta_id && filtros.tipo_conta_id !== 'todos') {
        const text = $('#filtro-tipo-conta option:selected').text();
        textoFiltros.push(`Tipo Conta: ${text}`);
    }
    
    if (filtros.estado_glosa && filtros.estado_glosa !== 'todos') {
        const text = $('#filtro-estado-glosa option:selected').text();
        textoFiltros.push(`Estado Glosa: ${text}`);
    }
    
    $('#filtros-ativos').text(textoFiltros.length > 0 ? textoFiltros.join(' | ') : 'Últimos 30 dias');
}

// Função para atualizar todo o dashboard
function atualizarDashboard() {
    const filtros = obterFiltros();
    atualizarTextoFiltros(filtros);
    
    // Mostrar indicador de carregamento
    Swal.fire({
        title: 'Carregando dados...',
        text: 'Por favor, aguarde enquanto atualizamos as informações',
        didOpen: () => {
            Swal.showLoading();
        },
        allowOutsideClick: false,
        allowEscapeKey: false
    });
    
    // Carregar KPIs principais
    $.ajax({
        url: '{{ route("graficos.kpis") }}',
        method: 'GET',
        data: filtros,
        success: function(response) {
            // Atualizar valores dos KPIs
            $('#kpi-total-pacotes').text(response.total_pacotes.toLocaleString('pt-BR'));
            $('#kpi-valor-total-faturas').text('R$ ' + response.valor_total_faturas.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#kpi-taxa-media-glosa').text(response.taxa_media_glosa.toLocaleString('pt-BR', {maximumFractionDigits: 2}) + '%');
            $('#kpi-tempo-medio-dias').text(response.tempo_medio_dias.toLocaleString('pt-BR', {maximumFractionDigits: 1}));
            
            // Atualiza a aba ativa
            const activeTab = $('#dashboard-tabs .nav-link.active').attr('href').substring(1);
            atualizarAba(activeTab);
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Erro ao carregar dados',
                text: 'Não foi possível atualizar o dashboard. Tente novamente mais tarde.'
            });
        },
        complete: function() {
            Swal.close();
        }
    });
}

// Função para atualizar uma aba específica
function atualizarAba(nomeAba) {
    const filtros = obterFiltros();
    
    console.log(`Atualizando aba: ${nomeAba}`);
    
    switch (nomeAba) {
        case 'visao-geral':
            carregarDadosVisaoGeral(filtros);
            break;
        case 'fluxo':
            carregarDadosFluxo(filtros);
            break;
        case 'financeiro':
            carregarDadosFinanceiros(filtros);
            break;
        case 'glosa':
            carregarDadosGlosas(filtros);
            break;
        case 'performance':
            carregarDadosPerformance(filtros);
            break;
        default:
            console.warn(`Aba desconhecida: ${nomeAba}`);
    }
}

// Função para formatar valores monetários
function formatarValor(valor) {
    return valor.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
</script>