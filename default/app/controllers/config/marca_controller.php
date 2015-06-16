<?php
/**
 * Dailyscript - Web | App | Media
 *
 * Descripcion: Controlador que se encarga de la gestión de las marcas de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/marca');

class MarcaController extends BackendController {
    
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
        $marca = new Marca();
        $marcas = $marca->getAjaxMarcas($field, $value, $order, $page);
        if(empty($marcas->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->marcas = $marcas;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de marcas del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.nombre.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $marca = new Marca();        
        $this->marcas = $marca->getListadoMarca($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Marcas';
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
    //    $empresa = Session::get('empresa', 'config');
        if(Input::hasPost('marca')) {
            if(Marca::setMarca('create', Input::post('marca'))) {
                DwMessage::valid('La marca se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }            
        } 
        $this->page_title = 'Agregar Marca';
    }
     /**
     * Método para obtener marcas
     */
    
        //accion que busca en las marcas y devuelve el json con los datos
    public function autocomplete() {
        View::template(NULL);
        View::select(NULL);
        if (Input::isAjax()) { //solo devolvemos los estados si se accede desde ajax 
            $busqueda = Input::post('busqueda');
            $marcas = Load::model('config/marca')->obtener_marcas($busqueda);
            die(json_encode($marcas)); // solo devolvemos los datos, sin template ni vista
            //json_encode nos devolverá el array en formato json ["aragua","carabobo","..."]
        }
    }        
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_marca', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $marca = new Marca();
        if(!$marca->getInformacionMarca($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('marca') && DwSecurity::isValidKey(Input::post('marca_id_key'), 'form_key')) {
            if(Marca::setMarca('update', Input::post('marca'))){
                DwMessage::valid('La marca se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        //$this->ciudades = Load::model('params/ciudad')->getCiudadesToJson();
        $this->marca = $marca;
        $this->page_title = 'Actualizar marca';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_marca', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $marca = new Marca();
        if(!$marca->getInformacionMarca($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Marca::setMarca('delete', array('id'=>$marca->id))) {
                DwMessage::valid('La marca se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta marca no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
}
