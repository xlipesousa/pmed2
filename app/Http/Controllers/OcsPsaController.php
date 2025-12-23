<?php

namespace App\Http\Controllers;

use App\Models\OcsPsa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Adicionar essa importação

class OcsPsaController extends Controller
{
    /**
     * Exibe a lista de OCS/PSA
     */
    public function index()
    {
        $ocspsaList = OcsPsa::all();
        return view('configuracoes.ocspsa', compact('ocspsaList'));
    }

    /**
     * Armazena uma nova OCS/PSA
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo_interno' => 'required|string|max:50|unique:ocs_psa,codigo_interno',
        ]);

        $ativo = $request->has('ativo') ? true : false;

        OcsPsa::create([
            'nome' => $request->nome,
            'codigo_interno' => $request->codigo_interno,
            'ativo' => $ativo
        ]);

        return redirect()->route('configuracoes.ocspsa')
            ->with('success', 'OCS/PSA cadastrada com sucesso!');
    }

    /**
     * Atualiza uma OCS/PSA existente
     */
    public function update(Request $request, $id)
    {
        $ocsPsa = OcsPsa::findOrFail($id);
        
        $request->validate([
            'nome' => 'required|string|max:255',
            'codigo_interno' => 'required|string|max:50|unique:ocs_psa,codigo_interno,' . $id,
        ]);

        $ativo = $request->has('ativo') ? true : false;

        $ocsPsa->update([
            'nome' => $request->nome,
            'codigo_interno' => $request->codigo_interno,
            'ativo' => $ativo
        ]);

        return redirect()->route('configuracoes.ocspsa')
            ->with('success', 'OCS/PSA atualizada com sucesso!');
    }

    /**
     * Remove uma OCS/PSA
     */
    public function destroy($id)
    {
        $ocsPsa = OcsPsa::findOrFail($id);
        
        // Verificar se existem pacotes vinculados
        if ($ocsPsa->pacotes()->count() > 0) {
            return back()->with('error', 'Esta OCS/PSA não pode ser excluída porque possui pacotes vinculados.');
        }
        
        $ocsPsa->delete();
        
        return redirect()->route('configuracoes.ocspsa')
            ->with('success', 'OCS/PSA excluída com sucesso!');
    }

    /**
     * Exibe o formulário para editar OCS/PSA
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $ocsPsa = OcsPsa::findOrFail($id);
        return view('configuracoes.ocspsa', compact('ocsPsa'));
    }

    /**
     * Alternar o status de ativo/inativo da OCS/PSA
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request, $id)
    {
        try {
            $ocsPsa = OcsPsa::findOrFail($id);
            
            // Atualizar o status
            $ocsPsa->ativo = $request->input('ativo') ? true : false;
            $ocsPsa->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Status atualizado com sucesso',
                'ativo' => $ocsPsa->ativo
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao alternar status de OCS/PSA: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status'
            ], 500);
        }
    }
}