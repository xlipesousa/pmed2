@extends('adminlte::page')

@section('title', 'Novo Usuário')

@section('content_header')
    <h1>Novo Usuário</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('usuarios.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Nome</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Nome completo" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="email@exemplo.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Digite a senha" required>
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirmar Senha</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirme a senha" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Equipe</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="" disabled selected>Selecione uma equipe</option>
                        <option value="admin">Administrador</option>
                        <option value="auditor">Auditor</option>
                        <option value="protocolo">Protocolo</option>
                        <option value="lisura">Lisura</option>
                        <option value="sire">SIRE</option>
                        <option value="glosa">Glosa</option>
                        <option value="arquivo">Arquivo</option>
                        <option value="pagamento">Pagamento</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="active" name="active" checked>
                        <label class="custom-control-label" for="active">Usuário ativo</label>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar
                    </button>
                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Estilos específicos desta página, se necessário */
    </style>
@stop

@section('js')
    <script>
        // Scripts específicos desta página, se necessário
    </script>
@stop