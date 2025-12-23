@extends('adminlte::page')

@section('title', 'Detalhes do Usuário')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Detalhes do Usuário #{{ $user->id }}</h1>
        <div>
            <a href="{{ route('usuarios.edit', $user->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informações Básicas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center mb-4">
                                <img class="profile-user-img img-fluid img-circle" 
                                     src="https://adminlte.io/themes/v3/dist/img/user{{ $user->id % 8 + 1 }}-128x128.jpg" 
                                     alt="Foto de perfil">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <dl>
                                <dt>Nome</dt>
                                <dd>{{ $user->name }}</dd>
                                
                                <dt>Email</dt>
                                <dd>{{ $user->email }}</dd>
                                
                                <dt>Equipe</dt>
                                <dd>
                                    <span class="badge 
                                        {{ $user->role == 'admin' ? 'bg-danger' : '' }}
                                        {{ $user->role == 'auditor' ? 'bg-warning' : '' }}
                                        {{ $user->role == 'protocolo' ? 'bg-info' : '' }}
                                        {{ $user->role == 'lisura' ? 'bg-success' : '' }}
                                        {{ $user->role == 'sire' ? 'bg-primary' : '' }}
                                        {{ $user->role == 'glosa' ? 'bg-secondary' : '' }}
                                        {{ $user->role == 'arquivo' ? 'bg-dark' : '' }}
                                        {{ $user->role == 'pagamento' ? 'bg-purple' : '' }}
                                    ">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </dd>
                                
                                <dt>Status</dt>
                                <dd>
                                    @if($user->active)
                                        <span class="badge bg-success">Ativo</span>
                                    @else
                                        <span class="badge bg-danger">Inativo</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informações de Acesso</h3>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Data de Criação</dt>
                        <dd>{{ $user->created_at->format('d/m/Y H:i:s') }}</dd>
                        
                        <dt>Última Atualização</dt>
                        <dd>{{ $user->updated_at->format('d/m/Y H:i:s') }}</dd>
                        
                        <dt>Último Login</dt>
                        <dd>{{ $user->last_login ? $user->last_login->format('d/m/Y H:i:s') : 'Nunca' }}</dd>
                    </dl>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ações</h3>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-warning btn-block" data-toggle="modal" data-target="#modal-reset-password">
                        <i class="fas fa-key"></i> Resetar Senha
                    </button>
                    
                    <button type="button" class="btn {{ $user->active ? 'btn-danger' : 'btn-success' }} btn-block" data-toggle="modal" data-target="#modal-toggle-status">
                        <i class="fas fa-{{ $user->active ? 'lock' : 'lock-open' }}"></i> {{ $user->active ? 'Desativar' : 'Ativar' }} Usuário
                    </button>
                    
                    <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#modal-delete">
                        <i class="fas fa-trash"></i> Excluir Usuário
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para resetar senha -->
    <div class="modal fade" id="modal-reset-password">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h4 class="modal-title">Resetar Senha</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja resetar a senha deste usuário?</p>
                    <p>A senha será alterada para <strong>brasil@123</strong>.</p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <form action="{{ route('usuarios.reset-password', $user->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning">Resetar Senha</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para ativar/desativar usuário -->
    <div class="modal fade" id="modal-toggle-status">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header {{ $user->active ? 'bg-danger' : 'bg-success' }}">
                    <h4 class="modal-title">{{ $user->active ? 'Desativar' : 'Ativar' }} Usuário</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja {{ $user->active ? 'desativar' : 'ativar' }} este usuário?</p>
                    
                    @if($user->active)
                        <p>O usuário não poderá mais acessar o sistema enquanto estiver inativo.</p>
                    @else
                        <p>O usuário poderá acessar o sistema novamente.</p>
                    @endif
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <form action="{{ route('usuarios.toggle-status', $user->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn {{ $user->active ? 'btn-danger' : 'btn-success' }}">
                            {{ $user->active ? 'Desativar' : 'Ativar' }} Usuário
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de exclusão -->
    <div class="modal fade" id="modal-delete">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title">Confirmar Exclusão</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o usuário {{ $user->name }}?</p>
                    <p><strong>Esta ação não pode ser desfeita!</strong></p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                    <form action="{{ route('usuarios.destroy', $user->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        dt {
            font-weight: bold;
            margin-bottom: 0.2rem;
        }
        dd {
            margin-bottom: 1rem;
        }
        .badge {
            font-size: 90%;
        }
        .profile-user-img {
            border: 3px solid #adb5bd;
            margin: 0 auto;
            padding: 3px;
            width: 100px;
        }
        .bg-purple {
            background-color: #6f42c1 !important;
            color: #fff !important;
        }
    </style>
@stop