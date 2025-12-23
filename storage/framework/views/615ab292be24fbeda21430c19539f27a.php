<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura - <?php echo e($pacote->numero_fatura); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin-bottom: 5px;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .info-col {
            flex: 1;
            min-width: 250px;
        }
        .total-row {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .print-button {
            text-align: center;
            margin-bottom: 20px;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
        .highlight {
            background-color: #f9f9f9;
            padding: 8px;
            border-radius: 3px;
            font-weight: bold;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">Imprimir / Salvar como PDF</button>
    </div>

    <div class="header">
        <h1>DETALHES DA FATURA</h1>
        <h2><?php echo e($pacote->numero_fatura); ?></h2>
    </div>

    <div class="info-box">
        <div class="info-row">
            <div class="info-col">
                <p><strong>Número da Fatura:</strong> <?php echo e($pacote->numero_fatura); ?></p>
                <p><strong>Data de Entrada:</strong> <?php echo e($pacote->data_entrada->format('d/m/Y')); ?></p>
                <p><strong>OCS/PSA:</strong> <?php echo e($pacote->ocsPsa->nome ?? 'N/A'); ?></p>
            </div>
            <div class="info-col">
                <p><strong>Valor Total:</strong> R$ <?php echo e(number_format($pacote->valor_fatura, 2, ',', '.')); ?></p>
                <p><strong>Valor Implantado:</strong> R$ <?php echo e(number_format($pacote->valor_pendente, 2, ',', '.')); ?></p>
                
                <?php
                    // Calcula o somatório dos valores pagos nos mapas
                    $totalPago = $pacote->mapas->sum(function($mapa) {
                        return $mapa->pivot->valor_parcial;
                    });
                ?>
                <p class="highlight"><strong>Valor Pago (Total):</strong> R$ <?php echo e(number_format($totalPago, 2, ',', '.')); ?></p>
            </div>
        </div>
    </div>

    <h3>Mapas de Pagamento Relacionados</h3>
    <table>
        <thead>
            <tr>
                <th>Nº do Mapa</th>
                <th>Data de Liberação</th>
                <th>Valor Empenhado</th>
                <th>Nº do Empenho</th>
                <th>Data Empenho</th>
                <th>Nota Fiscal</th>
                <th>Data NF</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $pacote->mapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($mapa->numero_mapa); ?></td>
                    <td><?php echo e($mapa->data_criacao->format('d/m/Y')); ?></td>
                    <td>R$ <?php echo e(number_format($mapa->pivot->valor_parcial, 2, ',', '.')); ?></td>
                    <td><?php echo e($mapa->pivot->empenho ?: '-'); ?></td>
                    <td><?php echo e($mapa->pivot->data_empenho ? \Carbon\Carbon::parse($mapa->pivot->data_empenho)->format('d/m/Y') : '-'); ?></td>
                    <td><?php echo e($mapa->pivot->nota_fiscal ?: '-'); ?></td>
                    <td><?php echo e($mapa->pivot->data_nota_fiscal ? \Carbon\Carbon::parse($mapa->pivot->data_nota_fiscal)->format('d/m/Y') : '-'); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Esta fatura não está em nenhum mapa de pagamento.</td>
                </tr>
            <?php endif; ?>
            
            <?php if($pacote->mapas->count() > 0): ?>
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>R$ <?php echo e(number_format($totalPago, 2, ',', '.')); ?></strong></td>
                    <td colspan="4"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        <p>Documento gerado em <?php echo e(now()->format('d/m/Y H:i:s')); ?></p>
        <p>PMED - Sistema de Gestão de Pagamento de Médicos</p>
    </div>
</body>
</html><?php /**PATH /home/admin21ct/pmed2/resources/views/mapas/fatura-exportar.blade.php ENDPATH**/ ?>