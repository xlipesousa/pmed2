@extends('adminlte::auth.login')

@section('auth_footer')
	<small class="text-muted d-block text-center">Versão {{ config('version.current') }}</small>
@stop