@extends('adminlte::page')

@section('title', 'Meu Perfil')

@section('content_header')
    <h1>Meu Perfil</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Dados Pessoais -->
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Dados Pessoais</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('perfil.atualizar') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="name">Nome</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label>Perfil</label>
                            <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Atualizar Dados
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Alterar Senha -->
        <div class="col-md-6">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">Alterar Senha</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('perfil.senha') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label for="senha_atual">Senha Atual</label>
                            <input type="password" class="form-control @error('senha_atual') is-invalid @enderror" id="senha_atual" name="senha_atual" required>
                            @error('senha_atual')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Nova Senha</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key"></i> Alterar Senha
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Informações da Conta -->
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Informações da Conta</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Último acesso</dt>
                        <dd>
                            @if($user->last_login)
                                {{ $user->last_login->format('d/m/Y H:i:s') }}
                            @else
                                <span class="text-muted">Não disponível</span>
                            @endif
                        </dd>
                        
                        <dt>Status da conta</dt>
                        <dd>
                            @if($user->active)
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-danger">Inativo</span>
                            @endif
                        </dd>
                        
                        <dt>Conta criada em</dt>
                        <dd>{{ $user->created_at->format('d/m/Y H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-group {
            margin-bottom: 1.5rem;
        }
        dt {
            font-weight: bold;
            margin-bottom: 0.2rem;
        }
        dd {
            margin-bottom: 1rem;
        }
    </style>
@stop

@section('js')
    <script>
        // Script específico para a página de perfil, se necessário
    </script>
@stop