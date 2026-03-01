<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pacote;
use App\Models\OcsPsa;
use App\Models\TipoPacote;
use App\Models\TipoConta;
use App\Models\MotivoGlosa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PesquisaController extends Controller
{
    public function index()
    {
        // Carregando dados para os filtros
        $ocsPsaList = OcsPsa::orderBy('nome')->pluck('nome', 'id');
        $tiposPacote = TipoPacote::orderBy('nome')->pluck('nome', 'id');
        $tiposConta = TipoConta::orderBy('nome')->pluck('nome', 'id');
        $motivosGlosa = MotivoGlosa::orderBy('nome')->pluck('nome', 'id');
        
        $estadosGerais = Pacote::select('estado_geral')->distinct()->pluck('estado_geral');
        $estadosGlosa = Pacote::select('estado_glosa')->distinct()->pluck('estado_glosa');
        $localizacoes = Pacote::select('localizacao_atual')->distinct()->pluck('localizacao_atual');

        return view('pesquisa.index', compact(
            'ocsPsaList', 
            'tiposPacote', 
            'tiposConta', 
            'motivosGlosa', 
            'estadosGerais',
            'estadosGlosa',
            'localizacoes'
        ));
    }

    /**
     * Processa a busca avançada de pacotes
     */
    public function buscar(Request $request)
    {
        // Construir a consulta base
        $query = Pacote::with(['ocsPsa', 'tipoPacote', 'tipoConta', 'motivoGlosa']);

        // Aplicar filtros de pesquisa
        if ($request->filled('numero_pacote')) {
            $query->where('id', $request->numero_pacote);
        }
        
        if ($request->filled('numero_fatura')) {
            $query->where('numero_fatura', 'like', '%' . $request->numero_fatura . '%');
        }
        
        if ($request->filled('ocs_psa_id')) {
            $query->where('ocs_psa_id', $request->ocs_psa_id);
        }

        if ($request->filled('tipo_id')) {
            $query->where('tipo_id', $request->tipo_id);
        }

        if ($request->filled('tipo_conta_id')) {
            $query->where('tipo_conta_id', $request->tipo_conta_id);
        }

        if ($request->filled('motivo_glosa_id')) {
            $query->where('motivo_glosa_id', $request->motivo_glosa_id);
        }

        if ($request->filled('estado_geral')) {
            $query->where('estado_geral', $request->estado_geral);
        }

        if ($request->filled('estado_glosa')) {
            $query->where('estado_glosa', $request->estado_glosa);
        }

        if ($request->filled('localizacao_atual')) {
            $query->where('localizacao_atual', $request->localizacao_atual);
        }

        // Filtros de valor
        if ($request->filled('valor_fatura_min')) {
            $valorMin = str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_fatura_min);
            $query->where('valor_fatura', '>=', (float)$valorMin);
        }

        if ($request->filled('valor_fatura_max')) {
            $valorMax = str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_fatura_max);
            $query->where('valor_fatura', '<=', (float)$valorMax);
        }

        if ($request->filled('valor_glosa_min')) {
            $valorMin = str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_glosa_min);
            $query->where('valor_glosa', '>=', (float)$valorMin);
        }

        if ($request->filled('valor_glosa_max')) {
            $valorMax = str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_glosa_max);
            $query->where('valor_glosa', '<=', (float)$valorMax);
        }

        // Filtros de data
        if ($request->filled('periodo_entrada')) {
            $datas = explode(' - ', $request->periodo_entrada);
            if (count($datas) == 2) {
                $dataInicio = Carbon::createFromFormat('d/m/Y', trim($datas[0]))->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', trim($datas[1]))->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
        }

        if ($request->filled('termo_busca')) {
            $termo = $request->termo_busca;
            $query->where(function($q) use ($termo) {
                $q->where('numero_fatura', 'like', '%' . $termo . '%')
                  ->orWhere('observacoes', 'like', '%' . $termo . '%')
                  ->orWhere('descricao_glosa', 'like', '%' . $termo . '%')
                  ->orWhere('localizacao_fisica', 'like', '%' . $termo . '%')
                  ->orWhereHas('ocsPsa', function($sq) use ($termo) {
                      $sq->where('nome', 'like', '%' . $termo . '%');
                  });
            });
        }

        // Ordenação
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');
        $query->orderBy($sort, $direction);

        // Executar consulta e paginar resultados
        $resultados = $query->paginate(15)->appends($request->except('page'));
        
        // Dados para os filtros (reutilizar do método index)
        $ocsPsaList = OcsPsa::orderBy('nome')->pluck('nome', 'id');
        $tiposPacote = TipoPacote::orderBy('nome')->pluck('nome', 'id');
        $tiposConta = TipoConta::orderBy('nome')->pluck('nome', 'id');
        $motivosGlosa = MotivoGlosa::orderBy('nome')->pluck('nome', 'id');
        
        $estadosGerais = Pacote::select('estado_geral')->distinct()->pluck('estado_geral');
        $estadosGlosa = Pacote::select('estado_glosa')->distinct()->pluck('estado_glosa');
        $localizacoes = Pacote::select('localizacao_atual')->distinct()->pluck('localizacao_atual');

        return view('pesquisa.index', compact(
            'resultados',
            'ocsPsaList', 
            'tiposPacote', 
            'tiposConta', 
            'motivosGlosa',
            'estadosGerais',
            'estadosGlosa',
            'localizacoes'
        ));
    }

    /**
     * Exporta os resultados da pesquisa para diferentes formatos
     * 
     * @param Request $request
     * @param string $formato (excel, csv, pdf, html)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportar(Request $request, $formato)
    {
        // Construir a consulta base (reutilizar a lógica do método buscar)
        $query = Pacote::with(['ocsPsa', 'tipoPacote', 'tipoConta', 'motivoGlosa']);

        // Aplicar filtros de pesquisa
        if ($request->filled('numero_pacote')) {
            $query->where('id', $request->numero_pacote);
        }
        
        if ($request->filled('numero_fatura')) {
            $query->where('numero_fatura', 'like', '%' . $request->numero_fatura . '%');
        }
        
        if ($request->filled('ocs_psa_id')) {
            $query->where('ocs_psa_id', $request->ocs_psa_id);
        }

        if ($request->filled('tipo_id')) {
            $query->where('tipo_id', $request->tipo_id);
        }

        if ($request->filled('tipo_conta_id')) {
            $query->where('tipo_conta_id', $request->tipo_conta_id);
        }

        if ($request->filled('motivo_glosa_id')) {
            $query->where('motivo_glosa_id', $request->motivo_glosa_id);
        }

        if ($request->filled('estado_geral')) {
            $query->where('estado_geral', $request->estado_geral);
        }

        if ($request->filled('estado_glosa')) {
            $query->where('estado_glosa', $request->estado_glosa);
        }

        if ($request->filled('localizacao_atual')) {
            $query->where('localizacao_atual', $request->localizacao_atual);
        }

        // Filtros de valor
        if ($request->filled('valor_fatura_min')) {
            $valorMin = str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_fatura_min);
            $query->where('valor_fatura', '>=', (float)$valorMin);
        }

        if ($request->filled('valor_fatura_max')) {
            $valorMax = str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_fatura_max);
            $query->where('valor_fatura', '<=', (float)$valorMax);
        }

        if ($request->filled('valor_glosa_min')) {
            $valorMin = str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_glosa_min);
            $query->where('valor_glosa', '>=', (float)$valorMin);
        }

        if ($request->filled('valor_glosa_max')) {
            $valorMax = str_replace(['R$', '.', ','], ['', '', '.'], $request->valor_glosa_max);
            $query->where('valor_glosa', '<=', (float)$valorMax);
        }

        // Filtros de data
        if ($request->filled('periodo_entrada')) {
            $datas = explode(' - ', $request->periodo_entrada);
            if (count($datas) == 2) {
                $dataInicio = Carbon::createFromFormat('d/m/Y', trim($datas[0]))->startOfDay();
                $dataFim = Carbon::createFromFormat('d/m/Y', trim($datas[1]))->endOfDay();
                $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
            }
        }

        if ($request->filled('termo_busca')) {
            $termo = $request->termo_busca;
            $query->where(function($q) use ($termo) {
                $q->where('numero_fatura', 'like', '%' . $termo . '%')
                  ->orWhere('observacoes', 'like', '%' . $termo . '%')
                  ->orWhere('descricao_glosa', 'like', '%' . $termo . '%')
                  ->orWhere('localizacao_fisica', 'like', '%' . $termo . '%')
                  ->orWhereHas('ocsPsa', function($sq) use ($termo) {
                      $sq->where('nome', 'like', '%' . $termo . '%');
                  });
            });
        }

        // Ordenação
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'desc');
        $query->orderBy($sort, $direction);

        // Executar a consulta sem paginação para exportar todos os resultados
        $pacotes = $query->get();

        // Nome do arquivo de exportação
        $fileName = 'pesquisa_pacotes_' . date('Y-m-d_His');
        
        // Exportar no formato solicitado
        switch ($formato) {
            case 'excel':
                return $this->exportarExcel($pacotes, $fileName);
            case 'csv':
                return $this->exportarCSV($pacotes, $fileName);
            case 'pdf':
                return $this->exportarPDF($pacotes, $fileName);
            case 'html':
                return $this->exportarHTML($pacotes, $fileName);
            default:
                return redirect()->back()->with('error', 'Formato de exportação inválido');
        }
    }

    /**
     * Exporta os dados para Excel
     */
    private function exportarExcel($pacotes, $fileName)
    {
        // Headers para o Excel
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.xlsx"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($pacotes) {
            $file = fopen('php://output', 'w');
            
            // Cabeçalhos das colunas
            fputcsv($file, [
                'ID', 'OCS/PSA', 'Número Fatura', 'Data Entrada', 'Valor Fatura',
                'Valor Glosa', 'Valor Pós Lisura', 'Valor Implantado', 'Valor Pendente',
                'Valor Recursado', 'Valor Deferido', 'Estado Geral', 'Estado Glosa', 'Localização'
            ]);
            
            // Somas totais
            $totalValorFatura = 0;
            $totalValorGlosa = 0;
            $totalValorPosLisura = 0;
            $totalValorImplantado = 0;
            $totalValorPendente = 0;
            $totalValorRecursado = 0;
            $totalValorDeferido = 0;
            
            // Linhas de dados
            foreach ($pacotes as $pacote) {
                // Somar valores
                $totalValorFatura += $pacote->valor_fatura;
                $totalValorGlosa += $pacote->valor_glosa;
                $totalValorPosLisura += $pacote->valor_pos_lisura;
                $totalValorImplantado += $pacote->valor_pago;
                $totalValorPendente += $pacote->valor_pendente;
                $totalValorRecursado += $pacote->valor_recursado;
                $totalValorDeferido += $pacote->valor_deferido;
                
                fputcsv($file, [
                    $pacote->id,
                    $pacote->ocsPsa->nome ?? 'N/A',
                    $pacote->numero_fatura,
                    $pacote->data_entrada ? $pacote->data_entrada->format('d/m/Y') : 'N/A',
                    number_format($pacote->valor_fatura, 2, ',', '.'),
                    number_format($pacote->valor_glosa, 2, ',', '.'),
                    number_format($pacote->valor_pos_lisura, 2, ',', '.'),
                    number_format($pacote->valor_pago, 2, ',', '.'),
                    number_format($pacote->valor_pendente, 2, ',', '.'),
                    number_format($pacote->valor_recursado, 2, ',', '.'),
                    number_format($pacote->valor_deferido, 2, ',', '.'),
                    $pacote->estado_geral,
                    $pacote->estado_glosa,
                    $pacote->localizacao_atual
                ]);
            }
            
            // Linha de totais
            fputcsv($file, [
                'TOTAL', '', '', '',
                number_format($totalValorFatura, 2, ',', '.'),
                number_format($totalValorGlosa, 2, ',', '.'),
                number_format($totalValorPosLisura, 2, ',', '.'),
                number_format($totalValorImplantado, 2, ',', '.'),
                number_format($totalValorPendente, 2, ',', '.'),
                number_format($totalValorRecursado, 2, ',', '.'),
                number_format($totalValorDeferido, 2, ',', '.'),
                '', '', ''
            ]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporta os dados para CSV
     */
    private function exportarCSV($pacotes, $fileName)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($pacotes) {
            $file = fopen('php://output', 'w');
            
            // Cabeçalhos das colunas
            fputcsv($file, [
                'ID', 'OCS/PSA', 'Número Fatura', 'Data Entrada', 'Valor Fatura',
                'Valor Glosa', 'Valor Pós Lisura', 'Valor Implantado', 'Valor Pendente',
                'Valor Recursado', 'Valor Deferido', 'Estado Geral', 'Estado Glosa', 'Localização'
            ]);
            
            // Somas totais
            $totalValorFatura = 0;
            $totalValorGlosa = 0;
            $totalValorPosLisura = 0;
            $totalValorImplantado = 0;
            $totalValorPendente = 0;
            $totalValorRecursado = 0;
            $totalValorDeferido = 0;
            
            // Linhas de dados
            foreach ($pacotes as $pacote) {
                // Somar valores
                $totalValorFatura += $pacote->valor_fatura;
                $totalValorGlosa += $pacote->valor_glosa;
                $totalValorPosLisura += $pacote->valor_pos_lisura;
                $totalValorImplantado += $pacote->valor_pago;
                $totalValorPendente += $pacote->valor_pendente;
                $totalValorRecursado += $pacote->valor_recursado;
                $totalValorDeferido += $pacote->valor_deferido;
                
                fputcsv($file, [
                    $pacote->id,
                    $pacote->ocsPsa->nome ?? 'N/A',
                    $pacote->numero_fatura,
                    $pacote->data_entrada ? $pacote->data_entrada->format('d/m/Y') : 'N/A',
                    number_format($pacote->valor_fatura, 2, ',', '.'),
                    number_format($pacote->valor_glosa, 2, ',', '.'),
                    number_format($pacote->valor_pos_lisura, 2, ',', '.'),
                    number_format($pacote->valor_pago, 2, ',', '.'),
                    number_format($pacote->valor_pendente, 2, ',', '.'),
                    number_format($pacote->valor_recursado, 2, ',', '.'),
                    number_format($pacote->valor_deferido, 2, ',', '.'),
                    $pacote->estado_geral,
                    $pacote->estado_glosa,
                    $pacote->localizacao_atual
                ]);
            }
            
            // Linha de totais
            fputcsv($file, [
                'TOTAL', '', '', '',
                number_format($totalValorFatura, 2, ',', '.'),
                number_format($totalValorGlosa, 2, ',', '.'),
                number_format($totalValorPosLisura, 2, ',', '.'),
                number_format($totalValorImplantado, 2, ',', '.'),
                number_format($totalValorPendente, 2, ',', '.'),
                number_format($totalValorRecursado, 2, ',', '.'),
                number_format($totalValorDeferido, 2, ',', '.'),
                '', '', ''
            ]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporta os dados para PDF com formato A4 paisagem otimizado para impressão
     */
    private function exportarPDF($pacotes, $fileName)
    {
        // HTML otimizado para impressão A4 em formato paisagem (landscape)
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>Resultado da Pesquisa - PMED 2.0</title>
            <style>
                @page { 
                    size: A4 landscape; 
                    margin: 1cm;
                }
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 10pt;
                    line-height: 1.3;
                    margin: 0;
                    padding: 0;
                    background-color: #fff;
                }
                h1 { 
                    color: #336699; 
                    font-size: 18pt;
                    text-align: center;
                    margin-bottom: 5px;
                }
                .subtitle {
                    text-align: center;
                    margin-bottom: 20px;
                    font-size: 10pt;
                    color: #666;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse;
                    margin-top: 10px;
                    page-break-inside: auto;
                }
                tr { page-break-inside: avoid; page-break-after: auto; }
                th { 
                    background-color: #f2f2f2; 
                    font-size: 9pt;
                    padding: 5px;
                    text-align: left;
                    border: 0.5px solid #ddd;
                }
                td { 
                    border: 0.5px solid #ddd; 
                    padding: 4px;
                    text-align: left; 
                    font-size: 9pt;
                    vertical-align: middle;
                }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .text-right { text-align: right; }
                .footer { 
                    position: fixed;
                    bottom: 0;
                    width: 100%;
                    text-align: center;
                    font-size: 8pt;
                    color: #666;
                    margin-top: 20px;
                    padding-top: 10px;
                    border-top: 1px solid #eee;
                }
                .header {
                    width: 100%;
                    text-align: center;
                    padding: 10px 0;
                    background-color: white;
                    margin-bottom: 15px;
                }
                .content {
                    margin-top: 15px;
                }
                .page-number:before { content: counter(page); }
                
                /* Cabeçalho com logo e informações */
                .report-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    border-bottom: 2px solid #336699;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                .logo-area {
                    text-align: left;
                    width: 20%;
                }
                .title-area {
                    text-align: center;
                    width: 60%;
                }
                .info-area {
                    text-align: right;
                    width: 20%;
                    font-size: 9pt;
                }
                .report-title {
                    font-size: 18pt;
                    color: #336699;
                    margin: 0;
                    padding: 0;
                }
                .report-subtitle {
                    font-size: 12pt;
                    color: #444;
                    margin: 5px 0 0 0;
                    padding: 0;
                }
                .summary-row {
                    background-color: #333;
                    color: white;
                    font-weight: bold;
                }
            </style>
        </head>
        <body>
            <div class="report-header">
                <div class="logo-area">
                    <!-- Logo ou identificação à esquerda -->
                    <strong style="font-size: 14pt; color: #336699;">PMED 2.0</strong>
                </div>
                <div class="title-area">
                    <div class="report-title">Relatório de Pesquisa</div>
                    <div class="report-subtitle">Sistema de Gerenciamento de Pacotes</div>
                </div>
                <div class="info-area">
                    <!-- Informações à direita -->
                    <strong>Data:</strong> ' . date('d/m/Y') . '<br>
                    <strong>Hora:</strong> ' . date('H:i:s') . '<br>
                    <strong>Total:</strong> ' . count($pacotes) . ' registros
                </div>
            </div>
            
            <div class="content">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>OCS/PSA</th>
                            <th>Nº Fatura</th>
                            <th>Data Entrada</th>
                            <th>Valor Fatura</th>
                            <th>Valor Glosa</th>
                            <th>Valor Pós Lisura</th>
                            <th>Valor Implantado</th>
                            <th>Valor Pendente</th>
                            <th>Valor Recursado</th>
                            <th>Valor Deferido</th>
                            <th>Estado Geral</th>
                            <th>Estado Glosa</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        // Somas totais
        $totalValorFatura = 0;
        $totalValorGlosa = 0;
        $totalValorPosLisura = 0;
        $totalValorImplantado = 0;
        $totalValorPendente = 0;
        $totalValorRecursado = 0;
        $totalValorDeferido = 0;
            
        foreach ($pacotes as $pacote) {
            // Somar valores
            $totalValorFatura += $pacote->valor_fatura;
            $totalValorGlosa += $pacote->valor_glosa;
            $totalValorPosLisura += $pacote->valor_pos_lisura;
            $totalValorImplantado += $pacote->valor_pago;
            $totalValorPendente += $pacote->valor_pendente;
            $totalValorRecursado += $pacote->valor_recursado;
            $totalValorDeferido += $pacote->valor_deferido;
                
            $html .= '<tr>
                    <td>' . $pacote->id . '</td>
                    <td>' . ($pacote->ocsPsa->nome ?? 'N/A') . '</td>
                    <td>' . $pacote->numero_fatura . '</td>
                    <td>' . ($pacote->data_entrada ? $pacote->data_entrada->format('d/m/Y') : 'N/A') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_fatura, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_glosa, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_pos_lisura, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_pago, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_pendente, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_recursado, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_deferido, 2, ',', '.') . '</td>
                    <td>' . $pacote->estado_geral . '</td>
                    <td>' . $pacote->estado_glosa . '</td>
                </tr>';
        }
        
        // Adicionar linha de totais
        $html .= '<tr class="summary-row">
                <td colspan="4" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right">R$ ' . number_format($totalValorFatura, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorGlosa, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorPosLisura, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorImplantado, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorPendente, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorRecursado, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorDeferido, 2, ',', '.') . '</td>
                <td colspan="2"></td>
            </tr>';
        
        $html .= '</tbody>
                </table>
            </div>
            
            <div class="footer">
                Sistema PMED 2.0 - Página <span class="page-number"></span> - Gerado em ' . date('d/m/Y H:i:s') . '
            </div>
        </body>
        </html>';
        
        // Script para conversão para PDF (omitindo para manter o código conciso)
        
        // Headers para HTML que se tornará PDF
        $headers = [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'inline; filename="' . $fileName . '.pdf"',
        ];
        
        return response($html, 200, $headers);
    }

    /**
     * Exporta os dados para HTML
     */
    private function exportarHTML($pacotes, $fileName)
    {
        // HTML para visualização direta
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <title>Resultado da Pesquisa - PMED 2.0</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 30px; }
                h1 { color: #336699; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .text-right { text-align: right; }
                .footer { margin-top: 30px; font-size: 11px; text-align: center; color: #666; }
                .summary-row { background-color: #333; color: white; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>Resultado da Pesquisa - PMED 2.0</h1>
            <p>Data de geração: ' . date('d/m/Y H:i:s') . '</p>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>OCS/PSA</th>
                        <th>Nº Fatura</th>
                        <th>Data Entrada</th>
                        <th>Valor Fatura</th>
                        <th>Valor Glosa</th>
                        <th>Valor Pós Lisura</th>
                        <th>Valor Implantado</th>
                        <th>Valor Pendente</th>
                        <th>Valor Recursado</th>
                        <th>Valor Deferido</th>
                        <th>Estado</th>
                        <th>Localização</th>
                    </tr>
                </thead>
                <tbody>';
        
        // Somas totais
        $totalValorFatura = 0;
        $totalValorGlosa = 0;
        $totalValorPosLisura = 0;
        $totalValorImplantado = 0;
        $totalValorPendente = 0;
        $totalValorRecursado = 0;
        $totalValorDeferido = 0;
            
        foreach ($pacotes as $pacote) {
            // Somar valores
            $totalValorFatura += $pacote->valor_fatura;
            $totalValorGlosa += $pacote->valor_glosa;
            $totalValorPosLisura += $pacote->valor_pos_lisura;
            $totalValorImplantado += $pacote->valor_pago;
            $totalValorPendente += $pacote->valor_pendente;
            $totalValorRecursado += $pacote->valor_recursado;
            $totalValorDeferido += $pacote->valor_deferido;
                
            $html .= '<tr>
                    <td>' . $pacote->id . '</td>
                    <td>' . ($pacote->ocsPsa->nome ?? 'N/A') . '</td>
                    <td>' . $pacote->numero_fatura . '</td>
                    <td>' . ($pacote->data_entrada ? $pacote->data_entrada->format('d/m/Y') : 'N/A') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_fatura, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_glosa, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_pos_lisura, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_pago, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_pendente, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_recursado, 2, ',', '.') . '</td>
                    <td class="text-right">R$ ' . number_format($pacote->valor_deferido, 2, ',', '.') . '</td>
                    <td>' . $pacote->estado_geral . ' / ' . $pacote->estado_glosa . '</td>
                    <td>' . $pacote->localizacao_atual . '</td>
                </tr>';
        }
        
        // Adicionar linha de totais
        $html .= '<tr class="summary-row">
                <td colspan="4" class="text-right"><strong>TOTAL:</strong></td>
                <td class="text-right">R$ ' . number_format($totalValorFatura, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorGlosa, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorPosLisura, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorImplantado, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorPendente, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorRecursado, 2, ',', '.') . '</td>
                <td class="text-right">R$ ' . number_format($totalValorDeferido, 2, ',', '.') . '</td>
                <td colspan="2"></td>
            </tr>';
        
        $html .= '</tbody>
            </table>
            
            <div class="footer">
                Este relatório foi gerado automaticamente pelo sistema PMED 2.0
            </div>
        </body>
        </html>';
        
        // Headers para HTML
        $headers = [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '.html"',
        ];
        
        return response($html, 200, $headers);
    }

    /**
     * Salva os critérios de pesquisa atual para uso futuro
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function salvarPesquisa(Request $request)
    {
        $request->validate([
            'nome_pesquisa' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'filtros_json' => 'required|string'
        ]);

        try {
            // Criar o registro da pesquisa salva
            DB::table('pesquisas_salvas')->insert([
                'nome' => $request->nome_pesquisa,
                'descricao' => $request->descricao,
                'filtros' => $request->filtros_json,
                'user_id' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Pesquisa salva com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao salvar pesquisa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar a pesquisa. Por favor, tente novamente.');
        }
    }

    /**
     * Carrega uma pesquisa salva
     * 
     * @param int $id ID da pesquisa salva
     * @return \Illuminate\Http\Response
     */
    public function carregarPesquisa($id)
    {
        $pesquisa = DB::table('pesquisas_salvas')->where('id', $id)->where('user_id', auth()->id())->first();
        
        if (!$pesquisa) {
            return redirect()->route('pesquisa.index')->with('error', 'Pesquisa não encontrada.');
        }
        
        // Converter os filtros salvos em parâmetros de URL
        $filtrosArray = json_decode($pesquisa->filtros, true);
        if (!is_array($filtrosArray)) {
            parse_str($pesquisa->filtros, $filtrosArray);
        }
        
        return redirect()->route('pesquisa.buscar', $filtrosArray);
    }

    /**
     * Lista as pesquisas salvas do usuário atual
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function listarPesquisas()
    {
        $pesquisas = DB::table('pesquisas_salvas')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($pesquisas);
    }

    /**
     * Exclui uma pesquisa salva
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function excluirPesquisa($id)
    {
        $deleted = DB::table('pesquisas_salvas')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();
        
        if ($deleted) {
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 404);
    }

    /**
     * Página para gerenciar todas as pesquisas salvas
     * 
     * @return \Illuminate\View\View
     */
    public function gerenciarPesquisas()
    {
        $pesquisas = DB::table('pesquisas_salvas')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('pesquisa.gerenciar', compact('pesquisas'));
    }
}