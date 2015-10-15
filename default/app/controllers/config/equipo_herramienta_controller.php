<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las Equipo_herramientas de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/equipo_herramienta');

class Equipo_herramientaController extends BackendController {
    
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
    public function buscar($field='Equipo_herramienta', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $Equipo_herramienta = new Equipo_herramienta();
        $Equipo_herramientas = $Equipo_herramienta->getAjaxEquipo_herramientas($field, $value, $order, $page);
        if(empty($Equipo_herramientas->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->Equipo_herramientas = $Equipo_herramientas;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de Equipo_herramientas del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.Equipo_herramienta.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $equipo_herramienta = new Equipo_herramienta();        
        $this->equipo_herramientas = $equipo_herramienta->getListadoEquipo_herramienta($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Equipo_herramientas';
    }
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('equipo_herramienta')){
            if(Equipo_herramienta::setEquipo_herramienta('create', Input::post('equipo_herramienta'))){
                DwMessage::valid('El Equipo_herramienta se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }  
        } 
        $this->page_title = 'Agregar Equipo_herramienta';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_Equipo_herramienta', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Equipo_herramienta = new Equipo_herramienta();
        if(!$Equipo_herramienta->getInformacionEquipo_herramienta($id)) {            
            DwMessage::get('id_no_found')
;            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('Equipo_herramienta') && DwSecurity::isValidKey(Input::post('Equipo_herramienta_id_key'), 'form_key')) {
            if(Equipo_herramienta::setEquipo_herramienta('update', Input::post('Equipo_herramienta'), array('id'=>$id, 'empresa_id'=>$Equipo_herramienta->empresa_id))){
                DwMessage::valid('La Equipo_herramienta se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        $this->Equipo_herramienta = $Equipo_herramienta;
        $this->page_title = 'Actualizar Equipo_herramienta';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_Equipo_herramienta', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Equipo_herramienta = new Equipo_herramienta();
        if(!$Equipo_herramienta->getInformacionEquipo_herramienta($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Equipo_herramienta::setEquipo_herramienta('delete', array('id'=>$Equipo_herramienta->id))) {
                DwMessage::valid('La Equipo_herramienta se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta Equipo_herramienta no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
    
}

