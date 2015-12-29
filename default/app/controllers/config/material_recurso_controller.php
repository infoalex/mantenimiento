<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las Material_recursos de la empresa
 *
 * @category    
 * @package     Controllers 
 * @author      Iván D. Meléndez (ivan.melendez@dailycript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co)
 */

Load::models('config/material_recurso');

class MaterialRecursoController extends BackendController {
    
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
    public function buscar($field='Material_recurso', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $Material_recurso = new MaterialRecurso();
        $Material_recursos = $Material_recurso->getAjaxMaterial_recursos($field, $value, $order, $page);
        if(empty($Material_recursos->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->Material_recursos = $Material_recursos;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de Material_recursos del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.Material_recurso.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $material_recurso = new MaterialRecurso();        
        $this->material_recursos = $material_recurso->getListadoMaterial_recurso($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Material_recursos';
    }
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('material_recurso')){
            if(Material_recurso::setMaterialRecurso('create', Input::post('material_recurso'))){
                DwMessage::valid('El Material_recurso se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }  
        } 
        $this->page_title = 'Agregar Material_recurso';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_Material_recurso', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Material_recurso = new MaterialRecurso();
        if(!$Material_recurso->getInformacionMaterial_recurso($id)) {            
            DwMessage::get('id_no_found')
;            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('Material_recurso') && DwSecurity::isValidKey(Input::post('Material_recurso_id_key'), 'form_key')) {
            if(Material_recurso::setMaterialRecurso('update', Input::post('Material_recurso'), array('id'=>$id, 'empresa_id'=>$Material_recurso->empresa_id))){
                DwMessage::valid('La Material_recurso se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        $this->Material_recurso = $Material_recurso;
        $this->page_title = 'Actualizar Material_recurso';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_Material_recurso', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $Material_recurso = new MaterialRecurso();
        if(!$Material_recurso->getInformacionMaterial_recurso($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Material_recurso::setMaterialRecurso('delete', array('id'=>$Material_recurso->id))) {
                DwMessage::valid('La Material_recurso se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta Material_recurso no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
    
}

