<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura - {{ $pacote->numero_fatura }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif; /* Fonte compatível com acentuação no PDF */
            line-height: 1.6;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin-bottom: 5px;
            font-size: 20px;
        }
        .header h2 {
            font-size: 18px;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-col {
            display: inline-block;
            width: 48%;
            vertical-align: top;
        }
        .total-row {
            margin-top: 10px;
            padding-top: 10px;
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
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        h3 {
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .highlight {
            background-color: #f9f9f9;
            padding: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DETALHES DA FATURA</h1>
        <h2>{{ $pacote->numero_fatura }}</h2>
    </div>

    <div class="info-box">
        <div class="info-row">
            <div class="info-col">
                <p><strong>Número da Fatura:</strong> {{ $pacote->numero_fatura }}</p>
                <p><strong>Data de Entrada:</strong> {{ $pacote->data_entrada->format('d/m/Y') }}</p>
                <p><strong>OCS/PSA:</strong> {{ $pacote->ocsPsa->nome ?? 'N/A' }}</p>
            </div>
            <div class="info-col">
                <p><strong>Valor Total:</strong> R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</p>
                <p><strong>Valor Implantado:</strong> R$ {{ number_format($pacote->valor_pendente, 2, ',', '.') }}</p>
                
                @php
                    // Calcula o somatório dos valores pagos nos mapas
                    $totalPago = $pacote->mapas->sum(function($mapa) {
                        return $mapa->pivot->valor_parcial;
                    });
                @endphp
                <p class="highlight"><strong>Valor Empenhado (Total):</strong> R$ {{ number_format($totalPago, 2, ',', '.') }}</p>
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
            @forelse($pacote->mapas as $mapa)
                <tr>
                    <td>{{ $mapa->numero_mapa }}</td>
                    <td>{{ $mapa->data_criacao->format('d/m/Y') }}</td>
                    <td>R$ {{ number_format($mapa->pivot->valor_parcial, 2, ',', '.') }}</td>
                    <td>{{ $mapa->pivot->empenho ?: '-' }}</td>
                    <td>{{ $mapa->pivot->data_empenho ? \Carbon\Carbon::parse($mapa->pivot->data_empenho)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $mapa->pivot->nota_fiscal ?: '-' }}</td>
                    <td>{{ $mapa->pivot->data_nota_fiscal ? \Carbon\Carbon::parse($mapa->pivot->data_nota_fiscal)->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Esta fatura não está em nenhum mapa de pagamento.</td>
                </tr>
            @endforelse
            
            @if($pacote->mapas->count() > 0)
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>R$ {{ number_format($totalPago, 2, ',', '.') }}</strong></td>
                    <td colspan="4"></td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>PMED - Sistema de Gestão de Pagamento de Médicos</p>
    </div>
</body>
</html>