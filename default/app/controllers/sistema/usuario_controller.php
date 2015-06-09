<?php
/**
 * SGIMPC (Sistema de Gestion de Incidencias de Mantenimientos Preventivos y Correctivos )
 *
 * Descripcion: Controlador del Modelo de Usuario
 *
 * @category
 * @package     Models
 * @subpackage
 * @author      Grupo SGIMPC UPTP 
 * @copyright   Copyright (c) 2015 UPTP / E.M.S. Arroz del Alba S.A.
 */

Load::models('config/sucursal', 'sistema/usuario_clave');

class UsuarioController extends BackendController {
     
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Gestión de usuarios';
    }
    
    /**
     * Método principal
     */
    public function index() {
        DwRedirect::toAction('listar');
    }
    
    /**
     * Método para buscar
     */
    public function buscar($field='nombre1', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        
        $usuario = new Usuario();            
        $usuarios = $usuario->getAjaxUsuario($field, $value, $order, $page);        
        if(empty($usuarios->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->usuarios = $usuarios;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de usuarios del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.id.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $usuario = new Usuario();
        $this->usuarios = $usuario->getListadoUsuario('todos', $order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de usuarios del sistema';
    }
    
     /**
     * Método para agregar
     */
    public function agregar() {
         if(Input::hasPost('usuario') && Input::hasPost('usuario_clave')) {
            ActiveRecord::beginTrans();
            //Guardo usuario
            $usuario = Usuario::setUsuario('create', Input::post('usuario'));
            if($usuario) {
                if(UsuarioClave::setClave('create', Input::post('usuario_clave'), array('usuario_id'=>$usuario->id))) {
                    ActiveRecord::commitTrans();
                    DwMessage::valid('El Usuario se ha creado correctamente.');
                    return DwRedirect::toAction('listar');
                }
            } else {
                ActiveRecord::rollbackTrans();
            }
             
        }
        $this->page_title = 'Agregar Usuario';
        
    }
    /**
     * Método para editar
     */
    public function editar($key) {       
        
        if(!$id = DwSecurity::isValidKey($key, 'upd_usuario', 'int')) {
           return DwRedirect::toAction('listar');
        }
        
        $usuario = new Usuario();
        if(!$usuario->getInformacionUsuario($id)) {
            DwMessage::get('id_no_found');    
            return DwRedirect::toAction('listar');
        }                
        
        if(Input::hasPost('usuario')) {
            if(DwSecurity::isValidKey(Input::post('usuario_id_key'), 'form_key')) {
                ActiveRecord::beginTrans();
                    if(Usuario::setUsuario('update', Input::post('usuario'), array('repassword'=>Input::post('repassword'), 'id'=>$usuario->id, 'login'=>$usuario->login))) {
                        ActiveRecord::commitTrans();
                        DwMessage::valid('El usuario se ha actualizado correctamente.');
                        return DwRedirect::toAction('listar');
                } else {
                    ActiveRecord::rollbackTrans();
                } 
            }
        }        
        $this->temas = DwUtils::getFolders(dirname(APP_PATH).'/public/css/backend/themes/');
        $this->usuario = $usuario;
        $this->page_title = 'Actualizar usuario';
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = DwSecurity::isValidKey($key, $tipo.'_usuario', 'int')) {
            return DwRedirect::toAction('listar');
        } 
        
        $usuario = new Usuario();
        if(!$usuario->getInformacionUsuario($id)) {
            DwMessage::get('id_no_found');    
            return DwRedirect::toAction('listar');
        }
        if($tipo == 'reactivar' && $usuario->estatus == 1) {
            DwMessage::info('El usuario ya se encuentra activo.');
            return DwRedirect::toAction('listar');
        }
        else if($tipo == 'reactivar' && $usuario->estatus == 2) {
            $usr = $usuario->getInformacionUsuario($id);
            $usr->estatus=1;
            $usr->save();
            return DwRedirect::toAction('listar');
        }
        else if($tipo == 'bloquear' && $usuario->estatus == 2) {
            DwMessage::info('El usuario ya se encuentra bloqueado.');
            return DwRedirect::toAction('listar');
        }
        else if($tipo == 'bloquear' && $usuario->estatus == 1) {
            $usr = $usuario->getInformacionUsuario($id);
            $usr->estatus=2;
            $usr->save();
            return DwRedirect::toAction('listar');
       }
    }

    public function bloquear($key){
        if(!$id = DwSecurity::isValidKey($key, 'upd_usuario', 'int')) {
            return DwRedirect::toAction('aprobacion');
        }
        //Mejorar esta parte  implementando algodon de seguridad
    
        $solicitud_servicio = new SolicitudServicio();
        $sol = $solicitud_servicio->getInformacionSolicitudServicio($id);
        $sol->estado_solicitud="A";
        $sol->save();
        $cod = $sol->codigo_solicitud;
        $nro = $sol->celular;
        $nombre = $sol->nombre;
        $apellido = $sol->apellido;
        $contenido= "Sr. ".$nombre." ".$apellido." Su solicitud ha sido aprobada Aprobada con el codigo: ".$cod;
        $destinatario=$nro;
        system( '/usr/bin/gammu -c /etc/gammu-smsdrc --sendsms EMS ' . escapeshellarg( $destinatario ) . ' -text ' . escapeshellarg( $contenido ) ); 
        return DwRedirect::toAction('reporte_aprobacion/'.$id);
    }
   
    /**
     * Método para ver
     */
    public function ver($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'shw_usuario', 'int')) {
            return DwRedirect::toAction('listar');
        }
        
        $usuario = new Usuario();
        if(!$usuario->getInformacionUsuario($id)) {
            DwMessage::get('id_no_found');    
            return DwRedirect::toAction('listar');
        }                
        
        $estado = new EstadoUsuario();
        $this->estados = $estado->getListadoEstadoUsuario($usuario->id);
        $acceso = new Acceso();
        $this->accesos = $acceso->getListadoAcceso($usuario->id, 'todos', 'order.fecha.desc');
        $this->usuario = $usuario;
        $this->page_title = 'Información del usuario';
        
    }
    
    /**
     * Método para subir imágenes
     */
    public function upload() {     
        $upload = new DwUpload('fotografia', 'img/upload/titulares/');
        $upload->setAllowedTypes('png|jpg|gif|jpeg');
        $upload->setEncryptName(TRUE);
        $upload->setSize(170, 200, TRUE);
        if(!$data = $upload->save()) { //retorna un array('path'=>'ruta', 'name'=>'nombre.ext');
            $data = array('error'=>$upload->getError());
        }
        sleep(1);//Por la velocidad del script no permite que se actualize el archivo
        View::json($data);
    }
    
}

