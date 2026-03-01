<?php

namespace App\Http\Controllers;

use App\Models\Mapa;
use App\Models\Pacote;
use App\Models\MapaPacote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class MapaController extends Controller
{
    public function __construct()
    {
        // Remover middleware anterior que bloqueava totalmente o acesso
        // e usar middleware específicos em cada rota
    }

    public function index()
    {
        $mapas = Mapa::withCount('pacotes')->orderBy('created_at', 'desc')->get();
        return view('mapas.index', compact('mapas'));
    }

    public function create()
    {
        $this->authorize('mapas-manage');
        return view('mapas.create');
    }

    public function store(Request $request)
    {
        $this->authorize('mapas-manage');
        $request->validate([
            'numero_mapa' => 'required|string|unique:mapas',
            'data_criacao' => 'required|date',
        ]);

        $mapa = Mapa::create($request->all());
        
        return redirect()->route('mapas.show', $mapa->id)->with('success', 'Mapa de pagamento criado com sucesso. Agora adicione faturas a este mapa.');
    }

    public function show($id)
    {
        $mapa = Mapa::with(['pacotes' => function($query) {
            $query->orderBy('data_entrada', 'desc');
        }])->findOrFail($id);
        
        // Carregar faturas com valor pendente > 0 incluindo informações da OCS/PSA
        $pacotes = Pacote::with('ocsPsa')
            ->where('valor_pendente', '>', 0)
            ->orderBy('numero_fatura')
            ->get();
        
        // Calcular o total pago (empenhado) no mapa
        $totalPago = $mapa->pacotes->sum(function($pacote) {
            return $pacote->pivot->valor_parcial;
        });
        
        return view('mapas.show', compact('mapa', 'pacotes', 'totalPago'));
    }

    public function edit($id)
    {
        $mapa = Mapa::findOrFail($id);
        return view('mapas.edit', compact('mapa'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'numero_mapa' => 'required|string|unique:mapas,numero_mapa,'.$id,
            'data_criacao' => 'required|date',
        ]);

        $mapa = Mapa::findOrFail($id);
        $mapa->update($request->all());
        
        return redirect()->route('mapas.show', $mapa->id)->with('success', 'Mapa de pagamento atualizado com sucesso');
    }

    public function destroy($id)
    {
        $mapa = Mapa::findOrFail($id);
        $mapa->delete();
        
        return redirect()->route('mapas.index')->with('success', 'Mapa de pagamento excluído com sucesso');
    }

    public function adicionarFatura(Request $request, $id)
    {
        $request->validate([
            'pacote_id' => 'required|exists:pacotes,id',
            'valor_parcial' => 'required|numeric|min:0',
            'empenho' => 'nullable|string|max:255',
            'data_empenho' => 'nullable|date',
            'nota_fiscal' => 'nullable|string|max:255',
            'data_nota_fiscal' => 'nullable|date',
        ]);

        $mapa = Mapa::findOrFail($id);
        $pacote = Pacote::findOrFail($request->pacote_id);

        // Verificar se o valor parcial não excede o valor pendente
        if ($request->valor_parcial > $pacote->valor_pendente) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['valor_parcial' => 'O valor parcial não pode exceder o valor pendente da fatura.']);
        }

        // Verificar se o pacote já está no mapa
        if ($mapa->pacotes()->where('pacote_id', $pacote->id)->exists()) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['pacote_id' => 'Esta fatura já está associada a este mapa.']);
        }

        // Adicionar fatura ao mapa
        $mapa->pacotes()->attach($pacote->id, [
            'valor_parcial' => $request->valor_parcial,
            'empenho' => $request->empenho,
            'data_empenho' => $request->data_empenho,
            'nota_fiscal' => $request->nota_fiscal,
            'data_nota_fiscal' => $request->data_nota_fiscal,
        ]);

        return redirect()->route('mapas.show', $mapa->id)->with('success', 'Fatura adicionada ao mapa com sucesso.');
    }

    public function removerFatura($mapaId, $pacoteId)
    {
        $mapa = Mapa::findOrFail($mapaId);
        $mapa->pacotes()->detach($pacoteId);
        
        return redirect()->route('mapas.show', $mapa->id)->with('success', 'Fatura removida do mapa com sucesso.');
    }

    public function editarFatura($mapaId, $pacoteId)
    {
        $mapa = Mapa::findOrFail($mapaId);
        $pacote = Pacote::findOrFail($pacoteId);
        $mapaPacote = MapaPacote::where('mapa_id', $mapaId)->where('pacote_id', $pacoteId)->firstOrFail();
        
        return view('mapas.editar-fatura', compact('mapa', 'pacote', 'mapaPacote'));
    }
    
    public function atualizarFatura(Request $request, $mapaId, $pacoteId)
    {
        $request->validate([
            'valor_parcial' => 'required|numeric|min:0',
            'empenho' => 'nullable|string|max:255',
            'data_empenho' => 'nullable|date',
            'nota_fiscal' => 'nullable|string|max:255',
            'data_nota_fiscal' => 'nullable|date',
        ]);

        $mapa = Mapa::findOrFail($mapaId);
        $pacote = Pacote::findOrFail($pacoteId);
        
        // Verificar se o valor parcial não excede o valor pendente
        $mapaPacote = MapaPacote::where('mapa_id', $mapaId)->where('pacote_id', $pacoteId)->firstOrFail();
        
        if ($request->valor_parcial > ($pacote->valor_pendente + $mapaPacote->valor_parcial)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['valor_parcial' => 'O valor parcial não pode exceder o valor pendente da fatura.']);
        }

        // Atualizar os dados na tabela pivot
        $mapa->pacotes()->updateExistingPivot($pacote->id, [
            'valor_parcial' => $request->valor_parcial,
            'empenho' => $request->empenho,
            'data_empenho' => $request->data_empenho,
            'nota_fiscal' => $request->nota_fiscal,
            'data_nota_fiscal' => $request->data_nota_fiscal,
        ]);

        return redirect()->route('mapas.show', $mapa->id)->with('success', 'Dados da fatura atualizados com sucesso.');
    }

    public function pesquisa()
    {
        // Carregar faturas com informações da OCS/PSA
        $pacotes = Pacote::with('ocsPsa')
            ->where('valor_pendente', '>', 0)
            ->orderBy('numero_fatura')
            ->get();
        
        $numeroMapas = Mapa::select('id', 'numero_mapa')->orderBy('numero_mapa')->get();
        
        return view('mapas.pesquisa', compact('pacotes', 'numeroMapas'));
    }

    public function buscar(Request $request)
    {
        $query = Mapa::withCount('pacotes');

        if ($request->numero_mapa) {
            $query->where('numero_mapa', 'like', '%' . $request->numero_mapa . '%');
        }

        if ($request->data_criacao) {
            $query->whereDate('data_criacao', $request->data_criacao);
        }

        if ($request->pacote_id) {
            $pacoteId = $request->pacote_id;
            $query->whereHas('pacotes', function ($q) use ($pacoteId) {
                $q->where('pacote_id', $pacoteId);
            });
        }

        if ($request->empenho) {
            $empenho = $request->empenho;
            $query->whereHas('mapaPacotes', function ($q) use ($empenho) {
                $q->where('empenho', 'like', '%' . $empenho . '%');
            });
        }

        if ($request->nota_fiscal) {
            $notaFiscal = $request->nota_fiscal;
            $query->whereHas('mapaPacotes', function ($q) use ($notaFiscal) {
                $q->where('nota_fiscal', 'like', '%' . $notaFiscal . '%');
            });
        }

        $mapas = $query->orderBy('created_at', 'desc')->get();
        $pacotes = Pacote::all();
        return view('mapas.pesquisa', compact('mapas', 'pacotes'));
    }
    
    public function visualizarPacote($id)
    {
        $pacote = Pacote::with('mapas')->findOrFail($id);
        return view('mapas.visualizar-pacote', compact('pacote'));
    }

    // Adicionar novo método para exportação
    public function exportar($id, $formato = 'html')
    {
        $mapa = Mapa::with('pacotes')->findOrFail($id);
        
        // Calcular o total pago (empenhado) no mapa
        $totalPago = $mapa->pacotes->sum(function($pacote) {
            return $pacote->pivot->valor_parcial;
        });
        
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('mapas.exportar-pdf', compact('mapa', 'totalPago'));
            return $pdf->download('mapa-' . $mapa->numero_mapa . '.pdf');
        }
        
        return view('mapas.exportar', compact('mapa', 'totalPago'));
    }

    /**
     * Exibe o formulário de pesquisa de faturas
     */
    public function pesquisaFatura()
    {
        // Carregar faturas com informações da OCS/PSA
        $pacotes = Pacote::with('ocsPsa')
            ->where('valor_pendente', '>', 0)
            ->orderBy('numero_fatura')
            ->get();
        
        return view('mapas.pesquisa-fatura', compact('pacotes'));
    }

    /**
     * Processa a busca de faturas
     */
    public function buscarFatura(Request $request)
    {
        $request->validate([
            'pacote_id' => 'nullable|exists:pacotes,id',
            'numero_fatura' => 'nullable|string',
            'empenho' => 'nullable|string',
            'nota_fiscal' => 'nullable|string',
        ]);
        
        // Recuperamos o pacote
        $pacote = null;
        
        if ($request->pacote_id) {
            $pacote = Pacote::with(['mapas' => function($query) {
                $query->orderBy('data_criacao', 'desc');
            }])->findOrFail($request->pacote_id);
        } elseif ($request->numero_fatura) {
            $pacote = Pacote::with(['mapas' => function($query) use ($request) {
                // Filtros adicionais para empenho e nota fiscal
                if ($request->empenho) {
                    $query->wherePivot('empenho', 'like', '%' . $request->empenho . '%');
                }
                if ($request->nota_fiscal) {
                    $query->wherePivot('nota_fiscal', 'like', '%' . $request->nota_fiscal . '%');
                }
                $query->orderBy('data_criacao', 'desc');
            }])
            ->where('numero_fatura', 'like', '%' . $request->numero_fatura . '%')
            ->first();
        }
        
        // Calcular o total pago (empenhado) da fatura em todos os mapas
        $totalPago = 0;
        if ($pacote && $pacote->mapas) {
            $totalPago = $pacote->mapas->sum(function($mapa) {
                return $mapa->pivot->valor_parcial;
            });
        }
        
        $pacotes = Pacote::with('ocsPsa')
            ->where('valor_pendente', '>', 0)
            ->orderBy('numero_fatura')
            ->get();
    
        return view('mapas.pesquisa-fatura', compact('pacote', 'pacotes', 'totalPago'));
    }

    /**
     * Exporta os detalhes de uma fatura e seus mapas relacionados
     */
    public function exportarFatura($id, $formato = 'html')
    {
        $pacote = Pacote::with(['mapas', 'ocsPsa'])->findOrFail($id);
        
        // Calcular o total pago (empenhado) da fatura em todos os mapas
        $totalPago = $pacote->mapas->sum(function($mapa) {
            return $mapa->pivot->valor_parcial;
        });
        
        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('mapas.fatura-exportar-pdf', compact('pacote', 'totalPago'));
            return $pdf->download('fatura-' . $pacote->numero_fatura . '.pdf');
        }
        
        return view('mapas.fatura-exportar', compact('pacote', 'totalPago'));
    }
}
