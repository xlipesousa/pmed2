@extends('configuracoes.layout')

@section('configuracoes_content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form action="{{ route('configuracoes.sistema.salvar') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <!-- Configurações de Identidade Visual -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Identidade Visual</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Seção do Logo -->
                    <div class="col-md-6">
                        <div class="card card-body bg-light">
                            <h5>Logo do Sistema</h5>
                            <div class="form-group">
                                <label>Logo Atual</label>
                                <div class="text-center p-3 bg-white border rounded">
                                    <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}?v={{ time() }}" 
                                         alt="Logo Atual" class="img-fluid" style="max-width: 130px;">
                                    <p class="text-muted mt-2">Dimensões recomendadas: 130x130 pixels</p>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="novo_logo">Substituir Logo</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="novo_logo" name="novo_logo" accept="image/png, image/jpeg, image/svg+xml">
                                        <label class="custom-file-label" for="novo_logo">Escolher arquivo</label>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Formatos aceitos: PNG, JPEG, SVG. Tamanho máximo: 2MB.
                                    O novo logo será redimensionado para 130x130 pixels automaticamente.
                                </small>
                                
                                <div class="mt-3" id="logo_preview_container" style="display:none;">
                                    <label>Pré-visualização:</label>
                                    <div class="text-center p-3 bg-white border rounded">
                                        <img id="logo_preview" src="#" alt="Pré-visualização do novo logo" 
                                             class="img-fluid" style="max-width: 130px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção do Favicon -->
                    <div class="col-md-6">
                        <div class="card card-body bg-light">
                            <h5>Favicon do Site</h5>
                            <div class="form-group">
                                <label>Favicon Atual</label>
                                <div class="text-center p-3 bg-white border rounded">
                                    <img src="{{ asset('favicon.ico') }}?v={{ time() }}" 
                                         alt="Favicon Atual" class="img-fluid" style="width: 32px;">
                                    <p class="text-muted mt-2">Dimensões recomendadas: 32x32 pixels</p>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="novo_favicon">Substituir Favicon</label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="novo_favicon" name="novo_favicon" accept="image/x-icon, image/png, image/jpeg, image/svg+xml">
                                        <label class="custom-file-label" for="novo_favicon">Escolher arquivo</label>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Formatos aceitos: ICO, PNG, JPEG, SVG. Tamanho máximo: 1MB.
                                    O novo favicon será redimensionado para 32x32 pixels automaticamente.
                                </small>
                                
                                <div class="mt-3" id="favicon_preview_container" style="display:none;">
                                    <label>Pré-visualização:</label>
                                    <div class="text-center p-3 bg-white border rounded">
                                        <img id="favicon_preview" src="#" alt="Pré-visualização do novo favicon" 
                                             class="img-fluid" style="width: 32px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações de Tipos de Pacotes -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tipo de Pacote</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-novo-tipo">
                        <i class="fas fa-plus"></i> Adicionar Tipo
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">Gerencie os tipos de pacotes disponíveis na criação de novos pacotes</p>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Nome do Tipo</th>
                            <th>Descrição</th>
                            <th style="width: 120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiposPacote as $tipo)
                            <tr>
                                <td>{{ $tipo->id }}</td>
                                <td>{{ $tipo->nome }}</td>
                                <td>{{ $tipo->descricao }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-editar-tipo-{{ $tipo->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modal-excluir-tipo-{{ $tipo->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal para editar tipo -->
                                    <div class="modal fade" id="modal-editar-tipo-{{ $tipo->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info">
                                                    <h4 class="modal-title">Editar Tipo de Pacote</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('configuracoes.tipos-pacote.editar', $tipo->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="nome-{{ $tipo->id }}">Nome do Tipo</label>
                                                            <input type="text" class="form-control" id="nome-{{ $tipo->id }}" name="nome" value="{{ $tipo->nome }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="descricao-{{ $tipo->id }}">Descrição</label>
                                                            <input type="text" class="form-control" id="descricao-{{ $tipo->id }}" name="descricao" value="{{ $tipo->descricao }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-info">Salvar Alterações</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal para excluir tipo -->
                                    <div class="modal fade" id="modal-excluir-tipo-{{ $tipo->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger">
                                                    <h4 class="modal-title">Confirmar Exclusão</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Tem certeza que deseja excluir o tipo de pacote <strong>{{ $tipo->nome }}</strong>?</p>
                                                    <p><strong>Atenção:</strong> Esta ação não pode ser desfeita e pode afetar pacotes existentes.</p>
                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('configuracoes.tipos-pacote.excluir', $tipo->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Nenhum tipo de pacote cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Configurações de Tipos de Conta -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Tipos de Conta</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-novo-tipo-conta">
                        <i class="fas fa-plus"></i> Adicionar Tipo de Conta
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">Gerencie os tipos de conta disponíveis para o setor de Lisura</p>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Tipo de Conta</th>
                            <th>Descrição</th>
                            <th style="width: 120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tiposConta as $tipo)
                            <tr>
                                <td>{{ $tipo->id }}</td>
                                <td>{{ $tipo->nome }}</td>
                                <td>{{ $tipo->descricao }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-editar-tipo-conta-{{ $tipo->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modal-excluir-tipo-conta-{{ $tipo->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal para editar tipo de conta -->
                                    <div class="modal fade" id="modal-editar-tipo-conta-{{ $tipo->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info">
                                                    <h4 class="modal-title">Editar Tipo de Conta</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('configuracoes.tipos-conta.editar', $tipo->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="nome-conta-{{ $tipo->id }}">Nome do Tipo</label>
                                                            <input type="text" class="form-control" id="nome-conta-{{ $tipo->id }}" name="nome" value="{{ $tipo->nome }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="descricao-conta-{{ $tipo->id }}">Descrição</label>
                                                            <input type="text" class="form-control" id="descricao-conta-{{ $tipo->id }}" name="descricao" value="{{ $tipo->descricao }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-info">Salvar Alterações</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal para excluir tipo de conta -->
                                    <div class="modal fade" id="modal-excluir-tipo-conta-{{ $tipo->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger">
                                                    <h4 class="modal-title">Confirmar Exclusão</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Tem certeza que deseja excluir o tipo de conta <strong>{{ $tipo->nome }}</strong>?</p>
                                                    <p><strong>Atenção:</strong> Esta ação não pode ser desfeita e pode afetar contas existentes.</p>
                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('configuracoes.tipos-conta.excluir', $tipo->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Nenhum tipo de conta cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Configurações de Motivos de Glosa -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Motivos de Glosa</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-novo-motivo-glosa">
                        <i class="fas fa-plus"></i> Adicionar Motivo
                    </button>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted">Gerencie os motivos de glosa disponíveis para o setor de Lisura</p>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 10px">#</th>
                            <th>Motivo</th>
                            <th>Descrição</th>
                            <th style="width: 120px">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($motivosGlosa as $motivo)
                            <tr>
                                <td>{{ $motivo->id }}</td>
                                <td>{{ $motivo->nome }}</td>
                                <td>{{ $motivo->descricao }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-editar-motivo-{{ $motivo->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#modal-excluir-motivo-{{ $motivo->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Modal para editar motivo -->
                                    <div class="modal fade" id="modal-editar-motivo-{{ $motivo->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info">
                                                    <h4 class="modal-title">Editar Motivo de Glosa</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <form action="{{ route('configuracoes.motivos-glosa.editar', $motivo->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="nome-motivo-{{ $motivo->id }}">Nome do Motivo</label>
                                                            <input type="text" class="form-control" id="nome-motivo-{{ $motivo->id }}" name="nome" value="{{ $motivo->nome }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="descricao-motivo-{{ $motivo->id }}">Descrição</label>
                                                            <input type="text" class="form-control" id="descricao-motivo-{{ $motivo->id }}" name="descricao" value="{{ $motivo->descricao }}">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer justify-content-between">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-info">Salvar Alterações</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Modal para excluir motivo -->
                                    <div class="modal fade" id="modal-excluir-motivo-{{ $motivo->id }}">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger">
                                                    <h4 class="modal-title">Confirmar Exclusão</h4>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Tem certeza que deseja excluir o motivo de glosa <strong>{{ $motivo->nome }}</strong>?</p>
                                                    <p><strong>Atenção:</strong> Esta ação não pode ser desfeita e pode afetar glosas existentes.</p>
                                                </div>
                                                <div class="modal-footer justify-content-between">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('configuracoes.motivos-glosa.excluir', $motivo->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Nenhum motivo de glosa cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Configurações de Notificação (mantidas conforme requisitos) -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Configurações de Notificação</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="notificar_limite_credito" name="notificar_limite_credito" checked>
                        <label class="custom-control-label" for="notificar_limite_credito">Notificar quando pacotes estiverem aguardando limite de crédito por mais de 15 dias</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="notificar_recurso_glosa" name="notificar_recurso_glosa" checked>
                        <label class="custom-control-label" for="notificar_recurso_glosa">Notificar quando recursos de glosa estiverem próximos do prazo de vencimento</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="backup_automatico" name="backup_automatico" checked>
                        <label class="custom-control-label" for="backup_automatico">Realizar backup automático diário</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right mb-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Configurações</button>
            <button type="reset" class="btn btn-secondary"><i class="fas fa-undo"></i> Restaurar Padrões</button>
        </div>
    </form>

    <!-- Modais para adição de novos itens -->
    <!-- Modal para adicionar novo tipo de pacote -->
    <div class="modal fade" id="modal-novo-tipo">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Adicionar Novo Tipo de Pacote</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('configuracoes.tipos-pacote.adicionar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nome">Nome do Tipo</label>
                            <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: Consulta" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Breve descrição deste tipo de pacote">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar novo tipo de conta -->
    <div class="modal fade" id="modal-novo-tipo-conta">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Adicionar Novo Tipo de Conta</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('configuracoes.tipos-conta.adicionar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nome-tipo-conta">Nome do Tipo</label>
                            <input type="text" class="form-control" id="nome-tipo-conta" name="nome" placeholder="Ex: Ambulatorial" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao-tipo-conta">Descrição</label>
                            <input type="text" class="form-control" id="descricao-tipo-conta" name="descricao" placeholder="Breve descrição deste tipo de conta">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar novo motivo de glosa -->
    <div class="modal fade" id="modal-novo-motivo-glosa">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">Adicionar Novo Motivo de Glosa</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('configuracoes.motivos-glosa.adicionar') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nome-motivo">Nome do Motivo</label>
                            <input type="text" class="form-control" id="nome-motivo" name="nome" placeholder="Ex: Cobrança indevida" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao-motivo">Descrição</label>
                            <input type="text" class="form-control" id="descricao-motivo" name="descricao" placeholder="Breve descrição deste motivo de glosa">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('configuracoes_js')
<script>
    $(document).ready(function() {
        // Modificado para não afetar botões de Tipo de Pacote, Tipo de Conta e Motivos de Glosa
        $('.btn-primary').not('[form]')
                         .not('[type="submit"]')
                         .not('[data-target="#modal-novo-tipo"]')
                         .not('[data-target="#modal-novo-tipo-conta"]')
                         .not('[data-target="#modal-novo-motivo-glosa"]')
                         .on('click', function() {
            // No futuro, implementar código AJAX para salvar
            alert("Esta funcionalidade será implementada em breve!");
        });

        // Pré-visualização do logo
        $('#novo_logo').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#logo_preview').attr('src', e.target.result);
                    $('#logo_preview_container').show();
                }
                
                reader.readAsDataURL(file);
                
                // Atualizar o label do input file para mostrar o nome do arquivo
                $(this).next('.custom-file-label').html(file.name);
            } else {
                $('#logo_preview_container').hide();
                $(this).next('.custom-file-label').html('Escolher arquivo');
            }
        });
        
        // Pré-visualização do favicon
        $('#novo_favicon').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    $('#favicon_preview').attr('src', e.target.result);
                    $('#favicon_preview_container').show();
                }
                
                reader.readAsDataURL(file);
                
                // Atualizar o label do input file para mostrar o nome do arquivo
                $(this).next('.custom-file-label').html(file.name);
            } else {
                $('#favicon_preview_container').hide();
                $(this).next('.custom-file-label').html('Escolher arquivo');
            }
        });
    });
</script>
@endsection