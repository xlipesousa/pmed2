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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">OCS/PSA Cadastradas: {{ count($ocspsaList) }}</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-criar-ocspsa">
                    <i class="fas fa-plus"></i> Nova OCS/PSA
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="tabela-ocspsa" class="table table-bordered table-striped dataTable">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Código Interno</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ocspsaList as $ocspsa)
                        <tr>
                            <td>{{ $ocspsa->nome }}</td>
                            <td>{{ $ocspsa->codigo_interno }}</td>
                            <td>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input status-switch" id="status-{{ $ocspsa->id }}" data-id="{{ $ocspsa->id }}" {{ $ocspsa->ativo ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="status-{{ $ocspsa->id }}">{{ $ocspsa->ativo ? 'Ativa' : 'Inativa' }}</label>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#modal-editar-ocspsa-{{ $ocspsa->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#modal-excluir-ocspsa-{{ $ocspsa->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Modal de Edição -->
                        <div class="modal fade" id="modal-editar-ocspsa-{{ $ocspsa->id }}" tabindex="-1" role="dialog" aria-labelledby="modalEditarOcsPsa" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('configuracoes.ocspsa.update', $ocspsa->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header bg-info">
                                            <h5 class="modal-title">Editar OCS/PSA</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="nome-{{ $ocspsa->id }}">Nome</label>
                                                <input type="text" class="form-control" id="nome-{{ $ocspsa->id }}" name="nome" value="{{ $ocspsa->nome }}">
                                            </div>
                                            <div class="form-group">
                                                <label for="codigo-{{ $ocspsa->id }}">Código Interno</label>
                                                <input type="text" class="form-control" id="codigo-{{ $ocspsa->id }}" name="codigo_interno" value="{{ $ocspsa->codigo_interno }}">
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <div class="custom-control custom-switch">
                                                    <input type="checkbox" class="custom-control-input" id="status-edit-{{ $ocspsa->id }}" name="ativo" {{ $ocspsa->ativo ? 'checked' : '' }}>
                                                    <label class="custom-control-label" for="status-edit-{{ $ocspsa->id }}">{{ $ocspsa->ativo ? 'Ativa' : 'Inativa' }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-info">Salvar alterações</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de Exclusão -->
                        <div class="modal fade" id="modal-excluir-ocspsa-{{ $ocspsa->id }}" tabindex="-1" role="dialog" aria-labelledby="modalExcluirOcsPsa" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('configuracoes.ocspsa.destroy', $ocspsa->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="modal-header bg-danger">
                                            <h5 class="modal-title">Confirmar Exclusão</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Tem certeza que deseja excluir a OCS/PSA "{{ $ocspsa->nome }}"?</p>
                                            <p class="text-danger"><strong>Atenção:</strong> Esta ação não poderá ser desfeita.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Nenhuma OCS/PSA cadastrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para criar nova OCS/PSA -->
    <div class="modal fade" id="modal-criar-ocspsa" tabindex="-1" role="dialog" aria-labelledby="modalCriarOcsPsa" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('configuracoes.ocspsa.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Nova OCS/PSA</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nome-novo">Nome</label>
                            <input type="text" class="form-control" id="nome-novo" name="nome" placeholder="Nome da OCS/PSA" required>
                        </div>
                        <div class="form-group">
                            <label for="codigo-novo">Código Interno</label>
                            <input type="text" class="form-control" id="codigo-novo" name="codigo_interno" placeholder="Ex: OCS-001" required>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="status-novo" name="ativo" checked>
                                <label class="custom-control-label" for="status-novo">Ativa</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
@parent
<script>
    $(function() {
        // Configurar CSRF token para requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Manipular os switches de status
        $('.status-switch').change(function() {
            const id = $(this).data('id');
            const isActive = $(this).prop('checked') ? 1 : 0;
            const statusLabel = $(this).next('label');
            
            $.ajax({
                url: "{{ url('configuracoes/ocspsa') }}/" + id + "/toggle-status",
                type: 'POST',
                data: { ativo: isActive },
                success: function(response) {
                    // Atualizar a label do switch
                    statusLabel.text(isActive ? 'Ativa' : 'Inativa');
                    
                    // Mostrar toast que desaparece automaticamente
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    
                    Toast.fire({
                        icon: 'success',
                        title: 'Status da OCS/PSA atualizado com sucesso!'
                    });
                },
                error: function(xhr) {
                    console.error('Erro ao atualizar status:', xhr);
                    
                    // Reverter o switch ao estado anterior
                    $('.status-switch[data-id="'+id+'"]').prop('checked', !isActive);
                    statusLabel.text(!isActive ? 'Ativa' : 'Inativa');
                    
                    // Mostrar toast de erro
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Erro ao atualizar status da OCS/PSA'
                    });
                }
            });
        });
    });
</script>
@endsection