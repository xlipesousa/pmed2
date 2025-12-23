<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Pagamento - {{ $mapa->numero_mapa }}</title>
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
        }
        .info-row {
            margin-bottom: 10px;
        }
        .info-col {
            display: inline-block;
            width: 48%;
            vertical-align: top;
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
            font-size: 12px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>MAPA DE PAGAMENTO</h1>
        <h2>{{ $mapa->numero_mapa }}</h2>
    </div>

    <div class="info-box">
        <div class="info-row">
            <div class="info-col">
                <strong>Número do Mapa:</strong> {{ $mapa->numero_mapa }}
            </div>
            <div class="info-col">
                <strong>Data de Criação:</strong> {{ $mapa->data_criacao->format('d/m/Y') }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-col">
                <strong>Total de Faturas:</strong> {{ $mapa->pacotes->count() }}
            </div>
            <div class="info-col">
                <strong>Valor Total:</strong> R$ {{ number_format($mapa->valorTotal, 2, ',', '.') }}
            </div>
        </div>
    </div>

    <h3>Faturas no Mapa</h3>
    <table id="tabela-faturas" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nº da Fatura</th>
                <th>Valor Implantado</th>
                <th>Valor Empenhado</th>
                <th>Nº do Empenho</th>
                <th>Nota Fiscal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mapa->pacotes as $pacote)
                <tr>
                    <td>{{ $pacote->numero_fatura }}</td>
                    <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pacote->pivot->valor_parcial, 2, ',', '.') }}</td>
                    <td>{{ $pacote->pivot->empenho ?: '-' }}</td>
                    <td>{{ $pacote->pivot->nota_fiscal ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Nenhuma fatura adicionada a este mapa.</td>
                </tr>
            @endforelse
            
            @if($mapa->pacotes->count() > 0)
                <tr>
                    <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong>R$ {{ number_format($totalPago, 2, ',', '.') }}</strong></td>
                    <td colspan="2"></td>
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