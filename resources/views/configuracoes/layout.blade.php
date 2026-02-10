@extends('adminlte::page')

@section('title', 'Configurações')

@section('content_header')
    <h1>Configurações do Sistema</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Menu de Configurações</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a href="{{ route('configuracoes.sistema') }}" class="nav-link {{ request()->routeIs('configuracoes.sistema') ? 'active' : '' }}">
                                <i class="fas fa-cog"></i> Sistema
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('configuracoes.ocspsa') }}" class="nav-link {{ request()->routeIs('configuracoes.ocspsa') ? 'active' : '' }}">
                                <i class="fas fa-hospital"></i> OCS/PSA
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('configuracoes.upgrade') }}" class="nav-link {{ request()->routeIs('configuracoes.upgrade') ? 'active' : '' }}">
                                <i class="fas fa-arrow-up"></i> Upgrade
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            @yield('configuracoes_content')
        </div>
    </div>
@stop

@section('css')
    @yield('configuracoes_css')
@stop

@section('js')
    @yield('configuracoes_js')
@stop