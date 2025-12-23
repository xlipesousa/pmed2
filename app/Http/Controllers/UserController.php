<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Exibir lista de usuários
     */
    public function index()
    {
        $users = User::all();
        return view('usuarios.index', compact('users'));
    }

    /**
     * Exibir formulário para criar novo usuário
     */
    public function create()
    {
        return view('usuarios.create');
    }

    /**
     * Armazenar novo usuário
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->active = $request->has('active');
        $user->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    /**
     * Exibir detalhes do usuário
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return view('usuarios.show', compact('user'));
    }

    /**
     * Exibir formulário para editar usuário
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('usuarios.edit', compact('user'));
    }

    /**
     * Atualizar usuário
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|string',
        ]);

        // Atualizar dados básicos
        $user->name = $request->nome;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->active = $request->has('active');

        // Atualizar senha apenas se foi fornecida
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Remover usuário
     */
    public function destroy($id)
    {
        // Evitar que o próprio usuário se exclua
        if (auth()->id() == $id) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        User::findOrFail($id)->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }

    /**
     * Resetar a senha do usuário para 'brasil@123'
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        
        // Define a nova senha padrão
        $user->password = Hash::make('brasil@123');
        $user->save();
        
        return redirect()->route('usuarios.show', $id)
            ->with('success', 'Senha do usuário resetada com sucesso para "brasil@123".');
    }

    /**
     * Alternar o status de ativação do usuário
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Evitar que o próprio usuário desative sua conta
        if (auth()->id() == $id) {
            return redirect()->route('usuarios.show', $id)
                ->with('error', 'Você não pode desativar seu próprio usuário.');
        }
        
        // Inverter o estado atual
        $user->active = !$user->active;
        $user->save();
        
        $status = $user->active ? 'ativado' : 'desativado';
        
        return redirect()->route('usuarios.show', $id)
            ->with('success', "Usuário {$status} com sucesso.");
    }

    /**
     * Exibe a página de perfil do usuário
     */
    public function perfil()
    {
        $user = auth()->user();
        return view('usuarios.perfil', compact('user'));
    }

    /**
     * Atualiza os dados do perfil do usuário
     */
    public function atualizarPerfil(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);
        
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        
        return redirect()->route('perfil')->with('success', 'Perfil atualizado com sucesso.');
    }

    /**
     * Atualiza a senha do usuário
     */
    public function atualizarSenha(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'senha_atual' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        // Verificar se a senha atual está correta
        if (!Hash::check($request->senha_atual, $user->password)) {
            return back()->withErrors(['senha_atual' => 'A senha atual está incorreta.']);
        }
        
        $user->password = Hash::make($request->password);
        $user->save();
        
        return redirect()->route('perfil')->with('success', 'Senha atualizada com sucesso.');
    }
}