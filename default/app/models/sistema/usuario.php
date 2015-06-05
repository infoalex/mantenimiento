<?php
/**
 * SGIMPC (Sistema de Gestion de Incidencias de Mantenimientos Preventivos y Correctivos )
 *
 * Descripcion: Modelo para el manejo de Usuario
 *
 * @category
 * @package     Models
 * @subpackage
 * @author      Grupo SGIMPC UPTP 
 * @copyright   Copyright (c) 2015 UPTP / E.M.S. Arroz del Alba S.A.
 */




Load::models('sistema/estado_usuario', 'sistema/perfil','sistema/usuario_clave','sistema/usuario_pregunta', 'sistema/recurso', 'sistema/recurso_perfil', 'sistema/acceso');

class Usuario extends ActiveRecord {
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        //$this->belongs_to('titular');
        $this->belongs_to('perfil');
        $this->belongs_to('usuario_clave');
        $this->has_many('usuario_pregunta');
        $this->has_many('estado_usuario');        
    }
    
    /**
     * Método que devuelve el inner join con el estado_usuario
     * @return string
     */
    public static function getInnerEstado() {
        return "INNER JOIN (SELECT usuario_id, CASE estado_usuario WHEN ".EstadoUsuario::COD_ACTIVO." THEN '".EstadoUsuario::ACTIVO."' WHEN ".EstadoUsuario::COD_BLOQUEADO." THEN '".EstadoUsuario::BLOQUEADO."' ELSE 'INDEFINIDO' END AS estado_usuario, descripcion FROM (SELECT * FROM estado_usuario ORDER BY estado_usuario.id DESC ) AS estado_usuario GROUP BY estado_usuario.usuario_id, estado_usuario.estado_usuario, descripcion) AS estado_usuario ON estado_usuario.usuario_id = usuario.id ";        
    }
    /**
     * Método para validar los intentos de la clave
     */
    public static function usuario_intentos($idusuario) {    
        $usuario = new Usuario();
        $intento = $usuario->find("columns: id,intentos","conditions: id='".$idusuario."'","order: id DESC","limit: 1 ");
        $intento1 = $intento[0]->intentos;
        if($intento1 == 3){
                    return 1;
        }
                    return 2;
    }    
    /**
     * Método para abrir y cerrar sesión
     * @param type $opt
     * @return boolean
     */
    public static function setSession($opt='open', $user=NULL, $pass=NULL, $mode=NULL) {  
        if($opt=='close') {
            $usuario = Session::get('id');
            if(DwAuth::logout()) {   
                //Registro la salida
                Acceso::setAcceso(Acceso::SALIDA, $usuario);
                return true;
            }                
            DwMessage::error(DwAuth::getError()); 
        } else if($opt=='open') {            
            if(DwAuth::isLogged()) {
                return true;
            } else {                                
                if(DwForm::isValidToken()) {
                    if(DwAuth::login(array('login'=>$user), array('password'=>sha1($pass)), $mode)) {
                        $usuario = self::getUsuarioLogueado();  
                        $usuval = UsuarioClave::clave_valida($usuario->id);
                        $usuintentos = self::usuario_intentos($usuario->id);                      
                        if( ($usuario->id!=2) &&  ($usuario->estado_usuario != EstadoUsuario::ACTIVO) ) { 
                            DwAuth::logout();
                            DwMessage::error('Lo sentimos pero tu cuenta se encuentra inactiva. <br />Si esta información es incorrecta contacta al administrador del sistema.');
                            return false;
                        }
                        if($usuintentos== 2 ) { 
                           // DwAuth::logout();
                           //Session::set('perfil_id', '8');
                           //Session::set('tema', 'default');
                        //Session::set('nombre1', $usuario->nombre1);
                        //Session::set('apellido1', $usuario->apellido1);                           
                            //return DwRedirect::to('sistema/usuario_clave/cambiar_clave');
                        //DwMessage::error('usuintentos. '.$usuintentos.'<br />Si esta información es incorrecta contacta al administrador del sistema.');
                        }
                        if($usuval!=1 ) { 
                           // DwAuth::logout();
                           Session::set('perfil_id', '8');
                           Session::set('tema', 'default');
                        Session::set('nombres', $usuario->nombres);
                        Session::set('apellidos', $usuario->apellidos);                           
                            return DwRedirect::to('sistema/usuario_clave/cambiar_clave');
                        }                         
                        Session::set('nombres', $usuario->nombres);
                        Session::set('apellidos', $usuario->apellidos);                        
                        Session::set('ip', DwUtils::getIp());
                        Session::set('perfil', $usuario->perfil);
                        Session::set('tema', $usuario->tema);
                        Session::set('app_ajax', $usuario->app_ajax);
                        
                        //Registro el acceso
                        Acceso::setAcceso(Acceso::ENTRADA, $usuario->id);
                        
                        DwMessage::info("¡ Bienvenido <strong>$usuario->nombres $usuario->apellidos</strong> !.");     
                        return true;
                    } else {
                        DwMessage::error(DwAuth::getError());
                    }
                } else {
                    DwMessage::info('La llave de acceso ha caducado. <br />Por favor '.Html::link('sistema/login/entrar/', 'recarga la página <b>aquí</b>')); 
                }
            }                      
        } else {
            DwMessage::error('No se ha podido establecer la sesión actual.');            
        }
        return false;  
    }
            
    /**
     * Método para obtener la información de un usuario logueado
     * @return object Usuario
     */
    public static function getUsuarioLogueado() {
        $columnas = 'usuario.*, perfil.perfil, nombres, apellidos, estado_usuario.estado_usuario';
        $join= "INNER JOIN perfil ON perfil.id = usuario.perfil_id ";
        $join.= self::getInnerEstado();
        $condicion = "usuario.id = '".Session::get('id')."'";
        $obj = new Usuario();
        return $obj->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    }  
    
    
    /**
     * Método para listar los usuarios por perfil
     */
    public function getUsuarioPorPerfil($perfil, $order='order.nombres.asc', $page=0) {
        $perfil = Filter::get($perfil, 'int');
        if(empty($perfil)) {
            return NULL;
        }
        $columns = 'usuario.*, nombres, apellidos, perfil.perfil, sucursal.sucursal';
        $join.= 'INNER JOIN perfil ON perfil.id = usuario.perfil_id ';
        $join.= 'LEFT JOIN sucursal ON sucursal.id = usuario.sucursal_id ';
        $conditions = "perfil.id = $perfil";
        
        $order = $this->get_order($order, 'nombres', array(                        
            'login' => array(
                'ASC'=>'usuario.login ASC, usuario.nombres ASC, usuario.apellidos DESC', 
                'DESC'=>'usuario.login DESC, usuario.nombres DESC, usuario.apellidos DESC'
            ),
            'nombres' => array(
                'ASC'=>'usuario.nombres ASC, usuario.apellidos DESC', 
                'DESC'=>'titular.nombres DESC, usuario.apellidos DESC'
            ),
            'apellidos' => array(
                'ASC'=>'usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'usuario.apellidos DESC, usuario.nombres DESC'
            ),
            'email' => array(
                'ASC'=>'usuario.email ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'usuario.email DESC, titular.apellidos DESC, usuario.nombres DESC'
            ),
            'sucursal' => array(
                'ASC'=>'sucursal.sucursal ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'sucursal.sucursal DESC, usuario.apellidos DESC, usuario.nombres DESC'
            ),
            'estado_usuario' => array(
                'ASC'=>'estado_usuario.estado_usuario ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'estado_usuario.estado_usuario DESC, usuario.apellidos DESC, usuario.nombres DESC'
            )
        ));
        
        if($page) {
            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");
        } 
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
    }
    
    /**
     * Método para buscar usuarios
     */
    public function getAjaxUsuario($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) <= 2 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'usuario.*, perfil.perfil, usuario.nombres, usuario.apellidos, estado_usuario.estado_usuario, estado_usuario.descripcion, sucursal.sucursal';
        $join = self::getInnerEstado();
        $join.= 'INNER JOIN perfil ON perfil.id = usuario.perfil_id ';
        $join.= 'LEFT JOIN sucursal ON sucursal.id = usuario.sucursal_id ';
        $conditions = "usuario.id > '2'";//Por el super usuario
        $order = $this->get_order($order, 'nombres', array(                        
            'login' => array(
                'ASC'=>'usuario.login ASC, usuario.nombres ASC, usuario.apellidos DESC', 
                'DESC'=>'usuario.login DESC, usuario.nombres DESC, usuario.apellidos DESC'
            ),
            'nombres' => array(
                'ASC'=>'usuario.nombres ASC, usuario.apellidos DESC', 
                'DESC'=>'titular.nombres DESC, usuario.apellidos DESC'
            ),
            'apellidos' => array(
                'ASC'=>'usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'usuario.apellidos DESC, usuario.nombres DESC'
            ),
            'email' => array(
                'ASC'=>'usuario.email ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'usuario.email DESC, titular.apellidos DESC, usuario.nombres DESC'
            ),
            'sucursal' => array(
                'ASC'=>'sucursal.sucursal ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'sucursal.sucursal DESC, usuario.apellidos DESC, usuario.nombres DESC'
            ),
            'estado_usuario' => array(
                'ASC'=>'estado_usuario.estado_usuario ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'estado_usuario.estado_usuario DESC, usuario.apellidos DESC, usuario.nombres DESC'
            )
        ));

        
        //Defino los campos habilitados para la búsqueda
        $fields = array('login', 'nombres', 'apellidos', 'email', 'perfil', 'sucursal', 'estado_usuario');
        if(!in_array($field, $fields)) {
            $field = 'nombres';
        }        
        if(! ($field=='sucursal' && $value=='todas') ) {
            $conditions.= " AND $field LIKE '%$value%'";
        }        
        if($page) {
            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }
    
    
    public function getListadoUsuario($estado, $order='', $page=0) {
        $columns = 'usuario.*, perfil.perfil, usuario.nombres, usuario.apellidos, estado_usuario.estado_usuario, estado_usuario.descripcion, sucursal.sucursal';
        $join = self::getInnerEstado();
        $join.= 'INNER JOIN perfil ON perfil.id = usuario.perfil_id ';
        $join.= 'LEFT JOIN sucursal ON sucursal.id = usuario.sucursal_id ';
        $conditions = "usuario.id > '2'";//Por el super usuario
                
        $order = $this->get_order($order, 'nombres', array(                        
            'login' => array(
                'ASC'=>'usuario.login ASC, usuario.nombres ASC, usuario.apellidos DESC', 
                'DESC'=>'usuario.login DESC, usuario.nombres DESC, usuario.apellidos DESC'
            ),
            'nombres' => array(
                'ASC'=>'usuario.nombres ASC, usuario.apellidos DESC', 
                'DESC'=>'titular.nombres DESC, usuario.apellidos DESC'
            ),
            'apellidos' => array(
                'ASC'=>'usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'usuario.apellidos DESC, usuario.nombres DESC'
            ),
            'email' => array(
                'ASC'=>'usuario.email ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'usuario.email DESC, titular.apellidos DESC, usuario.nombres DESC'
            ),
            'sucursal' => array(
                'ASC'=>'sucursal.sucursal ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'sucursal.sucursal DESC, usuario.apellidos DESC, usuario.nombres DESC'
            ),
            'estado_usuario' => array(
                'ASC'=>'estado_usuario.estado_usuario ASC, usuario.apellidos ASC, usuario.nombres ASC', 
                'DESC'=>'estado_usuario.estado_usuario DESC, usuario.apellidos DESC, usuario.nombres DESC'
            )
        ));
        
        if($estado == 'activos') {
            $conditions.= " AND estado_usuario.estado_usuario = '".EstadoUsuario::USR_ACTIVO."'";
        } else if($estado == 'bloqueados') {
            $conditions.= " AND estado_usuario.estado_usuario = '".EstadoUsuario::USR_BLOQUEADO."'";
        }          
        
        if($page) {
            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }
    
    /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $otherData: Data adicional para autocargar
     * 
     * @return object ActiveRecord
     */
    public static function setUsuario($method, $data, $optData=null) {
        $obj = new Usuario($data);   
        if($optData) {
            $obj->dump_result_self($optData);
        }

        if(!empty($obj->id)) { //Si va a actualizar
            $old = new Usuario();
            $old->find_first($obj->id);
                                   
        }
 
        $rs = $obj->$method();
        if($rs) {
            ($method == 'create') ? DwAudit::debug("Se ha registrado el usuario $obj->login en el sistema") : DwAudit::debug("Se ha modificado la información del usuario $obj->login");
        }
        return ($rs) ? $obj : FALSE;
    }
    
    /**
     * Método para verificar si existe un campo registrado
     */
    protected function _getRegisteredField($field, $value, $id=NULL) {                
        $conditions = "$field = '$value'";
        $conditions.= (!empty($id)) ? " AND id != $id" : '';
        return $this->count("conditions: $conditions");
    }
    
    /**
     * Callback que se ejecuta antes de guardar/modificar
     */
    protected function before_save() {
        //Coloco en mayusculas los datos que van a bd
        $this->nombres = strtoupper($this->nombres);
        $this->apellidos = strtoupper($this->apellidos);
        $this->login = strtoupper($this->login);
        $this->email = strtoupper($this->email);
                

        //Verifico la sucursal al crear el usuario        
        if(APP_OFFICE) {                                
            $this->sucursal_id = ($this->sucursal_id=='todas') ? NULL : Filter::get($this->sucursal_id, 'int');                
        } else {
            $this->sucursal_id = Sucursal::OFICINA_PRINCIPAL;
        }        
        if(Session::get('perfil_id') != Perfil::SUPER_USUARIO) { //Solo el super usuario puede hacer esto
            //Verifico las exclusiones de los nombres de usuarios del config.ini   
            $exclusion = DwConfig::read('config', array('custom'=>'login_exclusion') );        
            $exclusion = explode(',', $exclusion);
            if(!empty($exclusion)) {
                if(in_array($this->login, $exclusion)) {
                    DwMessage::error('El nombre de usuario indicado, no se encuentra disponible.');
                    return 'cancel';
                }
            }        
        }
        //Verifico si el login está disponible
        if($this->_getRegisteredField('login', $this->login, $this->id)) {
            DwMessage::error('El nombre de usuario no se encuentra disponible.');
            return 'cancel';
        }
        //Verifico si ya se encuentra registrado
        //if($this->_getRegisteredField('titular_id', $this->titular_id, $this->id)) {
        //    DwMessage::error('La titular registrada ya posee una cuenta de usuario.');
        //    return 'cancel';
        //} 
        //if($this->_getRegisteredField('titular_id', $this->titular_id, $this->cedula)) {
        //    DwMessage::error('La Cedula registrada ya posee una cuenta de usuario.');
        //    return 'cancel';
        //} 
        //Verifico si se encuentra el mail registrado
        if($this->_getRegisteredField('email', $this->email, $this->id)) {
            DwMessage::error('El correo electrónico ya se encuentra registrado.');
            return 'cancel';
        }
        $this->datagrid = Filter::get($this->datagrid, 'int');
        
    }
    
    /**
     * Callback que se ejecuta despues de insertar un usuario
     */
    protected function after_create() {        
        if(!EstadoUsuario::setEstadoUsuario('registrar', array('usuario_id'=>$this->id, 'descripcion'=>'Activado por registro inicial'))){
            DwMessage::error('Se ha producido un error interno al activar el usuario. Pofavor intenta nuevamente.');
            return 'cancel';
        }
    }
    
    /**
     * Método para obtener la información de un usuario
     * @return type
     */
    public function getInformacionUsuario($usuario) {
        $usuario = Filter::get($usuario, 'int');
        if(!$usuario) {
            return NULL;
        }
        $columnas = 'usuario.*, perfil.perfil, usuario.nombres, usuario.nombres, usuario.apellidos, usuario.apellidos, estado_usuario.estado_usuario, estado_usuario.descripcion, sucursal.sucursal';
        $join = self::getInnerEstado();
        $join.= 'INNER JOIN perfil ON perfil.id = usuario.perfil_id ';
        $join.= 'LEFT JOIN sucursal ON sucursal.id = usuario.sucursal_id ';
        $condicion = "usuario.id = $usuario";        
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
       
    
}
?>
