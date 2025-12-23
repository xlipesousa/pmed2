@extends('adminlte::page')

@section('title', 'Gerenciar Pesquisas Salvas')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-bookmark"></i> Gerenciar Pesquisas Salvas</h1>
        <a href="{{ route('pesquisa.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Voltar para Pesquisa
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($pesquisas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Data de Criação</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pesquisas as $pesquisa)
                                <tr>
                                    <td>{{ $pesquisa->nome }}</td>
                                    <td>{{ $pesquisa->descricao }}</td>
                                    <td>{{ Carbon\Carbon::parse($pesquisa->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('pesquisa.carregar', $pesquisa->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-search"></i> Carregar
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger excluir-pesquisa" data-id="{{ $pesquisa->id }}">
                                                <i class="fas fa-trash"></i> Excluir
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Você ainda não tem pesquisas salvas.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(function() {
        $('.excluir-pesquisa').on('click', function() {
            var id = $(this).data('id');
            var tr = $(this).closest('tr');
            
            if (confirm('Tem certeza que deseja excluir esta pesquisa salva?')) {
                $.ajax({
                    url: "{{ url('pesquisa/excluir') }}/" + id,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function() {
                        tr.fadeOut(400, function() {
                            $(this).remove();
                            
                            // Se não houver mais pesquisas, mostrar mensagem
                            if ($('table tbody tr').length === 0) {
                                $('.table-responsive').replaceWith(
                                    '<div class="alert alert-info">' +
                                    '<i class="fas fa-info-circle"></i> Você ainda não tem pesquisas salvas.' +
                                    '</div>'
                                );
                            }
                        });
                        
                        toastr.success('Pesquisa excluída com sucesso');
                    },
                    error: function() {
                        toastr.error('Erro ao excluir a pesquisa');
                    }
                });
            }
        });
    });
</script>
@stop