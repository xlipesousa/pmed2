<?php $__env->startSection('title', 'Mapas de Pagamento'); ?>

<?php $__env->startSection('content_header'); ?>
    <h1>Mapas de Pagamento</h1>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-header">
            <div class="float-right">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mapas-manage')): ?>
                <a href="<?php echo e(route('mapas.create')); ?>" class="btn btn-primary">Novo Mapa</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table id="tabela-mapas" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Número do Mapa</th>
                            <th>Data de Liberação</th>
                            <th>Total de Faturas</th>
                            <th>Valor Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $mapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($mapa->numero_mapa); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($mapa->data_criacao)->format('d/m/Y')); ?></td>
                                <td><?php echo e($mapa->pacotes_count); ?></td>
                                <td>R$ <?php echo e(number_format($mapa->valorTotal, 2, ',', '.')); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo e(route('mapas.show', $mapa->id)); ?>" class="btn btn-sm btn-info">Ver</a>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('mapas-manage')): ?>
                                        <a href="<?php echo e(route('mapas.edit', $mapa->id)); ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <form action="<?php echo e(route('mapas.destroy', $mapa->id)); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este mapa?');" style="display: inline-block;">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">Nenhum mapa de pagamento encontrado.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
    <script>
        $(document).ready(function() {
            $('#tabela-mapas').DataTable({
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
<?php echo $__env->make('adminlte::page', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/admin21ct/pmed2/resources/views/mapas/index.blade.php ENDPATH**/ ?>