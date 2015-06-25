<?php
/**
 * Dailyscript - Web | App | Media
 *
 * Descripcion: Controlador que se encarga de la gestión de las proveedores de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/proveedor');

class ProveedorController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Configuraciones';
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
    public function buscar($field='sucursal', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $proveedor = new Proveedor();
        $proveedores = $proveedor->getAjaxProveedores($field, $value, $order, $page);
        if(empty($proveedores->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->proveedores = $proveedores;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de proveedores del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.nombre.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $proveedor = new Proveedor();        
        $this->proveedores = $proveedor->getListadoProveedor($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Proveedores';
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
    //    $empresa = Session::get('empresa', 'config');
        if(Input::hasPost('proveedor')) {
            if(Proveedor::setProveedor('create', Input::post('proveedor'))) {
                DwMessage::valid('El proveedor se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }            
        } 
        $this->page_title = 'Agregar Proveedor';
    }
     /**
     * Método para obtener proveedores
     */
    
        //accion que busca en las proveedores y devuelve el json con los datos
    public function autocomplete() {
        View::template(NULL);
        View::select(NULL);
        if (Input::isAjax()) { //solo devolvemos los estados si se accede desde ajax 
            $busqueda = Input::post('busqueda');
            $proveedores = Load::model('config/proveedor')->obtener_proveedores($busqueda);
            die(json_encode($proveedores)); // solo devolvemos los datos, sin template ni vista
            //json_encode nos devolverá el array en formato json ["aragua","carabobo","..."]
        }
    }        
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_proveedor', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $proveedor = new Proveedor();
        if(!$proveedor->getInformacionProveedor($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('proveedor') && DwSecurity::isValidKey(Input::post('proveedor_id_key'), 'form_key')) {
            if(Proveedor::setProveedor('update', Input::post('proveedor'))){
                DwMessage::valid('La proveedor se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        //$this->ciudades = Load::model('params/ciudad')->getCiudadesToJson();
        $this->proveedor = $proveedor;
        $this->page_title = 'Actualizar proveedor';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_proveedor', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $proveedor = new Proveedor();
        if(!$proveedor->getInformacionProveedor($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Proveedor::setProveedor('delete', array('id'=>$proveedor->id))) {
                DwMessage::valid('La proveedor se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Este proveedor no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
}
