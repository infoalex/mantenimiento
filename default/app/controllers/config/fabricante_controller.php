<?php
/**
 * Dailyscript - Web | App | Media
 *
 * Descripcion: Controlador que se encarga de la gestión de las fabricantes de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/fabricante');

class FabricanteController extends BackendController {
    
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
        $fabricante = new Fabricante();
        $fabricantes = $fabricante->getAjaxFabricantes($field, $value, $order, $page);
        if(empty($fabricantes->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->fabricantes = $fabricantes;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de fabricantes del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.nombre.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $fabricante = new Fabricante();        
        $this->fabricantes = $fabricante->getListadoFabricante($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Fabricantes';
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
    //    $empresa = Session::get('empresa', 'config');
        if(Input::hasPost('fabricante')) {
            if(Fabricante::setFabricante('create', Input::post('fabricante'))) {
                DwMessage::valid('El fabricante se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }            
        } 
        $this->page_title = 'Agregar Fabricante';
    }
     /**
     * Método para obtener fabricantes
     */
    
        //accion que busca en las fabricantes y devuelve el json con los datos
    public function autocomplete() {
        View::template(NULL);
        View::select(NULL);
        if (Input::isAjax()) { //solo devolvemos los estados si se accede desde ajax 
            $busqueda = Input::post('busqueda');
            $fabricantes = Load::model('config/fabricante')->obtener_fabricantes($busqueda);
            die(json_encode($fabricantes)); // solo devolvemos los datos, sin template ni vista
            //json_encode nos devolverá el array en formato json ["aragua","carabobo","..."]
        }
    }        
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_fabricante', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $fabricante = new Fabricante();
        if(!$fabricante->getInformacionFabricante($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('fabricante') && DwSecurity::isValidKey(Input::post('fabricante_id_key'), 'form_key')) {
            if(Fabricante::setFabricante('update', Input::post('fabricante'))){
                DwMessage::valid('La fabricante se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        //$this->ciudades = Load::model('params/ciudad')->getCiudadesToJson();
        $this->fabricante = $fabricante;
        $this->page_title = 'Actualizar fabricante';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_fabricante', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $fabricante = new Fabricante();
        if(!$fabricante->getInformacionFabricante($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Fabricante::setFabricante('delete', array('id'=>$fabricante->id))) {
                DwMessage::valid('La fabricante se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Este fabricante no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
}
