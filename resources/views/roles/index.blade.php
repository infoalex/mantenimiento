@extends('layout.partials.table')

@section('filters')
	<div class="col-md-offset-8 col-md-4">
		@include('layout.partials.filters.search')
	</div>
@stop

@section('table')
	<tr>
		<th class="text-center" width="1"><input type="checkbox" name="chb-all" id="chb-all" /></th>
		<th class="text-center" width="1">{!! sort_by('admin.roles.index', 'id', trans('messages.id')) !!}</th>
		<th>{!! sort_by('admin.roles.index', 'name', trans('messages.name')) !!}</th>
		<th class="text-center" width="100">{!! sort_by('admin.roles.index', 'created_at', trans('messages.created_at')) !!}</th>		
		<th class="text-center" width="100">#</th>
	</tr>
	@foreach ($results as $role)
	<tr>
		<td class="text-center"><input type="checkbox" name="ids[]" value="{{ $role->id }}" class="chbids" /></td>
		<td class="text-center">{{ $role->id }}</td>
		<td>{{ $role->name }}</td>
		<td class="text-center">{{ $role->created }}</td>
		<td class="text-center">
			<a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-xs btn-success"><i class="fa fa-eye"></i></a>	
			<a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></a>
		</td>
	</tr>
	@endforeach
    	<style>
            .user{
                margin-top: 60px;
            }
        </style>
        <script>
            $(document).on('ready', function(){
                
                rol_id = null;
               $('#select-permisos').multiSelect({
                    selectableHeader: "<div class='custom-header'>Permisos no asignados</div>",
                    selectionHeader: "<div class='custom-header'>Permisos asignados</div>",
                   afterSelect:function(value){//enviamos al servidor el id del permiso seleccionado
                        $.ajax({
                        url : '{{ URL::to("/permisos/asignar") }}',
                        type : 'GET',
                        dataType: 'json',
                        data : {permission_id: value[0], role_id: rol_id}
                    }).done(function(data){
                        console.log(data);
                    });
                   },
                   afterDeselect:function(value){//enviamos al servidor el id del permiso seleccionado
                        $.ajax({
                        url : '{{ URL::to("/permisos/desasignar") }}',
                        type : 'GET',
                        dataType: 'json',
                        data : {permission_id: value[0], role_id: rol_id}
                    }).done(function(data){
                        console.log(data);
                    });
                   }
               });
                
                
                $('.get-permisos').on('click', function(){
                    rol_id = $(this).attr('rol_id');
                    $.ajax({
                        url : '{{ URL::to("/permisos") }}',
                        type : 'GET',
                        dataType: 'json',
                        data : {id: rol_id}
                    }).done(function(data){
                        
                        $.each(data.permisosAsignados ,function(index, value){
                            $('#select-permisos option[value="'+value.id+'"]').attr('selected', true);
                        });
                        $('#select-permisos').multiSelect('refresh');
                    });
                });
            });
        </script>
@stop
@section('content')
<div class="container">
    <div class="user">
        @if(Entrust::can('crear_roles'))
        {{ Form::open(array('method' 
                            => 'get', 'route' => array('roles.create'))) }}
                            {{ Form::submit('Crear', array('class'
                        => 'btn btn-success')) }}
                        {{ Form::close() }}
         @endif
        <table class="table table-bordered table-hover">
            <thead>
                <tr><th>Nombre</th><th>Permisos</th></tr>
            </thead>
            @if(isset($roles))
                <tbody>
                @foreach($roles as $rol)
                    <tr><td>{{ $rol->name }}</td>
                        <td><a data-toggle="modal" rol_id="{{ $rol->id }}" data-target="#permisos" class="btn btn-primary get-permisos">Permisos</a></td>
                        @if(Entrust::can('editar_roles'))
                        <td>{{ Form::open(array('method' 
                            => 'GET', 'route' => array('roles.edit', $rol->id))) }}
                            {{ Form::submit('Editar', array('class'
                        => 'btn btn-info')) }}
                        {{ Form::close() }}</td>
                        @endif
                         @if(Entrust::can('eliminar_roles'))
                        <td>
                            {{ Form::open(array('method' 
                            => 'DELETE', 'route' => array('roles.destroy', $rol->id))) }}
                            {{ Form::submit('Eliminar', array('class'
                        => 'btn btn-danger')) }}
                        {{ Form::close() }}
                    </td>
                        @endif
                    </tr>
                @endforeach
                    </tbody>
            @endif
        </table>
        
        <div class="modal" id="permisos">
	<div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4 class="modal-title">Gestionar permisos</h4>
        </div>
        <div class="modal-body">
          <select id="select-permisos" multiple="multiple">
                @if(isset($permisos))
                    @foreach($permisos as $permiso)
                        <option value="{{ $permiso->id }}">{{ $permiso->display_name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="modal-footer">
          <a href="#" data-dismiss="modal" class="btn">Cerrar</a>
        </div>
      </div>
    </div>
</div>
        
    </div>
</div>
@stop