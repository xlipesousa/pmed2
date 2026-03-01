<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Desempenho</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>Relatório de Desempenho Operacional</h1>
    <div class="meta">
        <div><strong>Gerado em:</strong> {{ $dados['gerado_em'] }}</div>
        <div>
            <strong>Pesos:</strong>
            Volume {{ $dados['pesos']['volume'] }}% |
            Tempo {{ $dados['pesos']['tempo'] }}% |
            Qualidade {{ $dados['pesos']['qualidade'] }}% |
            Retrabalho {{ $dados['pesos']['retrabalho'] }}%
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Colaborador</th>
                <th>Score</th>
                <th>Volume</th>
                <th>Tempo</th>
                <th>Qualidade</th>
                <th>Retrabalho</th>
                <th>Taxa Retrabalho (%)</th>
                <th>Movimentações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dados['itens'] as $item)
                <tr>
                    <td>{{ $item['nome'] }}</td>
                    <td>{{ $item['score_operacional'] }}</td>
                    <td>{{ $item['scores']['volume'] ?? 0 }}</td>
                    <td>{{ $item['scores']['tempo'] ?? 0 }}</td>
                    <td>{{ $item['scores']['qualidade'] ?? 0 }}</td>
                    <td>{{ $item['scores']['retrabalho'] ?? 0 }}</td>
                    <td>{{ $item['taxa_retrabalho'] ?? 0 }}</td>
                    <td>{{ $item['volume_bruto'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Sem dados para o filtro aplicado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
