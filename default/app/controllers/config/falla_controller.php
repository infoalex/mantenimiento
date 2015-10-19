<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las Fallaes de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/falla');

class FallaController extends BackendController {
    
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
    public function buscar($field='Falla', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $Falla = new Falla();
        $Fallaes = $Falla->getAjaxFallaes($field, $value, $order, $page);
        if(empty($Fallaes->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->Fallaes = $Fallaes;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de Fallaes del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.Falla.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $falla = new Falla();        
        $this->fallas = $falla->getListadoFalla($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Fallaes';
    }
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('falla')){
            if(Falla::setFalla('create', Input::post('falla'))){
                DwMessage::valid('El Falla se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }  
        } 
        $this->page_title = 'Agregar Falla';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_Falla', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Falla = new Falla();
        if(!$Falla->getInformacionFalla($id)) {            
            DwMessage::get('id_no_found')
;            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('Falla') && DwSecurity::isValidKey(Input::post('Falla_id_key'), 'form_key')) {
            if(Falla::setFalla('update', Input::post('Falla'), array('id'=>$id, 'empresa_id'=>$Falla->empresa_id))){
                DwMessage::valid('La Falla se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        $this->Falla = $Falla;
        $this->page_title = 'Actualizar Falla';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_Falla', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Falla = new Falla();
        if(!$Falla->getInformacionFalla($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Falla::setFalla('delete', array('id'=>$Falla->id))) {
                DwMessage::valid('La Falla se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta Falla no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
    
}

