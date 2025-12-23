@extends('adminlte::page')

@section('title', 'Anulação de Pacotes')

@section('content_header')
    <h1><i class="fas fa-ban text-danger"></i> Anulação de Pacotes</h1>
@stop

@section('content')
<div class="container-fluid">
    
    <!-- Seção de Busca e Anulação -->
    <div class="row">
        <div class="col-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-search"></i> Buscar Pacote para Anulação</h3>
                </div>
                <div class="card-body">
                    <form id="form-buscar-pacote">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="id-pacote">ID do Pacote:</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control" 
                                               id="id-pacote" 
                                               name="id_pacote" 
                                               placeholder="Digite o ID do pacote (ex: 998)" 
                                               min="1"
                                               required>
                                        <div class="input-group-append">
                                            <button type="button" 
                                                    class="btn btn-primary" 
                                                    id="btn-buscar-pacote">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Informe o ID do pacote para verificar se pode ser anulado
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button type="button" 
                                                class="btn btn-secondary btn-block" 
                                                id="btn-limpar-busca">
                                            <i class="fas fa-eraser"></i> Limpar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Detalhes do Pacote Encontrado -->
    <div class="row" id="secao-pacote-encontrado" style="display: none;">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Detalhes do Pacote</h3>
                </div>
                <div class="card-body">
                    <div class="row" id="detalhes-pacote">
                        <!-- Conteúdo será preenchido via JavaScript -->
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="button" 
                                    class="btn btn-danger btn-lg" 
                                    id="btn-anular-pacote"
                                    data-pacote-id="">
                                <i class="fas fa-ban"></i> Anular Este Pacote
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de Pacotes Anulados -->
    <div class="row">
        <div class="col-12">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Histórico de Pacotes Anulados</h3>
                    <div class="card-tools">
                        <button type="button" 
                                class="btn btn-tool" 
                                id="btn-recarregar-anulados">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tabela-pacotes-anulados">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID Pacote</th> <!-- NOVA COLUNA -->
                                    <th>Nº Fatura</th>
                                    <th>OCS/PSA</th>
                                    <th>Valor Fatura</th>
                                    <th>Data Anulação</th>
                                    <th>Usuário</th>
                                    <th>Motivo</th>
                                    <th>Localização</th> <!-- NOVA COLUNA -->
                                    <th>Estado</th> <!-- NOVA COLUNA -->
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Conteúdo será carregado via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Anulação -->
<div class="modal fade" id="modal-confirmar-anulacao" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle"></i> Confirmar Anulação de Pacote
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-warning"></i> 
                    <strong>Atenção!</strong> Esta ação é <strong>irreversível</strong>. 
                    O pacote será marcado como anulado permanentemente.
                </div>
                
                <p><strong>Pacote a ser anulado:</strong></p>
                <ul id="resumo-pacote-anulacao">
                    <!-- Preenchido via JavaScript -->
                </ul>

                <form id="form-anular-pacote">
                    <div class="form-group">
                        <label for="motivo-anulacao">
                            <strong>Motivo da Anulação:</strong> <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="motivo-anulacao" 
                                  name="motivo_anulacao" 
                                  rows="4" 
                                  placeholder="Descreva detalhadamente o motivo da anulação..."
                                  required
                                  minlength="10"
                                  maxlength="500"></textarea>
                        <small class="form-text text-muted">
                            Mínimo 10 caracteres, máximo 500 caracteres
                        </small>
                    </div>
                    <input type="hidden" id="pacote-id-anulacao" name="id_pacote" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-anulacao">
                    <i class="fas fa-ban"></i> Confirmar Anulação
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Visualizar Motivo -->
<div class="modal fade" id="modal-ver-motivo" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title text-white">
                    <i class="fas fa-eye"></i> Motivo da Anulação
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="conteudo-motivo"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .card-primary .card-header {
            background-color: #007bff;
            border-color: #007bff;
        }
        .card-info .card-header {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .card-danger .card-header {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }
        .badge-danger {
            background-color: #dc3545 !important;
        }
        #secao-pacote-encontrado {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configurar CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // ATUALIZAR: DataTable com coluna motivo simplificada
            var tabelaAnulados = $('#tabela-pacotes-anulados').DataTable({
                "language": {
                    "sEmptyTable": "Nenhum registro encontrado",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                    "sInfoThousands": ".",
                    "sLengthMenu": "_MENU_ resultados por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing": "Processando...",
                    "sZeroRecords": "Nenhum registro encontrado",
                    "sSearch": "Pesquisar",
                    "oPaginate": {
                        "sNext": "Próximo",
                        "sPrevious": "Anterior",
                        "sFirst": "Primeiro",
                        "sLast": "Último"
                    },
                    "oAria": {
                        "sSortAscending": ": Ordenar colunas de forma ascendente",
                        "sSortDescending": ": Ordenar colunas de forma descendente"
                    }
                },
                "pageLength": 10,
                "order": [[ 4, "desc" ]],
                "columnDefs": [
                    { "orderable": false, "targets": [6, 9] }
                ],
                "ajax": {
                    "url": "{{ url('configuracoes/anulacao/listar') }}",
                    "type": "GET",
                    "dataSrc": ""
                },
                "columns": [
                    // ID do Pacote
                    { 
                        "data": "id",
                        "render": function(data) {
                            return '<span class="badge badge-danger font-weight-bold">#' + data + '</span>';
                        }
                    },
                    { "data": "numero_fatura" },
                    { "data": "ocs_psa" },
                    { 
                        "data": "valor_fatura",
                        "render": function(data) {
                            return 'R$ ' + parseFloat(data).toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                    },
                    { "data": "data_anulacao" },
                    { "data": "usuario_anulacao" },
                    // CORREÇÃO 1: Motivo simplificado - apenas botão Ver
                    {
                        "data": "motivo_anulacao",
                        "render": function(data) {
                            return '<button class="btn btn-sm btn-outline-info btn-ver-motivo" data-motivo="' + 
                                   data.replace(/"/g, '&quot;') + '" title="Clique para ver o motivo completo">' +
                                   '<i class="fas fa-eye"></i> Ver</button>';
                        }
                    },
                    // Localização
                    {
                        "data": "localizacao_atual",
                        "render": function(data) {
                            return '<span class="badge badge-secondary">' + data + '</span>';
                        }
                    },
                    // Estado
                    {
                        "data": "estado_geral",
                        "render": function(data) {
                            return '<span class="badge badge-warning">' + data + '</span>';
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return '<a href="/configuracoes/anulacao/ver/' + row.id + '" class="btn btn-info btn-sm" target="_blank"><i class="fas fa-eye"></i> Ver Auditoria</a>';
                        }
                    }
                ]
            });

            // Buscar pacote
            $('#btn-buscar-pacote').click(function() {
                var idPacote = $('#id-pacote').val();
                
                if (!idPacote) {
                    Swal.fire({
                        title: 'Atenção',
                        text: 'Por favor, informe o ID do pacote.',
                        icon: 'warning'
                    });
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');

                $.ajax({
                    url: "{{ url('configuracoes/anulacao/buscar-pacote') }}/" + idPacote,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            exibirDetalhesPacote(response.pacote);
                        } else {
                            Swal.fire({
                                title: 'Pacote não encontrado',
                                text: response.message,
                                icon: 'error'
                            });
                            $('#secao-pacote-encontrado').hide();
                        }
                    },
                    error: function(xhr) {
                        var message = 'Erro ao buscar pacote.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Erro',
                            text: message,
                            icon: 'error'
                        });
                        $('#secao-pacote-encontrado').hide();
                    },
                    complete: function() {
                        $('#btn-buscar-pacote').prop('disabled', false).html('<i class="fas fa-search"></i> Buscar');
                    }
                });
            });

            // Limpar busca
            $('#btn-limpar-busca').click(function() {
                $('#form-buscar-pacote')[0].reset();
                $('#secao-pacote-encontrado').hide();
            });

            // Anular pacote (abrir modal)
            $(document).on('click', '#btn-anular-pacote', function() {
                var pacoteId = $(this).data('pacote-id');
                $('#pacote-id-anulacao').val(pacoteId);
                $('#modal-confirmar-anulacao').modal('show');
            });

            // Confirmar anulação
            $('#btn-confirmar-anulacao').click(function() {
                var motivo = $('#motivo-anulacao').val();
                var pacoteId = $('#pacote-id-anulacao').val();

                if (!motivo || motivo.length < 10) {
                    Swal.fire({
                        title: 'Atenção',
                        text: 'Por favor, informe o motivo da anulação (mínimo 10 caracteres).',
                        icon: 'warning'
                    });
                    return;
                }

                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Anulando...');

                $.ajax({
                    url: "{{ route('configuracoes.anulacao.anular') }}",
                    type: 'POST',
                    data: {
                        id_pacote: pacoteId,
                        motivo_anulacao: motivo
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#modal-confirmar-anulacao').modal('hide');
                            $('#form-anular-pacote')[0].reset();
                            $('#secao-pacote-encontrado').hide();
                            $('#form-buscar-pacote')[0].reset();
                            
                            Swal.fire({
                                title: 'Sucesso!',
                                text: response.message,
                                icon: 'success'
                            });

                            // Recarregar tabela
                            tabelaAnulados.ajax.reload();
                        } else {
                            Swal.fire({
                                title: 'Erro',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        var message = 'Erro ao anular pacote.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Erro',
                            text: message,
                            icon: 'error'
                        });
                    },
                    complete: function() {
                        $('#btn-confirmar-anulacao').prop('disabled', false).html('<i class="fas fa-ban"></i> Confirmar Anulação');
                    }
                });
            });

            // Ver motivo da anulação
            $(document).on('click', '.btn-ver-motivo', function() {
                var motivo = $(this).data('motivo');
                $('#conteudo-motivo').text(motivo);
                $('#modal-ver-motivo').modal('show');
            });

            // Recarregar tabela de anulados
            $('#btn-recarregar-anulados').click(function() {
                tabelaAnulados.ajax.reload();
                $(this).find('i').addClass('fa-spin');
                setTimeout(() => {
                    $(this).find('i').removeClass('fa-spin');
                }, 1000);
            });

            // Enter para buscar
            $('#id-pacote').keypress(function(e) {
                if (e.which == 13) {
                    $('#btn-buscar-pacote').click();
                }
            });

            // Função para exibir detalhes do pacote
            function exibirDetalhesPacote(pacote) {
                var html = `
                    <div class="col-md-3">
                        <p><strong>ID do Pacote:</strong><br><span class="badge badge-primary">${pacote.id}</span></p>
                        <p><strong>Número da Fatura:</strong><br>${pacote.numero_fatura}</p>
                        <p><strong>OCS/PSA:</strong><br>${pacote.ocs_psa}</p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Valor da Fatura:</strong><br>R$ ${pacote.valor_fatura}</p>
                        <p><strong>Data de Entrada:</strong><br>${pacote.data_entrada}</p>
                        <p><strong>Localização Atual:</strong><br>${pacote.localizacao_atual}</p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Estado Geral:</strong><br>${pacote.estado_geral}</p>
                        <p><strong>Estado da Glosa:</strong><br>${pacote.estado_glosa}</p>
                        <p><strong>Tipo de Pacote:</strong><br>${pacote.tipo_pacote}</p>
                    </div>
                    <div class="col-md-3">
                        <p><strong>Tipo de Conta:</strong><br>${pacote.tipo_conta}</p>
                        <p><strong>Valor Pendente:</strong><br>R$ ${pacote.valor_pendente}</p>
                        <p><strong>Valor Glosa:</strong><br>R$ ${pacote.valor_glosa}</p>
                    </div>
                `;

                $('#detalhes-pacote').html(html);
                $('#btn-anular-pacote').data('pacote-id', pacote.id);
                
                // Atualizar resumo do modal
                $('#resumo-pacote-anulacao').html(`
                    <li><strong>ID:</strong> ${pacote.id}</li>
                    <li><strong>Número da Fatura:</strong> ${pacote.numero_fatura}</li>
                    <li><strong>OCS/PSA:</strong> ${pacote.ocs_psa}</li>
                    <li><strong>Valor:</strong> R$ ${pacote.valor_fatura}</li>
                `);

                $('#secao-pacote-encontrado').show();
            }

            // Carregar tabela inicial
            tabelaAnulados.ajax.reload();
        });
    </script>
@stop