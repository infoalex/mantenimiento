<?php
/**
 * @category
 * @package     Models
 * @subpackage
 * @author     
 * @copyright
 */

class Material_recurso extends ActiveRecord {
    
    protected function initialize() {
        //$this->belongs_to('sucursal');
    }  
    
    /**
     * Método para ver la información de una material_recurso
     * @param int|string $id
     * @return material_recurso
     */
    public function getInformacionmaterial_recurso($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'material_recurso.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = material_recurso.sucursal_id';
        $condicion = ($isSlug) ? "material_recurso.slug = '$id'" : "material_recurso.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las material_recursos
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
   public function getListadoMaterial_recurso($order='order.descripcion.asc', $page='') {
       $columns = 'material_recurso.*';
        $order = $this->get_order($order, 'material_recurso', array('material_recurso'=>array('ASC'=>'material_recurso.descripcion ASC, material_recurso.observacion ASC',
                                                                              'DESC'=>'material_recurso.descripcion DESC, material_recurso.observacion ASC'),
                                                            'observacion'));
        if($page) {                
            return $this->paginated("columns: $columns", "order: $order", "page: $page");
        } else {
            return $this->find("columns: $columns", "order: $order", "page: $page");            
        }
    }
    /**
     * Método para setear
     * @param string $method Método a ejecutar (create, update, save)
     * @param array $data Array con la data => Input::post('model')
     * @param array $otherData Array con datos adicionales
     * @return Obj
     */
    public static function setMaterial_recurso($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new material_recurso($data);
        //Se verifica si contiene una data adicional para autocargar
        if ($optData) {
            $obj->dump_result_self($optData);
        }   
        $rs = $obj->$method();
        return ($rs) ? $obj : FALSE;
    }
    /**
     * Método que se ejecuta antes de guardar y/o modificar     
     */
    public function before_save() {        
        //MAYUSCULAS A LA BD
        $this->material_recurso = strtoupper($this->material_recurso);
        $this->direccion = strtoupper($this->direccion);
    }
    /**
     * Método para buscar material_recursos
     */
    public function getAjaxmaterial_recursos($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        if($field=='parroquia'){ $field ='parroquia.nombre';}

        $columns = 'material_recurso.*, parroquia.*, parroquia.id as idparroquia ';
        $join = 'INNER JOIN parroquia  ON  material_recurso.parroquia_id = parroquia.id ';   
        $order = $this->get_order($order, 'material_recurso', array(                        
            'material_recurso' => array(
                'ASC'=>'material_recurso.material_recurso ASC, material_recurso.direccion ASC', 
                'DESC'=>'material_recurso.material_recurso DESC, material_recurso.direccion DESC'
            ),
            'direccion' => array(
                'ASC'=>'material_recurso.direccion ASC, material_recurso.material_recurso ASC', 
                'DESC'=>'material_recurso.direccion DESC, material_recurso.material_recurso DESC'
            ),
            'celular' => array(
                'ASC'=>'material_recurso.cedula ASC, material_recurso.apellido1 ASC', 
                'DESC'=>'material_recurso.cedula DESC, titular.apellido1 DESC'
            ),
            'parroquia' => array(
                'ASC'=>'parroquia.nombre ASC, material_recurso.material_recurso ASC', 
                'DESC'=>'parroquia.nombre DESC, material_recurso.material_recurso DESC'
            ),
            'telefono' => array(
                'ASC'=>'material_recurso.telefono ASC, material_recurso.material_recurso ASC, material_recurso.direccion ASC', 
                'DESC'=>'material_recurso.telefono DESC, material_recurso.material_recurso DESC, material_recurso.direccion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('material_recurso', 'direccion', 'celular','parroquia.nombre', 'telefono');
        if(!in_array($field, $fields)) {
            $field = 'material_recurso';
        }        
        //if(! ($field=='parroquia' && $value=='todas') ) {
          $conditions= " $field LIKE '%$value%'";
        //} 

        if($page) {
            return $this->paginated("columns: $columns", "join: $join","conditions: $conditions",  "order: $order", "page: $page");
        } else {
            return $this->find("columns: $columns", "join: $join","conditions: $conditions", "order: $order");
        }  
        //"conditions: $conditions",
    } 
    /**
     * Callback que se ejecuta antes de eliminar
     */
    public function before_delete() {
        if($this->id == 1) { //Para no eliminar la información de material_recurso
            DwMessage::warning('Lo sentimos, pero esta material_recurso no se puede eliminar.');
            return 'cancel';
        }
    }
}
