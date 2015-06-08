<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las Sectores de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/sector');

class SectorController extends BackendController {
    
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
    public function buscar($field='Sector', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $Sector = new Sector();
        $Sectores = $Sector->getAjaxSectores($field, $value, $order, $page);
        if(empty($Sectores->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->Sectores = $Sectores;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de Sectores del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.Sector.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $sector = new Sector();        
        $this->sectores = $sector->getListadoSector($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Sectores';
    }
    /**
     * Método para agregar
     */
    public function agregar() {
        //$empresa = Session::get('empresa', 'config');
        $empresa = 1;
        if(Input::hasPost('Sector')){
            if(Sector::setSector('create', Input::post('Sector'), array('empresa_id'=>$empresa))){
                DwMessage::valid('La Sector se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }  
        } 
        $this->page_title = 'Agregar Sector';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_Sector', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Sector = new Sector();
        if(!$Sector->getInformacionSector($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('Sector') && DwSecurity::isValidKey(Input::post('Sector_id_key'), 'form_key')) {
            if(Sector::setSector('update', Input::post('Sector'), array('id'=>$id, 'empresa_id'=>$Sector->empresa_id))){
                DwMessage::valid('La Sector se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        $this->Sector = $Sector;
        $this->page_title = 'Actualizar Sector';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_Sector', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Sector = new Sector();
        if(!$Sector->getInformacionSector($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Sector::setSector('delete', array('id'=>$Sector->id))) {
                DwMessage::valid('La Sector se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta Sector no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
    
}

