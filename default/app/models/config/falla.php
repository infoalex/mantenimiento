<?php
/**
 * @category
 * @package     Models
 * @subpackage
 * @author     
 * @copyright
 */

class Falla extends ActiveRecord {
    
    protected function initialize() {
        //$this->belongs_to('sucursal');
    }  
    
    /**
     * Método para ver la información de una falla
     * @param int|string $id
     * @return falla
     */
    public function getInformacionfalla($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'falla.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = falla.sucursal_id';
        $condicion = ($isSlug) ? "falla.slug = '$id'" : "falla.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las fallas
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
   public function getListadoFalla($order='order.descripcion.asc', $page='') {
       $columns = 'falla.*';
        $order = $this->get_order($order, 'falla', array('falla'=>array('ASC'=>'falla.descripcion ASC, falla.observacion ASC',
                                                                              'DESC'=>'falla.descripcion DESC, falla.observacion ASC'),
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
    public static function setFalla($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new falla($data);
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
        $this->falla = strtoupper($this->falla);
        $this->direccion = strtoupper($this->direccion);
    }
    /**
     * Método para buscar fallas
     */
    public function getAjaxfallas($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        if($field=='parroquia'){ $field ='parroquia.nombre';}

        $columns = 'falla.*, parroquia.*, parroquia.id as idparroquia ';
        $join = 'INNER JOIN parroquia  ON  falla.parroquia_id = parroquia.id ';   
        $order = $this->get_order($order, 'falla', array(                        
            'falla' => array(
                'ASC'=>'falla.falla ASC, falla.direccion ASC', 
                'DESC'=>'falla.falla DESC, falla.direccion DESC'
            ),
            'direccion' => array(
                'ASC'=>'falla.direccion ASC, falla.falla ASC', 
                'DESC'=>'falla.direccion DESC, falla.falla DESC'
            ),
            'celular' => array(
                'ASC'=>'falla.cedula ASC, falla.apellido1 ASC', 
                'DESC'=>'falla.cedula DESC, titular.apellido1 DESC'
            ),
            'parroquia' => array(
                'ASC'=>'parroquia.nombre ASC, falla.falla ASC', 
                'DESC'=>'parroquia.nombre DESC, falla.falla DESC'
            ),
            'telefono' => array(
                'ASC'=>'falla.telefono ASC, falla.falla ASC, falla.direccion ASC', 
                'DESC'=>'falla.telefono DESC, falla.falla DESC, falla.direccion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('falla', 'direccion', 'celular','parroquia.nombre', 'telefono');
        if(!in_array($field, $fields)) {
            $field = 'falla';
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
        if($this->id == 1) { //Para no eliminar la información de falla
            DwMessage::warning('Lo sentimos, pero esta falla no se puede eliminar.');
            return 'cancel';
        }
    }
}
