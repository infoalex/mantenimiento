<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las Tipo_trabajos de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/tipo_trabajo');

class TipoTrabajoController extends BackendController {
    
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
    public function buscar($field='Tipo_trabajo', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $Tipo_trabajo = new TipoTrabajo();
        $Tipo_trabajos = $Tipo_trabajo->getAjaxTipo_trabajos($field, $value, $order, $page);
        if(empty($Tipo_trabajos->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->Tipo_trabajos = $Tipo_trabajos;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de Tipo_trabajos del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.Tipo_trabajo.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $tipo_trabajo = new TipoTrabajo();        
        $this->tipo_trabajos = $tipo_trabajo->getListadoTipo_trabajo($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Tipo_trabajos';
    }
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('tipo_trabajo')){
            if(Tipo_trabajo::setTipoTrabajo('create', Input::post('tipo_trabajo'))){
                DwMessage::valid('El Tipo_trabajo se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }  
        } 
        $this->page_title = 'Agregar Tipo_trabajo';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_Tipo_trabajo', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Tipo_trabajo = new TipoTrabajo();
        if(!$Tipo_trabajo->getInformacionTipo_trabajo($id)) {            
            DwMessage::get('id_no_found')
;            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('Tipo_trabajo') && DwSecurity::isValidKey(Input::post('Tipo_trabajo_id_key'), 'form_key')) {
            if(Tipo_trabajo::setTipoTrabajo('update', Input::post('Tipo_trabajo'), array('id'=>$id, 'empresa_id'=>$Tipo_trabajo->empresa_id))){
                DwMessage::valid('La Tipo_trabajo se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        $this->Tipo_trabajo = $Tipo_trabajo;
        $this->page_title = 'Actualizar Tipo_trabajo';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_Tipo_trabajo', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Tipo_trabajo = new TipoTrabajo();
        if(!$Tipo_trabajo->getInformacionTipo_trabajo($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Tipo_trabajo::setTipoTrabajo('delete', array('id'=>$Tipo_trabajo->id))) {
                DwMessage::valid('La Tipo_trabajo se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta Tipo_trabajo no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
    
}

