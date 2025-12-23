@extends('adminlte::page')

@section('title', 'Editar Usuário')

@section('content_header')
    <h1>Editar Usuário #{{ $user->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('usuarios.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" class="form-control" id="nome" name="nome" value="{{ $user->name }}" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Nova Senha (deixe em branco para manter a atual)</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Digite a nova senha">
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Nova Senha</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirme a nova senha">
                </div>
                
                <div class="form-group">
                    <label for="role">Equipe</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="auditor" {{ $user->role == 'auditor' ? 'selected' : '' }}>Auditor</option>
                        <option value="protocolo" {{ $user->role == 'protocolo' ? 'selected' : '' }}>Protocolo</option>
                        <option value="lisura" {{ $user->role == 'lisura' ? 'selected' : '' }}>Lisura</option>
                        <option value="sire" {{ $user->role == 'sire' ? 'selected' : '' }}>SIRE</option>
                        <option value="glosa" {{ $user->role == 'glosa' ? 'selected' : '' }}>Glosa</option>
                        <option value="arquivo" {{ $user->role == 'arquivo' ? 'selected' : '' }}>Arquivo</option>
                        <option value="pagamento" {{ $user->role == 'pagamento' ? 'selected' : '' }}>Pagamento</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="active" name="active" {{ $user->active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="active">Usuário ativo</label>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop