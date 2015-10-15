<?php
/**
 * @category
 * @package     Models
 * @subpackage
 * @author     
 * @copyright
 */

class Tipo_trabajo extends ActiveRecord {
    
    protected function initialize() {
        //$this->belongs_to('sucursal');
    }  
    
    /**
     * Método para ver la información de una tipo_trabajo
     * @param int|string $id
     * @return tipo_trabajo
     */
    public function getInformaciontipo_trabajo($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'tipo_trabajo.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = tipo_trabajo.sucursal_id';
        $condicion = ($isSlug) ? "tipo_trabajo.slug = '$id'" : "tipo_trabajo.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las tipo_trabajos
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
   public function getListadoTipo_trabajo($order='order.descripcion.asc', $page='') {
       $columns = 'tipo_trabajo.*';
        $order = $this->get_order($order, 'tipo_trabajo', array('tipo_trabajo'=>array('ASC'=>'tipo_trabajo.descripcion ASC, tipo_trabajo.observacion ASC',
                                                                              'DESC'=>'tipo_trabajo.descripcion DESC, tipo_trabajo.observacion ASC'),
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
    public static function setTipo_trabajo($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new tipo_trabajo($data);
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
        $this->tipo_trabajo = strtoupper($this->tipo_trabajo);
        $this->direccion = strtoupper($this->direccion);
    }
    /**
     * Método para buscar tipo_trabajos
     */
    public function getAjaxtipo_trabajos($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        if($field=='parroquia'){ $field ='parroquia.nombre';}

        $columns = 'tipo_trabajo.*, parroquia.*, parroquia.id as idparroquia ';
        $join = 'INNER JOIN parroquia  ON  tipo_trabajo.parroquia_id = parroquia.id ';   
        $order = $this->get_order($order, 'tipo_trabajo', array(                        
            'tipo_trabajo' => array(
                'ASC'=>'tipo_trabajo.tipo_trabajo ASC, tipo_trabajo.direccion ASC', 
                'DESC'=>'tipo_trabajo.tipo_trabajo DESC, tipo_trabajo.direccion DESC'
            ),
            'direccion' => array(
                'ASC'=>'tipo_trabajo.direccion ASC, tipo_trabajo.tipo_trabajo ASC', 
                'DESC'=>'tipo_trabajo.direccion DESC, tipo_trabajo.tipo_trabajo DESC'
            ),
            'celular' => array(
                'ASC'=>'tipo_trabajo.cedula ASC, tipo_trabajo.apellido1 ASC', 
                'DESC'=>'tipo_trabajo.cedula DESC, titular.apellido1 DESC'
            ),
            'parroquia' => array(
                'ASC'=>'parroquia.nombre ASC, tipo_trabajo.tipo_trabajo ASC', 
                'DESC'=>'parroquia.nombre DESC, tipo_trabajo.tipo_trabajo DESC'
            ),
            'telefono' => array(
                'ASC'=>'tipo_trabajo.telefono ASC, tipo_trabajo.tipo_trabajo ASC, tipo_trabajo.direccion ASC', 
                'DESC'=>'tipo_trabajo.telefono DESC, tipo_trabajo.tipo_trabajo DESC, tipo_trabajo.direccion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('tipo_trabajo', 'direccion', 'celular','parroquia.nombre', 'telefono');
        if(!in_array($field, $fields)) {
            $field = 'tipo_trabajo';
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
        if($this->id == 1) { //Para no eliminar la información de tipo_trabajo
            DwMessage::warning('Lo sentimos, pero esta tipo_trabajo no se puede eliminar.');
            return 'cancel';
        }
    }
}
