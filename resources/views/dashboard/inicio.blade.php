@extends('layout.frontend')
@section('navs')
<a href="admin" >
        <i class="fa fa-gears"> Iniciar Sesión</i>
    </a>
@stop    
@section('content')
    {!! trans('messages.welcome', ['name' => config('custom.htmlname')]) !!}
@stop