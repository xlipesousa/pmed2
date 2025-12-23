<?php $__env->startSection('title', 'Criar Novo Pacote'); ?>

<?php $__env->startSection('content_header'); ?>
    <h1>
        Criar Novo Pacote
        <a href="<?php echo e(route('pacotes.index')); ?>" class="btn btn-sm btn-secondary float-right">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <form id="form-criar-pacote" action="<?php echo e(route('pacotes.store')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="row">
            <!-- Informações básicas -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">Informações Básicas</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="ocs_psa_id">OCS/PSA</label>
                            <select class="form-control" id="ocs_psa_id" name="ocs_psa_id" required>
                                <option value="">Selecione...</option>
                                <?php $__currentLoopData = $ocsPsaList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ocsPsa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($ocsPsa->id); ?>"><?php echo e($ocsPsa->nome); ?> <?php echo e($ocsPsa->codigo_interno ? '('.$ocsPsa->codigo_interno.')' : ''); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="numero_fatura">Número da Fatura</label>
                            <input type="text" class="form-control" id="numero_fatura" name="numero_fatura" 
                                   placeholder="Ex: FT-1234" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="data_entrada">Data de Entrada no Protocolo</label>
                            <div class="input-group date" id="data_entrada_div" data-target-input="nearest">
                                <input type="text" class="form-control datetimepicker-input" id="data_entrada" 
                                       name="data_entrada" data-target="#data_entrada_div" required
                                       value="<?php echo e(date('d/m/Y')); ?>">
                                <div class="input-group-append" data-target="#data_entrada_div" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Valores e tipos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title">Valores e Tipos</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="valor_fatura">Valor da Fatura (R$)</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="text" class="form-control money" id="valor_fatura" 
                                       name="valor_fatura" placeholder="0,00" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo_id">Tipo</label>
                            <select class="form-control" id="tipo_id" name="tipo_id" required>
                                <option value="">Selecione...</option>
                                <?php $__currentLoopData = $tiposPacote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($tipo->id); ?>"><?php echo e($tipo->nome); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Espaço reservado para o campo Tipo de Conta (será implementado no fluxo da Lisura) -->
                        <div class="form-group" style="visibility: hidden;">
                            <label>Espaço Reservado</label>
                            <div class="form-control" style="height: 38px;">&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Observações -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title">Observações</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="observacoes">Observações</label>
                            <textarea class="form-control" id="observacoes" name="observacoes" rows="3" 
                                      placeholder="Informe detalhes adicionais sobre o pacote..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Botões de ação -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Pacote
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-eraser"></i> Limpar Formulário
                        </button>
                        <a href="<?php echo e(route('pacotes.index')); ?>" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar select2 para OCS/PSA com filtragem
            $('#ocs_psa_id').select2({
                placeholder: "Selecione ou digite para buscar...",
                allowClear: true,
                theme: "bootstrap"
            });
            
            // Inicializar datepicker com restrição para datas futuras
            $('#data_entrada_div').datetimepicker({
                format: 'DD/MM/YYYY',
                locale: 'pt-br',
                maxDate: moment().endOf('day') // Limita até hoje (fim do dia atual)
            });
            
            // Inicializar máscara para valores monetários
            $('.money').maskMoney({
                prefix: '',
                thousands: '.',
                decimal: ',',
                allowZero: true
            });
            
            // Validação adicional no envio do formulário para garantir que não sejam aceitas datas futuras
            $('#form-criar-pacote').submit(function(e) {
                // Pegar a data inserida
                const dataEntrada = moment($('#data_entrada').val(), 'DD/MM/YYYY');
                const hoje = moment().endOf('day');
                
                // Verificar se a data é futura
                if (dataEntrada.isValid() && dataEntrada.isAfter(hoje)) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Data inválida',
                        text: 'A data de entrada não pode ser uma data futura.'
                    });
                    return false;
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/admin21ct/pmed2/resources/views/pacotes/criar.blade.php ENDPATH**/ ?>