<?php $__env->startSection('title', 'Detalhes do Mapa de Pagamento'); ?>

<?php $__env->startSection('content_header'); ?>
    <h1>Detalhes do Mapa de Pagamento</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informações do Mapa</h3>
            <div class="float-right">
                <div class="btn-group mr-2">
                    <a href="<?php echo e(route('mapas.exportar', [$mapa->id, 'html'])); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-alt"></i> Exportar HTML
                    </a>
                    <a href="<?php echo e(route('mapas.exportar', [$mapa->id, 'pdf'])); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mapas-manage')): ?>
                <a href="<?php echo e(route('mapas.edit', $mapa->id)); ?>" class="btn btn-sm btn-primary">Editar Mapa</a>
                <?php endif; ?>
                <a href="<?php echo e(route('mapas.index')); ?>" class="btn btn-sm btn-secondary">Voltar para Lista</a>
            </div>
        </div>
        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Número do Mapa:</strong> <?php echo e($mapa->numero_mapa); ?></p>
                    <p><strong>Data de Liberação:</strong> <?php echo e($mapa->data_criacao->format('d/m/Y')); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Total de Faturas:</strong> <?php echo e($mapa->pacotes->count()); ?></p>
                    <p><strong>Valor Total:</strong> R$ <?php echo e(number_format($mapa->valorTotal, 2, ',', '.')); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mapas-manage')): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Adicionar Fatura ao Mapa</h3>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('mapas.adicionar-fatura', $mapa->id)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pacote_id">Selecione uma fatura</label>
                            <select name="pacote_id" id="pacote_id" class="form-control select2-faturas <?php $__errorArgs = ['pacote_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                <option value="">Selecione...</option>
                                <?php $__currentLoopData = $pacotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pacote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pacote->id); ?>" <?php echo e(old('pacote_id') == $pacote->id ? 'selected' : ''); ?>>
                                        <?php echo e($pacote->numero_fatura); ?> - <?php echo e($pacote->ocsPsa->nome ?? 'OCS/PSA não informada'); ?> - R$ <?php echo e(number_format($pacote->valor_fatura, 2, ',', '.')); ?> - Implantado: R$ <?php echo e(number_format($pacote->valor_pendente, 2, ',', '.')); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['pacote_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_parcial">Valor Empenhado</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">R$</span>
                                </div>
                                <input type="number" step="0.01" name="valor_parcial" id="valor_parcial" class="form-control <?php $__errorArgs = ['valor_parcial'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="0,00" required>
                                <?php $__errorArgs = ['valor_parcial'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="empenho">Nº do Empenho</label>
                            <input type="text" name="empenho" id="empenho" class="form-control <?php $__errorArgs = ['empenho'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['empenho'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group">
                            <label for="data_empenho">Data do Empenho</label>
                            <input type="date" name="data_empenho" id="data_empenho" class="form-control <?php $__errorArgs = ['data_empenho'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['data_empenho'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nota_fiscal">Nota Fiscal</label>
                            <input type="text" name="nota_fiscal" id="nota_fiscal" class="form-control <?php $__errorArgs = ['nota_fiscal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['nota_fiscal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="data_nota_fiscal">Data da Nota Fiscal</label>
                            <input type="date" name="data_nota_fiscal" id="data_nota_fiscal" class="form-control <?php $__errorArgs = ['data_nota_fiscal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['data_nota_fiscal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Adicionar Fatura</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Faturas no Mapa</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabela-faturas" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nº da Fatura</th>
                            <th>Valor Implantado</th>
                            <th>Valor Empenhado</th>
                            <th>Nº do Empenho</th>
                            <th>Nota Fiscal</th>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mapas-manage')): ?>
                            <th>Ações</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $mapa->pacotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pacote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($pacote->numero_fatura); ?></td>
                                <td>R$ <?php echo e(number_format($pacote->valor_fatura, 2, ',', '.')); ?></td>
                                <td>R$ <?php echo e(number_format($pacote->pivot->valor_parcial, 2, ',', '.')); ?></td>
                                <td><?php echo e($pacote->pivot->empenho ?: '-'); ?></td>
                                <td><?php echo e($pacote->pivot->nota_fiscal ?: '-'); ?></td>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mapas-manage')): ?>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('mapas.editar-fatura', [$mapa->id, $pacote->id])); ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <form action="<?php echo e(route('mapas.remover-fatura', [$mapa->id, $pacote->id])); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta fatura do mapa?');" style="display: inline-block;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e(Auth::user()->can('mapas-manage') ? '6' : '5'); ?>" class="text-center">Nenhuma fatura adicionada a este mapa.</td>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if($mapa->pacotes->count() > 0): ?>
                            <tr>
                                <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                                <td><strong>R$ <?php echo e(number_format($totalPago, 2, ',', '.')); ?></strong></td>
                                <td colspan="<?php echo e(Auth::user()->can('mapas-manage') ? '3' : '2'); ?>"></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para pesquisa de faturas
            $('.select2-faturas').select2({
                theme: 'bootstrap4',
                placeholder: 'Digite para pesquisar por número da fatura ou OCS/PSA...',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Nenhuma fatura encontrada";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });
            
            // Inicializar DataTable
            $('#tabela-faturas').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
                },
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]]
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/admin21ct/pmed2/resources/views/mapas/show.blade.php ENDPATH**/ ?>