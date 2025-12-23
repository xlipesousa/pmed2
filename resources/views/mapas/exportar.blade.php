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
    </style>
</head>
<body>
    <div class="print-button">
        <button onclick="window.print()">Imprimir / Salvar como PDF</button>
    </div>

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
            @forelse($mapa->pacotes as $pacote)
                <tr>
                    <td>{{ $pacote->numero_fatura }}</td>
                    <td>R$ {{ number_format($pacote->valor_fatura, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($pacote->pivot->valor_parcial, 2, ',', '.') }}</td>
                    <td>{{ $pacote->pivot->empenho ?: '-' }}</td>
                    <td>{{ $pacote->pivot->data_empenho ? \Carbon\Carbon::parse($pacote->pivot->data_empenho)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $pacote->pivot->nota_fiscal ?: '-' }}</td>
                    <td>{{ $pacote->pivot->data_nota_fiscal ? \Carbon\Carbon::parse($pacote->pivot->data_nota_fiscal)->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Nenhuma fatura adicionada a este mapa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>PMED - Sistema de Gestão de Pagamento de Médicos</p>
    </div>
</body>
</html>