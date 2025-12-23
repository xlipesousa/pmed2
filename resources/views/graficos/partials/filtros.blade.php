<div class="card card-outline card-primary collapsed-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter mr-1"></i> Filtros
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body" style="display: none;">
        <form id="form-filtros">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="filtro-periodo">Período:</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" id="filtro-periodo" name="periodo">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="filtro-ocspsa">OCS/PSA:</label>
                        <select class="form-control select2" id="filtro-ocspsa" name="ocs_psa_id" data-placeholder="Selecione OCS/PSA...">
                            <option></option><!-- Opção vazia para placeholder -->
                            <option value="todos">Todos</option>
                            @foreach($ocsPsas as $ocsPsa)
                                <option value="{{ $ocsPsa->id }}">{{ $ocsPsa->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filtro-tipo">Tipo de Pacote:</label>
                        <select class="form-control select2" id="filtro-tipo" name="tipo_id" data-placeholder="Selecione tipo...">
                            <option></option>
                            <option value="todos">Todos</option>
                            @foreach($tiposPacote as $tipoPacote)
                                <option value="{{ $tipoPacote->id }}">{{ $tipoPacote->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filtro-tipo-conta">Tipo de Conta:</label>
                        <select class="form-control select2" id="filtro-tipo-conta" name="tipo_conta_id" data-placeholder="Selecione tipo de conta...">
                            <option></option>
                            <option value="todos">Todos</option>
                            @foreach($tiposContas as $tipoConta)
                                <option value="{{ $tipoConta->id }}">{{ $tipoConta->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filtro-estado-glosa">Estado da Glosa:</label>
                        <select class="form-control select2" id="filtro-estado-glosa" name="estado_glosa" data-placeholder="Selecione estado...">
                            <option></option>
                            <option value="todos">Todos</option>
                            @foreach($estadosGlosa as $value => $label)
                                @if($value != 'todos')
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row mt-2">
                <div class="col-md-12 text-right">
                    <span class="badge badge-primary p-2 mr-2" id="filtros-ativos">Últimos 30 dias</span>
                    <button type="button" class="btn btn-default" id="btn-limpar-filtros" name="btn-limpar-filtros">
                        <i class="fas fa-eraser mr-1"></i> Limpar Filtros
                    </button>
                    <button type="button" class="btn btn-primary" id="btn-aplicar-filtros" name="btn-aplicar-filtros">
                        <i class="fas fa-search mr-1"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>