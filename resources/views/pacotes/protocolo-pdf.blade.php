<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protocolo de Entrega - Pacote #{{ $pacote->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000;
        }
        
        .protocolo {
            width: 190mm;
            height: 60mm;
            padding: 3mm;
            border: 1px solid #333;
            position: relative;
        }
        
        .cabecalho {
            text-align: center;
            margin-bottom: 2mm;
            border-bottom: 1px solid #333;
            padding-bottom: 1mm;
        }
        
        .cabecalho h1 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 0.5mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cabecalho p {
            font-size: 8pt;
            color: #555;
        }
        
        .dados {
            display: table;
            width: 100%;
            margin-bottom: 2mm;
        }
        
        .dados-row {
            display: table-row;
        }
        
        .dados-cell {
            display: table-cell;
            padding: 1mm 2mm;
            vertical-align: middle;
            border-bottom: 1px solid #ddd;
        }
        
        .dados-label {
            font-weight: bold;
            width: 25%;
            color: #333;
            font-size: 7pt;
        }
        
        .dados-valor {
            width: 75%;
            font-size: 9pt;
        }
        
        .assinatura {
            margin-top: 3mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .assinatura-campo {
            width: 45%;
        }
        
        .assinatura-linha {
            border-top: 1px solid #000;
            margin-top: 4mm;
            padding-top: 1mm;
            text-align: center;
            font-size: 7pt;
        }
        
        .data-emissao {
            position: absolute;
            bottom: 3mm;
            right: 5mm;
            font-size: 7pt;
            color: #666;
        }
        
        .destaque {
            font-weight: bold;
            color: #000;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .protocolo {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="protocolo">
        <!-- Cabeçalho -->
        <div class="cabecalho">
            <h1>PMED 2.0 - Protocolo de Entrega</h1>
            <p>Comprovante de Recebimento</p>
        </div>
        
        <!-- Dados do Pacote -->
        <div class="dados">
            <div class="dados-row">
                <div class="dados-cell dados-label">Nº do Pacote:</div>
                <div class="dados-cell dados-valor destaque">#{{ $pacote->id }}</div>
                <div class="dados-cell dados-label">Data de entrada no sistema:</div>
                <div class="dados-cell dados-valor">{{ \Carbon\Carbon::parse($pacote->data_entrada)->format('d/m/Y') }}</div>
            </div>
            
            <div class="dados-row">
                <div class="dados-cell dados-label">Nº da Fatura:</div>
                <div class="dados-cell dados-valor destaque">{{ $pacote->numero_fatura }}</div>
                <div class="dados-cell dados-label">Valor da Fatura:</div>
                <div class="dados-cell dados-valor destaque">R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</div>
            </div>
            
            <div class="dados-row">
                <div class="dados-cell dados-label">OCS/PSA:</div>
                <div class="dados-cell dados-valor" colspan="3">{{ optional($pacote->ocsPsa)->nome ?? 'Não informado' }}</div>
            </div>
        </div>
        
        <!-- Assinatura -->
        <div class="assinatura">
            <div class="assinatura-campo">
                <div class="assinatura-linha">
                    Assinatura do Protocolista
                </div>
            </div>
            
            <div class="assinatura-campo">
                <div class="assinatura-linha">
                    Data: ____/____/________
                </div>
            </div>
        </div>
        
        <!-- Data de Emissão -->
        <div class="data-emissao">
            Emitido em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
