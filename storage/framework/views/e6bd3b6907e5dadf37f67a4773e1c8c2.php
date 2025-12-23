<?php $__env->startSection('title', 'Visualizar Pacote'); ?>

<?php $__env->startSection('content_header'); ?>
    <h1>
        Detalhes do Pacote #<?php echo e($pacote->id ?? request('id')); ?>

        <div class="float-right">
            <?php if(Auth::user()->role == 'admin' || Auth::user()->role == 'protocolo'): ?>
                <button id="criar-pacote" class="btn btn-primary btn-sm mr-2">
                    <i class="fas fa-plus"></i> Criar Novo Pacote
                </button>
            <?php endif; ?>
            <a href="<?php echo e(route('pacotes.index')); ?>" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo e(url('/')); ?>">Início</a></li>
        <li class="breadcrumb-item"><a href="<?php echo e(route('pacotes.index')); ?>">Pacotes</a></li>
        <li class="breadcrumb-item active">Pacote #<?php echo e($pacote->id ?? request('id')); ?></li>
    </ol>

    <div class="row">
        <!-- Informações Básicas -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Informações Básicas</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Nº do Pacote</dt>
                        <dd><?php echo e($pacote->id ?? 'Não definido'); ?></dd>
                        
                        <dt>OCS/PSA</dt>
                        <dd><?php echo e(optional($pacote->ocsPsa)->nome ?? 'Não definido'); ?></dd>
                        
                        <dt>Data da Entrada no Protocolo</dt>
                        <dd><?php echo e(optional($pacote->data_entrada)->format('d/m/Y') ?? 'Não definido'); ?></dd>
                        
                        <dt>Número da Fatura</dt>
                        <dd><?php echo e($pacote->numero_fatura ?? 'Não definido'); ?></dd>
                        
                        <dt>Tipo</dt>
                        <dd><?php echo e(optional($pacote->tipoPacote)->nome ?? 'Não definido'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Valores -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title">Valores</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Valor da Fatura</dt>
                        <dd>R$ <?php echo e(number_format($pacote->valor_fatura, 2, ',', '.')); ?></dd>
                        
                        <dt>Glosa</dt>
                        <dd>R$ <?php echo e(number_format($pacote->valor_glosa, 2, ',', '.')); ?></dd>
                        
                        <dt>Valor Pós Lisura</dt>
                        <dd>R$ <?php echo e(number_format($pacote->valor_pos_lisura, 2, ',', '.')); ?></dd>
                        
                        <dt>Valor Implantado</dt>
                        <dd>R$ <?php echo e(number_format($pacote->valor_pago, 2, ',', '.')); ?></dd>
                        
                        <dt>Valor Pendente</dt>
                        <dd>R$ <?php echo e(number_format($pacote->valor_pendente, 2, ',', '.')); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Detalhes de Glosa -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-warning">
                    <h3 class="card-title">Detalhes de Glosa</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Tipo de Conta</dt>
                        <dd><?php echo e(optional($pacote->tipoConta)->nome ?? 'Não informado'); ?></dd>
                        
                        <dt>Motivo da Glosa</dt>
                        <dd><?php echo e($pacote->motivo_glosa ?? 'Não informado'); ?></dd>
                        
                        <dt>Recurso de Glosa</dt>
                        <dd>R$ <?php echo e(number_format($pacote->valor_recurso_glosa ?? 0, 2, ',', '.')); ?></dd>
                        
                        <dt>Valor Recursado</dt>
                        <dd>R$ <?php echo e(number_format($pacote->valor_recursado ?? 0, 2, ',', '.')); ?></dd>
                        
                        <dt>Valor Deferido</dt>
                        <dd>R$ <?php echo e(number_format($pacote->valor_deferido ?? 0, 2, ',', '.')); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Estados/Localização -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title">Estados/Localização</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Estado Geral</dt>
                        <dd><?php echo e($pacote->estado_geral); ?></dd>
                        
                        <dt>Estado da Glosa</dt>
                        <dd><?php echo e($pacote->estado_glosa); ?></dd>
                        
                        <dt>Localização Atual</dt>
                        <dd><?php echo e(ucfirst($pacote->localizacao_atual)); ?></dd>
                        
                        <dt>Localização Anterior</dt>
                        <dd><?php echo e(ucfirst($pacote->localizacao_anterior)); ?></dd>
                        
                        <dt>Última Ação</dt>
                        <dd><?php echo e($pacote->ultima_acao); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Progress Bar do Fluxo, Gráfico de Pagamento e Informação de Arquivo -->
    <div class="row mb-4">
        <div class="col-md-6">
            <!-- Progresso do Pacote no Fluxo de Trabalho -->
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Progresso do Pacote no Fluxo de Trabalho</h3>
                </div>
                <div class="card-body">
                    <div class="progress-stages">
                        <div class="row text-center">
                            <?php
                                // Definir a ordem do fluxo de trabalho
                                $fluxoTrabalho = ['protocolo', 'lisura', 'sire', 'glosa', 'arquivo'];
                                
                                // Obter a posição da localização atual no fluxo
                                $posicaoAtual = array_search($pacote->localizacao_atual, $fluxoTrabalho);
                                
                                // Se a localização atual não estiver no fluxo (improvável), definir como -1 para segurança
                                if ($posicaoAtual === false) {
                                    $posicaoAtual = -1;
                                }
                                
                                // Calcular a porcentagem de progresso baseado na posição atual
                                $totalEtapas = count($fluxoTrabalho);
                                $percentualProgresso = ($posicaoAtual + 1) / $totalEtapas * 100;
                            ?>
                            
                            <!-- Protocolo -->
                            <div class="col">
                                <div class="stage <?php echo e($posicaoAtual >= 0 ? 'active' : ''); ?>">
                                    <i class="fas fa-sign-in-alt"></i>
                                </div>
                                <div>Protocolo</div>
                            </div>
                            
                            <!-- Lisura -->
                            <div class="col">
                                <div class="stage <?php echo e($posicaoAtual >= 1 ? 'active' : ''); ?>">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>Lisura</div>
                            </div>
                            
                            <!-- SIRE -->
                            <div class="col">
                                <div class="stage <?php echo e($posicaoAtual >= 2 ? 'active' : ''); ?>">
                                    <i class="fas fa-money-bill-alt"></i>
                                </div>
                                <div>SIRE</div>
                            </div>
                            
                            <!-- Glosa -->
                            <div class="col">
                                <div class="stage <?php echo e($posicaoAtual >= 3 ? 'active' : ''); ?>">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div>Glosa</div>
                            </div>
                            
                            <!-- Arquivo -->
                            <div class="col">
                                <div class="stage <?php echo e($posicaoAtual >= 4 ? 'active' : ''); ?>">
                                    <i class="fas fa-archive"></i>
                                </div>
                                <div>Arquivo</div>
                            </div>
                        </div>
                        
                        <!-- Barra de progresso horizontal -->
                        <div class="progress mt-3">
                            <div class="progress-bar bg-primary progress-bar-striped" role="progressbar" 
                                 style="width: <?php echo e($percentualProgresso); ?>%" 
                                 aria-valuenow="<?php echo e($percentualProgresso); ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo e(round($percentualProgresso)); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Div para o gráfico circular de Pagamento -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Situação do Pagamento</h3>
                </div>
                <div class="card-body text-center">
                    <?php
                        $valorTotal = $pacote->valor_pos_lisura + $pacote->valor_deferido; // Alterado para somar Valor Pós Lisura e Valor Deferido
                        $percentualPago = ($valorTotal > 0) ? round(($pacote->valor_pago / $valorTotal) * 100) : 0;
                    ?>
                    <input type="text" class="knob" value="<?php echo e($percentualPago); ?>" data-thickness="0.2" data-width="90" 
                           data-height="90" data-fgColor="#28a745" data-readonly="true">
                    
                    <div class="mt-3">
                        <span>
                            R$ <?php echo e(number_format($pacote->valor_pago, 2, ',', '.')); ?> 
                            de R$ <?php echo e(number_format($valorTotal, 2, ',', '.')); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Nova div para informação de Arquivo -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Arquivo</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Localização Física:</dt>
                        <dd><?php echo e($pacote->localizacao_fisica ?? 'Não informada'); ?></dd>
                    </dl>
                    
                    <?php if($pacote->localizacao_atual == 'arquivo' || $pacote->localizacao_atual == 'arquivado'): ?>
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-info-circle"></i> 
                            <?php if($pacote->localizacao_atual == 'arquivo'): ?>
                                Pacote está aguardando arquivamento
                            <?php else: ?>
                                Pacote está arquivado definitivamente
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botões de ação -->
    <div class="row mb-4">
        <div class="col-md-12">
            
            <?php if((Auth::user()->role == 'admin' || 
                (Auth::user()->role == $pacote->localizacao_atual && Auth::user()->role != 'glosa' && Auth::user()->role != 'arquivo'))): ?>
                <a href="#" class="btn btn-success btn-mover-pacote" 
                   data-id="<?php echo e($pacote->id); ?>" 
                   data-localizacao="<?php echo e($pacote->localizacao_atual); ?>">
                    <i class="fas fa-arrow-right"></i> Mover Pacote
                </a>
            <?php endif; ?>
            
            
            <?php if(Auth::user()->role == 'admin' || 
               ((Auth::user()->role == 'protocolo' || Auth::user()->role == 'lisura') && 
                (Auth::user()->role == $pacote->localizacao_atual))): ?>
                <a href="<?php echo e(route('pacotes.edit', $pacote->id)); ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Pacote
                </a>
            <?php endif; ?>
            
            
            <a href="<?php echo e(route('pacotes.movimentacoes', $pacote->id)); ?>" class="btn btn-info">
                <i class="fas fa-history"></i> Movimentações
            </a>
            
            
            <a href="<?php echo e(route('pacotes.prazos', $pacote->id)); ?>" class="btn btn-warning">
                <i class="fas fa-calendar-alt"></i> Prazos e Notificações
            </a>

            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'protocolo') && 
                $pacote->estado_glosa == 'Aguardando Recurso de Glosa'): ?>
                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalRecebimentoRecurso" 
                   data-id="<?php echo e($pacote->id); ?>">
                    <i class="fas fa-file-import"></i> Recebimento de Recurso de Glosa
                </a>
            <?php endif; ?>

            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'glosa') && 
                $pacote->localizacao_atual == 'glosa' && 
                $pacote->data_notificacao_glosa == null): ?>
                <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modalNotificarGlosa" 
                   data-id="<?php echo e($pacote->id); ?>">
                    <i class="fas fa-bell"></i> Notificação de Existência de Glosa
                </a>
            <?php endif; ?>
            
            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'glosa') && 
                $pacote->localizacao_atual == 'glosa' && 
                $pacote->data_notificacao_glosa != null &&
                $pacote->data_retirada_oficio == null): ?>
                <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modalRetiradaOficioGlosa" 
                   data-id="<?php echo e($pacote->id); ?>">
                    <i class="fas fa-file-download"></i> Retirada de Ofício de Glosa
                </a>
            <?php endif; ?>

            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'glosa') && 
                $pacote->estado_glosa == 'Aguardando Recurso de Glosa'): ?>
                <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#modalRecursoNaoRecebido" 
                   data-id="<?php echo e($pacote->id); ?>">
                    <i class="fas fa-ban"></i> Recurso Não Recebido
                </a>
            <?php endif; ?>

            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'glosa') && 
                $pacote->estado_glosa == 'Recurso recebido'): ?>
                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalAnaliseRecurso" 
                   data-id="<?php echo e($pacote->id); ?>">
                    <i class="fas fa-balance-scale"></i> Análise de Recurso
                </a>
            <?php endif; ?>

            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'sire') && 
                ($pacote->localizacao_atual == 'sire' || $pacote->localizacao_atual == 'glosa') && 
                $pacote->valor_pendente > 0): ?>
                <a href="#" class="btn btn-success" data-toggle="modal" data-target="#modalInformarPagamento" 
                   data-id="<?php echo e($pacote->id); ?>" data-valor-pendente="<?php echo e($pacote->valor_pendente); ?>">
                    <i class="fas fa-money-bill-wave"></i> Implantar Pagamento
                </a>
            <?php endif; ?>

            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'sire') && 
                ($pacote->localizacao_atual == 'sire' || $pacote->localizacao_atual == 'glosa') && 
                $pacote->valor_pendente > 0): ?>
                <a href="#" class="btn btn-warning" data-toggle="modal" data-target="#modalAguardandoLimite" 
                   data-id="<?php echo e($pacote->id); ?>">
                    <i class="fas fa-clock"></i> Aguardando Limite de Crédito
                </a>
            <?php endif; ?>

            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'arquivo') && 
                $pacote->localizacao_atual == 'arquivo'): ?>
                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#modalArquivar" 
                   data-id="<?php echo e($pacote->id); ?>">
                    <i class="fas fa-archive mr-2"></i> Arquivar Pacote
                </a>
            <?php endif; ?>

            
            <?php if((Auth::user()->role == 'admin' || Auth::user()->role == 'arquivo') && 
                ($pacote->localizacao_atual == 'arquivo' || $pacote->localizacao_atual == 'arquivado')): ?>
                <a href="#" class="btn btn-info" data-toggle="modal" data-target="#modalEditarLocalizacaoFisica" 
                   data-id="<?php echo e($pacote->id); ?>">
                    <i class="fas fa-map-marker-alt mr-2"></i> Editar Localização Física
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal de Confirmação para Mover Pacote -->
    <div class="modal fade" id="modalMoverPacote" tabindex="-1" role="dialog" aria-labelledby="modalMoverPacoteLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMoverPacoteLabel">Confirmar Encaminhamento do Pacote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Você está movendo o pacote que está em <strong><span id="localAtual"></span></strong> para <strong><span id="localDestino"></span></strong>.</p>
                    
                    <form id="formMoverPacote" action="" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" id="modalDestino" name="destino">
                        <div class="form-group">
                            <label for="observacao">Observação (opcional)</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" form="formMoverPacote">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Implantar Pagamento -->
    <div class="modal fade" id="modalInformarPagamento" tabindex="-1" role="dialog" aria-labelledby="modalInformarPagamentoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formInformarPagamento" action="" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalInformarPagamentoLabel">Implantar Pagamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="valor_pagamento">Valor do Pagamento (R$):</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_pagamento" name="valor_pagamento" 
                                       required maxlength="15">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btnPreencherTotal">Valor Total</button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Valor Pendente: R$ <span id="valorPendenteText"></span>
                            </small>
                        </div>
                        <div class="form-group">
                            <label for="observacao">Observação (opcional):</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3" 
                                      placeholder="Informe uma observação sobre este pagamento..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Confirmar Pagamento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Aguardando Limite de Crédito -->
    <div class="modal fade" id="modalAguardandoLimite" tabindex="-1" role="dialog" aria-labelledby="modalAguardandoLimiteLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formAguardandoLimite" action="<?php echo e(route('pacotes.aguardar-limite', $pacote->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAguardandoLimiteLabel">Aguardando Limite de Crédito</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Confirma que o pacote #<?php echo e($pacote->id); ?> está aguardando limite de crédito?</p>
                        <div class="form-group">
                            <label for="observacao_aguardando_limite">Observação (opcional):</label>
                            <textarea class="form-control" id="observacao_aguardando_limite" name="observacao" rows="3" 
                                      placeholder="Informe detalhes adicionais..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Notificação de Existência de Glosa -->
    <div class="modal fade" id="modalNotificarGlosa" tabindex="-1" role="dialog" aria-labelledby="modalNotificarGlosaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formNotificarGlosa" action="<?php echo e(route('pacotes.notificar-glosa', $pacote->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNotificarGlosaLabel">Notificar Existência de Glosa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Novo campo: Data da Notificação -->
                        <div class="form-group">
                            <label for="data_notificacao">Data da Notificação:</label>
                            <div class="input-group date" id="data_notificacao_div" data-target-input="nearest">
                                <input type="text" id="data_notificacao" name="data_notificacao" class="form-control datetimepicker-input" data-target="#data_notificacao_div" required />
                                <div class="input-group-append" data-target="#data_notificacao_div" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="meio_notificacao">Meio de Notificação</label>
                            <select class="form-control" id="meio_notificacao" name="meio_notificacao" required>
                                <option value="">Selecione...</option>
                                <option value="E-mail">E-mail</option>
                                <option value="Telefone">Telefone</option>
                                <option value="Correspondência">Correspondência</option>
                                <option value="Presencialmente">Presencialmente</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="detalhes_notificacao">Detalhes da Notificação</label>
                            <textarea class="form-control" id="detalhes_notificacao" name="detalhes_notificacao" 
                                      rows="3" placeholder="Informe detalhes sobre a notificação (contato, data, hora, etc)" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="data_limite_retirada">Prazo para Retirada do Ofício</label>
                            <div class="input-group date" id="data_limite_div" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" id="data_limite_retirada" 
                                       name="data_limite_retirada" data-target="#data_limite_div"
                                       value="<?php echo e(date('d/m/Y', strtotime('+30 days'))); ?>" required>
                                <div class="input-group-append" data-target="#data_limite_div" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Por padrão, a OCS/PSA tem 30 dias para retirar o Ofício de Glosa, contados a partir de hoje. Você pode modificar este prazo se necessário.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Notificação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Retirada de Ofício de Glosa -->
    <div class="modal fade" id="modalRetiradaOficioGlosa" tabindex="-1" role="dialog" aria-labelledby="modalRetiradaOficioGlosaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formRetiradaOficioGlosa" action="<?php echo e(route('pacotes.retirada-oficio-glosa', $pacote->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRetiradaOficioGlosaLabel">Registrar Retirada de Ofício de Glosa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Informe a data em que a OCS/PSA <strong><?php echo e(optional($pacote->ocsPsa)->nome); ?></strong> retirou o ofício de glosa:</p>
                        
                        <div class="form-group">
                            <label for="data_retirada_oficio">Data de Retirada do Ofício</label>
                            <div class="input-group date" id="data_retirada_div" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" id="data_retirada_oficio" 
                                       name="data_retirada_oficio" data-target="#data_retirada_div"
                                       value="<?php echo e(date('d/m/Y')); ?>" required>
                                <div class="input-group-append" data-target="#data_retirada_div" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Informe a data em que o ofício de glosa foi retirado pela OCS/PSA.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacao_retirada">Observações (opcional)</label>
                            <textarea class="form-control" id="observacao_retirada" name="observacao" 
                                      rows="2" placeholder="Informe observações adicionais, se necessário"></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> O prazo para apresentação de recurso é de 30 dias, contados a partir da data de retirada do ofício.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Retirada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Recebimento de Recurso de Glosa -->
    <div class="modal fade" id="modalRecebimentoRecurso" tabindex="-1" role="dialog" aria-labelledby="modalRecebimentoRecursoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formRecebimentoRecurso" action="<?php echo e(route('pacotes.recebimento-recurso', $pacote->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalRecebimentoRecursoLabel">Registrar Recebimento de Recurso de Glosa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Informe a data em que o recurso de glosa foi recebido da OCS/PSA <strong><?php echo e(optional($pacote->ocsPsa)->nome); ?></strong>:</p>
                        
                        <div class="form-group">
                            <label for="data_recebimento_recurso">Data de Recebimento do Recurso</label>
                            <div class="input-group date" id="data_recebimento_div" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" id="data_recebimento_recurso" 
                                       name="data_recebimento_recurso" data-target="#data_recebimento_div"
                                       value="<?php echo e(date('d/m/Y')); ?>" required>
                                <div class="input-group-append" data-target="#data_recebimento_div" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Informe a data em que o recurso de glosa foi recebido.
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacao_recebimento">Observações (opcional)</label>
                            <textarea class="form-control" id="observacao_recebimento" name="observacao" 
                                      rows="2" placeholder="Informe observações adicionais, se necessário"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Recebimento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Recurso Não Recebido -->
    <div class="modal fade" id="modalRecursoNaoRecebido" tabindex="-1" role="dialog" aria-labelledby="modalRecursoNaoRecebidoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRecursoNaoRecebidoLabel">Confirmar Recurso Não Recebido</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formRecursoNaoRecebido" action="<?php echo e(route('pacotes.recurso-nao-recebido', $pacote->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <p>Confirmar que o recurso de glosa <strong>não foi recebido</strong> dentro do prazo estabelecido?</p>
                        <p>Esta ação irá:</p>
                        <ul>
                            <li>Alterar o estado da glosa para "Recurso não recebido"</li>
                            <?php if($pacote->valor_pendente > 0): ?>
                                <li>Mover o pacote para o setor SIRE para processamento do valor pendente</li>
                            <?php else: ?>
                                <li>Mover o pacote para o Arquivo</li>
                            <?php endif; ?>
                        </ul>
                        
                        <div class="form-group">
                            <label for="observacao">Observação (opcional):</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"
                                      placeholder="Informe detalhes adicionais, se necessário..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Análise de Recurso -->
    <div class="modal fade" id="modalAnaliseRecurso" tabindex="-1" role="dialog" aria-labelledby="modalAnaliseRecursoLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="formAnaliseRecurso" action="<?php echo e(route('pacotes.analise-recurso', $pacote->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalAnaliseRecursoLabel">Análise de Recurso de Glosa</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Realize a análise do recurso de glosa apresentado pela OCS/PSA <strong><?php echo e(optional($pacote->ocsPsa)->nome); ?></strong>.</p>
                        
                        <div class="form-group">
                            <label>Resultado da Análise</label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="resultado_indeferido" name="resultado" value="indeferido" class="custom-control-input" checked>
                                <label class="custom-control-label" for="resultado_indeferido">Indeferido</label>
                            </div>
                            <div class="custom-control custom-radio mt-2">
                                <input type="radio" id="resultado_deferido" name="resultado" value="deferido" class="custom-control-input">
                                <label class="custom-control-label" for="resultado_deferido">Deferido</label>
                            </div>
                        </div>
                        
                        <!-- Campos específicos para recurso deferido -->
                        <div id="campos-deferido" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Informe os valores recursados e deferidos.
                            </div>
                            
                            <div class="form-group">
                                <label for="valor_recursado">Valor Recursado (R$)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" id="valor_recursado" 
                                           name="valor_recursado" value="0,00">
                                </div>
                                <small class="form-text text-muted">
                                    Valor máximo: R$ <?php echo e(number_format($pacote->valor_glosa, 2, ',', '.')); ?>

                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="valor_deferido">Valor Deferido (R$)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" class="form-control money" id="valor_deferido" 
                                           name="valor_deferido" value="0,00">
                                </div>
                                <small class="form-text text-muted">
                                    Valor máximo: o valor recursado informado acima.
                                </small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacao_analise">Observações (opcional)</label>
                            <textarea class="form-control" id="observacao_analise" name="observacao" 
                                      rows="3" placeholder="Informe observações adicionais sobre a análise do recurso..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar Análise</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Arquivar -->
    <div class="modal fade" id="modalArquivar" tabindex="-1" role="dialog" aria-labelledby="modalArquivarLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalArquivarLabel">Arquivar Pacote</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formArquivar" action="<?php echo e(route('pacotes.arquivar', $pacote->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <p>Informe a localização física onde o pacote será arquivado:</p>
                        
                        <div class="form-group">
                            <label for="localizacao_fisica">Localização Física</label>
                            <input type="text" class="form-control" id="localizacao_fisica" name="localizacao_fisica" 
                                   value="<?php echo e($pacote->localizacao_fisica ?? ''); ?>" required
                                   placeholder="Ex: Armário 3, Prateleira B, Caixa 15">
                        </div>
                        
                        <div class="form-group">
                            <label for="observacao">Observação (opcional)</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"
                                      placeholder="Informações adicionais sobre o arquivamento..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Arquivar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Editar Localização Física -->
    <div class="modal fade" id="modalEditarLocalizacaoFisica" tabindex="-1" role="dialog" aria-labelledby="modalEditarLocalizacaoFisicaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLocalizacaoFisicaLabel">Editar Localização Física</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditarLocalizacaoFisica" action="<?php echo e(route('pacotes.atualizar-localizacao-fisica', $pacote->id)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <p>Informe a nova localização física do pacote:</p>
                        
                        <div class="form-group">
                            <label for="localizacao_fisica_edit">Localização Física</label>
                            <input type="text" class="form-control" id="localizacao_fisica_edit" name="localizacao_fisica" 
                                   value="<?php echo e($pacote->localizacao_fisica ?? ''); ?>" required
                                   placeholder="Ex: Armário 3, Prateleira B, Caixa 15">
                        </div>
                        
                        <div class="form-group">
                            <label for="observacao">Observação (opcional)</label>
                            <textarea class="form-control" id="observacao" name="observacao" rows="3"
                                      placeholder="Informações adicionais sobre a alteração..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <!-- SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Tempus Dominus Bootstrap 4 (Datepicker) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />
    <!-- Estilos existentes -->
    <style>
        dt {
            font-weight: bold;
        }
        dd {
            margin-bottom: 10px;
        }
        
        /* Estilos para a visualização dos estágios */
        .progress-stages .stage {
            width: 40px;
            height: 40px;
            line-height: 40px;
            border-radius: 50%;
            background-color: #f4f6f9;
            margin: 0 auto 5px;
            color: #adb5bd;
            font-size: 18px;
        }
        
        .progress-stages .stage.active {
            background-color: #007bff;
            color: white;
        }
        
        .bg-orange {
            background-color: #fd7e14 !important;
        }

        /* Estilo para o gráfico knob */
        .knob-container {
            position: relative;
            text-align: center;
        }
        
        .knob-label {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <!-- SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <!-- Adicionar MaskMoney -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
    <!-- Adicionar jQuery Knob -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-Knob/1.2.13/jquery.knob.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Moment.js (necessário para o datepicker) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/pt-br.js"></script>
    <!-- Tempus Dominus Bootstrap 4 (Datepicker) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Configuração do Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            // Exibir mensagens usando Toastr
            <?php if(session('success')): ?>
                toastr.success("<?php echo e(session('success')); ?>");
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                toastr.error("<?php echo e(session('error')); ?>");
            <?php endif; ?>
            
            // Botão "Criar Novo Pacote"
            $('#criar-pacote').click(function() {
                window.location.href = '<?php echo e(route("pacotes.create")); ?>';
            });
            
            // Inicializar o jQuery Knob
            $('.knob').knob({
                format: function(value) {
                    return value + '%';  // Adiciona o símbolo % ao valor
                },
                draw: function() {
                    // Adicionar estilo tron
                    if (this.$.data('skin') == 'tron') {
                        this.cursorExt = 0.3;
                        var a = this.arc(this.cv);
                        var pa = this.g.beginPath();
                        var r = this.radius;
                        pa.addColorStop(0, '#28a745');
                        pa.addColorStop(1, '#20c997');
                        this.g.stroke();
                        this.g.beginPath();
                        this.g.strokeStyle = pa;
                        this.g.arc(this.xy, this.xy, r - this.lineWidth, a.s, a.e, a.d);
                        this.g.stroke();
                    }
                }
            });
            
            // Modificar a função que verifica se o pacote pode ser movido
            $('.btn-mover-pacote').click(function(e) {
                e.preventDefault();
                e.stopPropagation(); // Impedir a propagação do evento
                
                var pacoteId = $(this).data('id');
                var localizacao = $(this).data('localizacao');
                
                // Verificação especial para pacotes do SIRE
                if (localizacao === 'sire') {
                    var valorGlosa = <?php echo e($pacote->valor_glosa ?? 0); ?>;
                    var valorPendente = <?php echo e($pacote->valor_pendente ?? 0); ?>;
                    var valorRecursoGlosa = <?php echo e($pacote->valor_recurso_glosa ?? 0); ?>;
                    var localizacaoAnterior = "<?php echo e($pacote->localizacao_anterior); ?>";
                    
                    // Caso 2: Glosa Zero e Valor Pendente > 0 - Mostrar erro
                    if (valorGlosa === 0 && valorPendente > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ação não permitida',
                            text: 'Não é possível mover o pacote pois existe valor pendente de R$ ' + 
                                  valorPendente.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + 
                                  '. Informe os pagamentos antes de mover o pacote.'
                        });
                        return;
                    }
                    
                    // Caso 5: Glosa > 0, Recurso > 0, Localização Anterior = Glosa e Valor Pendente > 0 - Mostrar erro
                    if (valorGlosa > 0 && valorRecursoGlosa > 0 && localizacaoAnterior === 'glosa' && valorPendente > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ação não permitida',
                            text: 'Não é possível mover o pacote pois existe valor pendente de R$ ' + 
                                  valorPendente.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + 
                                  '. Informe os pagamentos antes de mover o pacote.'
                        });
                        return;
                    }
                    
                    $.ajax({
                        url: '<?php echo e(url("pacotes")); ?>/' + pacoteId + '/pode-mover',
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response.pode_mover) {
                                // Se pode mover, exibir o modal normalmente
                                configurarModal(pacoteId, localizacao);
                            } else {
                                // Se não pode mover, exibir apenas o SweetAlert
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Ação não permitida',
                                    text: response.mensagem,
                                    confirmButtonText: 'Entendi'
                                });
                                // Não chama configurarModal e não abre a modal
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Ocorreu um erro ao verificar se o pacote pode ser movido.',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                } else {
                    // Para outras localizações, exibe o modal normalmente
                    configurarModal(pacoteId, localizacao);
                }
            });

            // Configurar o modal de movimentação de pacote
            function configurarModal(pacoteId, localizacao, destino = '') {
                // Determinar próximo destino com base na localização atual
                if (!destino) {
                    switch(localizacao) {
                        case 'protocolo':
                            destino = 'lisura';
                            break;
                        case 'lisura':
                            destino = 'sire';
                            break;
                        case 'sire':
                            // Determinação automática do destino conforme regras de negócio
                            var valorGlosa = <?php echo e($pacote->valor_glosa ?? 0); ?>;
                            var valorPendente = <?php echo e($pacote->valor_pendente ?? 0); ?>;
                            var valorRecursoGlosa = <?php echo e($pacote->valor_recurso_glosa ?? 0); ?>;
                            var localizacaoAnterior = "<?php echo e($pacote->localizacao_anterior); ?>";
                            
                            // Implementação da lógica completa para a mensagem da modal
                            if (valorGlosa == 0 && valorPendente == 0) {
                                // Caso 1: Glosa Zero e Valor Pendente Zero
                                destino = 'arquivo';
                            } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'lisura' && valorPendente > 0) {
                                // Caso 2: Glosa > 0, Recurso = 0, Localização Anterior = Lisura e Valor Pendente > 0
                                destino = 'glosa';
                            } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'lisura' && valorPendente == 0) {
                                // Caso 5: Glosa > 0, Recurso = 0, Localização Anterior = Lisura e Valor Pendente == 0
                                destino = 'glosa';
                            } else if (valorGlosa > 0 && valorRecursoGlosa > 0 && localizacaoAnterior === 'glosa' && valorPendente == 0) {
                                // Caso 3: Glosa > 0, Recurso > 0, Localização Anterior = Glosa e Valor Pendente = 0
                                destino = 'arquivo';
                            } else if (valorGlosa > 0 && valorRecursoGlosa == 0 && localizacaoAnterior === 'glosa' && valorPendente == 0) {
                                // Caso 4: Glosa > 0, Recurso = 0, Localização Anterior = Glosa e Valor Pendente = 0
                                destino = 'arquivo';
                            } else {
                                // Valor pendente > 0, permanece no SIRE
                                destino = 'sire';
                            }
                            break;
                        case 'glosa':
                            destino = 'sire';
                            break;
                        case 'arquivo':
                            destino = 'arquivado';
                            break;
                        default:
                            destino = '';
                    }
                }
                
                // Atualizar o modal
                $('#localAtual').text(localizacao.charAt(0).toUpperCase() + localizacao.slice(1));
                $('#localDestino').text(destino.toUpperCase());
                $('#modalDestino').val(destino);
                $('#formMoverPacote').attr('action', '<?php echo e(route("pacotes.mover", $pacote->id)); ?>');
                
                // Exibir o modal
                $('#modalMoverPacote').modal('show');
            }

            // Configurar o modal de informar pagamento
            $('#modalInformarPagamento').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget);
                var pacoteId = button.data('id');
                var valorPendente = button.data('valor-pendente');
                
                // Formatar o valor pendente para exibição
                var valorPendenteFormatado = parseFloat(valorPendente).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                // Atualizar o modal
                $('#valorPendenteText').text(valorPendenteFormatado);
                $('#formInformarPagamento').attr('action', '<?php echo e(url("pacotes")); ?>/' + pacoteId + '/pagamento');
                
                // Limpar campos do formulário
                $('#valor_pagamento').val('');
                $('#observacao').val('');
                
                // Configurar o botão para preencher com o valor total pendente
                $('#btnPreencherTotal').off('click').on('click', function() {
                    $('#valor_pagamento').val(valorPendenteFormatado);
                });
            });

            // Configurar o modal de informar pagamento e processar o envio do formulário
            $('#formInformarPagamento').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modalInformarPagamento').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: response.message,
                                showConfirmButton: true
                            }).then((result) => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Ocorreu um erro ao processar a solicitação.'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Ocorreu um erro ao processar a solicitação.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('\n');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: errorMsg
                        });
                    }
                });
            });

            // Aplicar máscara para o campo de valor
            $('.money').maskMoney({
                prefix: '',
                thousands: '.',
                decimal: ',',
                allowZero: true
            });

            // Mostrar/ocultar campos conforme seleção do resultado
            $('input[name="resultado"]').change(function() {
                if ($(this).val() === 'deferido') {
                    $('#campos-deferido').slideDown();
                } else {
                    $('#campos-deferido').slideUp();
                }
            });

            // Validação do formulário de análise de recurso
            $('#formAnaliseRecurso').submit(function(e) {
                e.preventDefault();

                // Verificar se os valores monetários são válidos quando o resultado é "deferido"
                if ($('input[name="resultado"]:checked').val() === 'deferido') {
                    var valorRecursado = $('#valor_recursado').val();
                    var valorDeferido = $('#valor_deferido').val();
                    
                    if (!valorRecursado || valorRecursado === '0,00') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro de validação',
                            text: 'O valor recursado deve ser informado e maior que zero.'
                        });
                        return;
                    }
                    
                    if (!valorDeferido) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro de validação',
                            text: 'O valor deferido deve ser informado.'
                        });
                        return;
                    }
                }
                
                // Se a validação passou, enviar via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: response.message,
                                showConfirmButton: true
                            }).then((result) => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Ocorreu um erro ao processar a solicitação.'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Ocorreu um erro ao processar a solicitação.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('\n');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: errorMsg
                        });
                    }
                });
            });

            // Preencher o valor total no campo de pagamento
            $('#btnPreencherTotal').click(function() {
                var valorPendente = $('#valorPendenteText').text().replace('.', '').replace(',', '.');
                $('#valor_pagamento').val(valorPendente).trigger('maskMoney.mask');
            });

            // Configurar o modal de Aguardando Limite de Crédito
            $('#modalAguardandoLimite').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget);
                var pacoteId = button.data('id');
                var valorPendente = button.data('valor-pendente');
                
                // Formatar o valor pendente para exibição
                var valorPendenteFormatado = parseFloat(valorPendente).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                // Atualizar o modal
                $('#valorPendenteAguardandoLimite').text(valorPendenteFormatado);
                $('#formAguardandoLimite').attr('action', '<?php echo e(url("pacotes")); ?>/' + pacoteId + '/aguardar-limite');
                
                // Limpar campos do formulário
                $('#observacao_aguardando_limite').val('');
            });

            // Inicializar o gráfico circular
            $(".knob").knob();

            // Inicializar o datepicker para o campo de data da notificação
            $('#data_notificacao_div').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br',
                maxDate: moment().endOf('day'), // Limita até hoje ou datas anteriores
                icons: {
                    time: 'far fa-clock',
                    date: 'fa fa-calendar', // Mesmo ícone dos outros datepickers
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'far fa-calendar-check',
                    clear: 'far fa-trash-alt',
                    close: 'fas fa-times'
                }
            });

            // Atualizar data quando a modal for aberta
            $('#modalNotificarGlosa').on('shown.bs.modal', function() {
                $('#data_notificacao_div').datetimepicker('date', moment());
            });

            // Inicializar o datepicker para o campo de prazo de retirada do ofício
            // com data padrão de hoje + 30 dias
            $('#data_limite_div').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br',
                defaultDate: moment().add(30, 'days'),
                minDate: moment(), // Impede a seleção de datas passadas
                icons: {
                    time: 'far fa-clock',
                    date: 'far fa-calendar',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'far fa-calendar-check',
                    clear: 'far fa-trash-alt',
                    close: 'fas fa-times'
                }
            });

            // Inicializar o datepicker para o campo de data de retirada do ofício
            $('#data_retirada_div').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br',
                minDate: moment("<?php echo e($pacote->data_notificacao_glosa ? $pacote->data_notificacao_glosa->format('Y-m-d') : null); ?>"), // Data mínima = data notificação
                //useCurrent: false, // Evita selecionar data atual automaticamente
                icons: {
                    time: 'far fa-clock',
                    date: 'far fa-calendar',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'far fa-calendar-check',
                    clear: 'far fa-trash-alt',
                    close: 'fas fa-times'
                }
            });

            // Garantir que o valor inicial seja o mesmo dia da notificação ou posterior
            $('#modalRetiradaOficioGlosa').on('shown.bs.modal', function() {
                // Define a data atual como valor padrão, verificando se é o mesmo dia ou posterior à notificação
                var dataNotificacao = moment("<?php echo e($pacote->data_notificacao_glosa ? $pacote->data_notificacao_glosa->format('Y-m-d') : null); ?>");
                var hoje = moment().startOf('day');
                
                // Se a data de hoje for válida e igual ou posterior à notificação, usa ela
                if (hoje.isSameOrAfter(dataNotificacao)) {
                    $('#data_retirada_div').datetimepicker('date', hoje);
                } else {
                    // Caso contrário, usa o dia seguinte à notificação
                    $('#data_retirada_div').datetimepicker('date', dataNotificacao.add(1, 'days'));
                }
            });

            // Envio do formulário de retirada de ofício via AJAX
            $('#formRetiradaOficioGlosa').submit(function(e) {
                e.preventDefault();
                
                // Mostrar indicador de carregamento
                Swal.fire({
                    title: 'Processando...',
                    html: 'Registrando a retirada do ofício.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar formulário via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        // Fechar modal
                        $('#modalRetiradaOficioGlosa').modal('hide');
                        
                        // Mostrar mensagem de sucesso
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: response.message || 'Retirada de ofício registrada com sucesso.',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            // Ocultar botões relevantes e atualizar interface
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        // Mostrar mensagem de erro específica
                        var mensagem = 'Ocorreu um erro ao processar a solicitação.';
                        
                        // Tentar extrair a mensagem de erro da resposta
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            mensagem = xhr.responseJSON.message;
                        } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            // Extrair a primeira mensagem de validação
                            var errors = xhr.responseJSON.errors;
                            for (var field in errors) {
                                if (errors.hasOwnProperty(field) && errors[field].length > 0) {
                                    mensagem = errors[field][0];
                                    break;
                                }
                            }
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: mensagem
                        });
                    }
                });
            });

            // Inicializar o datepicker para o campo de data de recebimento do recurso
            $('#data_recebimento_div').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br',
                defaultDate: moment(), // Define a data padrão como hoje
                maxDate: moment(), // Impede a seleção de datas futuras
                icons: {
                    time: 'far fa-clock',
                    date: 'far fa-calendar',
                    up: 'fas fa-arrow-up',
                    down: 'fas fa-arrow-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'far fa-calendar-check',
                    clear: 'far fa-trash-alt',
                    close: 'fas fa-times'
                }
            });

            // Inicializar corretamente quando a modal for aberta
            $('#modalRecebimentoRecurso').on('shown.bs.modal', function() {
                $('#data_recebimento_div').datetimepicker('update');
            });

            // Envio do formulário de recurso não recebido via AJAX
            $('#formRecursoNaoRecebido').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modalRecursoNaoRecebido').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: response.message,
                                showConfirmButton: true
                            }).then((result) => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Ocorreu um erro ao processar a solicitação.'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Ocorreu um erro ao processar a solicitação.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('\n');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: errorMsg
                        });
                    }
                });
            });

            // Envio do formulário de arquivamento via AJAX
            $('#formArquivar').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modalArquivar').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: response.message,
                                showConfirmButton: true
                            }).then((result) => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Ocorreu um erro ao processar a solicitação.'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Ocorreu um erro ao processar a solicitação.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('\n');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: errorMsg
                        });
                    }
                });
            });

            // Envio do formulário de edição de localização física via AJAX
            $('#formEditarLocalizacaoFisica').submit(function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modalEditarLocalizacaoFisica').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Sucesso!',
                                text: response.message,
                                showConfirmButton: true
                            }).then((result) => {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: response.message || 'Ocorreu um erro ao processar a solicitação.'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMsg = 'Ocorreu um erro ao processar a solicitação.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMsg = errors.join('\n');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: errorMsg
                        });
                    }
                });
            });

            // Adicionar validação para o campo valor_deferido
            $('#valor_deferido').on('keyup change', function() {
                // Obter o valor máximo permitido (valor recursado)
                var valorRecursado = parseFloat($('#valor_recursado_display').text().replace(/\./g, '').replace(',', '.'));
                
                // Obter o valor atual digitado
                var valorDeferido = $(this).maskMoney('unmasked')[0];
                
                // Se o valor deferido for maior que o valor recursado
                if (valorDeferido > valorRecursado) {
                    // Limitar ao valor máximo
                    $(this).val(formatarMoeda(valorRecursado));
                    $(this).maskMoney('mask');
                    
                    // Mostrar mensagem de aviso
                    toastr.warning('O valor deferido não pode ser maior que o valor recursado (R$ ' + $('#valor_recursado_display').text() + ')');
                }
            });

            // Adicionar validação para o campo valor_recursado
            $('#valor_recursado').on('keyup change', function() {
                // Obter o valor máximo permitido (valor da glosa)
                var valorGlosa = parseFloat($('#valor_glosa_display').text().replace(/\./g, '').replace(',', '.'));
                
                // Obter o valor atual digitado
                var valorRecursado = $(this).maskMoney('unmasked')[0];
                
                // Se o valor recursado for maior que o valor da glosa
                if (valorRecursado > valorGlosa) {
                    // Limitar ao valor máximo
                    $(this).val(formatarMoeda(valorGlosa));
                    $(this).maskMoney('mask');
                    
                    // Mostrar mensagem de aviso
                    toastr.warning('O valor recursado não pode ser maior que o valor da glosa (R$ ' + $('#valor_glosa_display').text() + ')');
                }
            });

            // Função auxiliar para formatar moeda
            function formatarMoeda(valor) {
                return valor.toFixed(2).replace('.', ',');
            }

            // Configuração do datepicker para Data da Notificação de Glosa
            $('#modalNotificarGlosa').on('shown.bs.modal', function() {
                // Destruir instância anterior caso exista
                if ($('#data_notificacao_div').data('datetimepicker')) {
                    $('#data_notificacao_div').datetimepicker('destroy');
                }
                
                // Inicializar o datepicker
                $('#data_notificacao_div').datetimepicker({
                    format: 'DD/MM/YYYY',
                    locale: 'pt-br',
                    maxDate: moment().endOf('day'), // Limita até hoje
                    allowInputToggle: true, // Permite abrir o calendário ao clicar no input
                    icons: {
                        time: 'far fa-clock',
                        date: 'fa fa-calendar', // Mesmo ícone dos outros datepickers
                        up: 'fas fa-arrow-up',
                        down: 'fas fa-arrow-down',
                        previous: 'fas fa-chevron-left',
                        next: 'fas fa-chevron-right',
                        today: 'far fa-calendar-check',
                        clear: 'far fa-trash-alt',
                        close: 'fas fa-times'
                    }
                });
                
                // Definir a data atual como valor padrão
                $('#data_notificacao_div').datetimepicker('date', moment());
                
                // Garantir que o botão abra o datepicker ao clicar (corrigido)
                $('#modalNotificarGlosa .input-group-append[data-target="#data_notificacao_div"]').off('click').on('click', function() {
                    $('#data_notificacao_div').datetimepicker('toggle');
                });
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/admin21ct/pmed2/resources/views/pacotes/ver.blade.php ENDPATH**/ ?>