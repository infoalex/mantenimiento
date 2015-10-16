<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las Mano_obras de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/mano_obra');

class Mano_obraController extends BackendController {
    
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
    public function buscar($field='Mano_obra', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $Mano_obra = new Mano_obra();
        $Mano_obras = $Mano_obra->getAjaxMano_obras($field, $value, $order, $page);
        if(empty($Mano_obras->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->Mano_obras = $Mano_obras;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de Mano_obras del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.Mano_obra.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $mano_obra = new Mano_obra();        
        $this->mano_obras = $mano_obra->getListadoMano_obra($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Mano_obras';
    }
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('mano_obra')){
            if(Mano_obra::setMano_obra('create', Input::post('mano_obra'))){
                DwMessage::valid('El Mano_obra se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }  
        } 
        $this->page_title = 'Agregar Mano_obra';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_Mano_obra', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Mano_obra = new Mano_obra();
        if(!$Mano_obra->getInformacionMano_obra($id)) {            
            DwMessage::get('id_no_found')
;            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('Mano_obra') && DwSecurity::isValidKey(Input::post('Mano_obra_id_key'), 'form_key')) {
            if(Mano_obra::setMano_obra('update', Input::post('Mano_obra'), array('id'=>$id, 'empresa_id'=>$Mano_obra->empresa_id))){
                DwMessage::valid('La Mano_obra se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        $this->Mano_obra = $Mano_obra;
        $this->page_title = 'Actualizar Mano_obra';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_Mano_obra', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Mano_obra = new Mano_obra();
        if(!$Mano_obra->getInformacionMano_obra($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Mano_obra::setMano_obra('delete', array('id'=>$Mano_obra->id))) {
                DwMessage::valid('La Mano_obra se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta Mano_obra no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
    
}

