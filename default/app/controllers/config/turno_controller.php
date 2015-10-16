<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las Turnoes de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/turno');

class TurnoController extends BackendController {
    
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
    public function buscar($field='Turno', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $Turno = new Turno();
        $Turnoes = $Turno->getAjaxTurnoes($field, $value, $order, $page);
        if(empty($Turnoes->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->Turnoes = $Turnoes;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de Turnoes del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.Turno.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $turno = new Turno();        
        $this->turnos = $turno->getListadoTurno($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Turnoes';
    }
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('turno')){
            if(Turno::setTurno('create', Input::post('turno'))){
                DwMessage::valid('El Turno se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }  
        } 
        $this->page_title = 'Agregar Turno';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_Turno', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Turno = new Turno();
        if(!$Turno->getInformacionTurno($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('Turno') && DwSecurity::isValidKey(Input::post('Turno_id_key'), 'form_key')) {
            if(Turno::setTurno('update', Input::post('Turno'), array('id'=>$id, 'empresa_id'=>$Turno->empresa_id))){
                DwMessage::valid('La Turno se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        $this->Turno = $Turno;
        $this->page_title = 'Actualizar Turno';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_Turno', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Turno = new Turno();
        if(!$Turno->getInformacionTurno($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Turno::setTurno('delete', array('id'=>$Turno->id))) {
                DwMessage::valid('La Turno se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta Turno no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
    
}

