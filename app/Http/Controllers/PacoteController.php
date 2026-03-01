<?php

namespace App\Http\Controllers;

use App\Models\Pacote;
use Illuminate\Http\Request;

class PacoteController extends Controller
{
    /**
     * ATUALIZAR: Index - excluir anulados
     */
    public function index()
    {
        // Usar scope válidos para excluir anulados
        $pacotes = Pacote::with(['ocsPsa', 'tipoPacote', 'tipoConta'])
            ->validos() // FILTRO INTEGRADO
            ->orderBy('data_entrada', 'desc')
            ->get();
            
        return view('pacotes.index', compact('pacotes'));
    }
    
    public function create()
    {
        return view('pacotes.criar');
    }
    
    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'ocs_psa' => 'required',
            'numero_fatura' => 'required|string|max:255',
            'data_entrada' => 'required',
            'valor_fatura' => 'required',
            'tipo' => 'required|string',
            'tipo_conta' => 'required|string',
            'observacoes' => 'nullable|string',
        ]);

        // Em um sistema real, aqui seria feito o salvamento no banco de dados
        
        return redirect()->route('pacotes.index')->with('success', 'Pacote criado com sucesso!');
    }
    
    public function show($id)
    {
        return view('pacotes.ver', ['id' => $id]);
    }
    
    public function movimentacoes($id)
    {
        // No futuro, buscar as movimentações reais do pacote no banco de dados
        return view('pacotes.movimentacoes', ['id' => $id]);
    }

    public function edit($id)
    {
        // Aqui buscaríamos os dados do pacote no banco de dados
        return view('pacotes.editar_protocolo', ['id' => $id]);
    }

    public function update(Request $request, $id)
    {
        // Validação dos dados
        $validated = $request->validate([
            'ocs_psa' => 'required',
            'numero_fatura' => 'required|string|max:255',
            'data_entrada' => 'required',
            'valor_fatura' => 'required',
            'tipo' => 'required|string',
            'observacoes' => 'nullable|string',
        ]);
        
        // Em um sistema real, aqui seria feita a atualização no banco de dados
        
        return redirect()->route('pacotes.show', ['id' => $id])
                         ->with('success', 'Pacote atualizado com sucesso!');
    }

    public function editLisura($id)
    {
        // No futuro, buscaríamos os dados do pacote no banco de dados
        return view('pacotes.editar_lisura', ['id' => $id]);
    }

    public function updateLisura(Request $request, $id)
    {
        // Validação dos dados
        $validated = $request->validate([
            'tipo_conta' => 'required|string',
            'glosa' => 'required',
            'motivo_glosa' => 'nullable',
            'descricao_glosa' => 'nullable|string',
            'estado_glosa' => 'required',
            'observacoes_lisura' => 'nullable|string',
        ]);
        
        // Em um sistema real, aqui seria feita a atualização no banco de dados
        
        return redirect()->route('pacotes.show', ['id' => $id])
                         ->with('success', 'Pacote atualizado com sucesso pela Lisura!');
    }
}