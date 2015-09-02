<?php
/**
 * @category
 * @package     Models
 * @subpackage
 * @author     
 * @copyright
 */

class Turno extends ActiveRecord {
    
    protected function initialize() {
        $this->belongs_to('sucursal');
    }  
    
    /**
     * Método para ver la información de una turno
     * @param int|string $id
     * @return turno
     */
    public function getInformacionturno($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'turno.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = turno.sucursal_id';
        $condicion = ($isSlug) ? "turno.slug = '$id'" : "turno.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las turnos
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
   public function getListadoTurno($order='order.turno.asc', $page='', $sucursal=null) {
        $sucursal = Filter::get($sucursal, 'int');
        
        $columns = 'sucursal.*, turno.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = turno.sucursal_id  ';        
        $conditions = (empty($sucursal)) ? 'turno.id > 0' : "sucursal.id = '$sucursal'";
        
        $order = $this->get_order($order, 'turno', array('turno'=>array('ASC'=>'turno.turno ASC',
                                                                              'DESC'=>'turno.turno DESC'),
                                                                                'turno_slug',
                                                                                'observacion'));
        if($page) {                
            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");            
        }
    }
    
    /**
     * Método para setear
     * @param string $method Método a ejecutar (create, update, save)
     * @param array $data Array con la data => Input::post('model')
     * @param array $otherData Array con datos adicionales
     * @return Obj
     */
    public static function setTurno($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new turno($data);
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
        $this->turno = strtoupper($this->turno);
        $this->direccion = strtoupper($this->direccion);
    }
    /**
     * Método para buscar turnos
     */
    public function getAjaxturnos($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        if($field=='parroquia'){ $field ='parroquia.nombre';}

        $columns = 'turno.*, parroquia.*, parroquia.id as idparroquia ';
        $join = 'INNER JOIN parroquia  ON  turno.parroquia_id = parroquia.id ';   
        $order = $this->get_order($order, 'turno', array(                        
            'turno' => array(
                'ASC'=>'turno.turno ASC, turno.direccion ASC', 
                'DESC'=>'turno.turno DESC, turno.direccion DESC'
            ),
            'direccion' => array(
                'ASC'=>'turno.direccion ASC, turno.turno ASC', 
                'DESC'=>'turno.direccion DESC, turno.turno DESC'
            ),
            'celular' => array(
                'ASC'=>'turno.cedula ASC, turno.apellido1 ASC', 
                'DESC'=>'turno.cedula DESC, titular.apellido1 DESC'
            ),
            'parroquia' => array(
                'ASC'=>'parroquia.nombre ASC, turno.turno ASC', 
                'DESC'=>'parroquia.nombre DESC, turno.turno DESC'
            ),
            'telefono' => array(
                'ASC'=>'turno.telefono ASC, turno.turno ASC, turno.direccion ASC', 
                'DESC'=>'turno.telefono DESC, turno.turno DESC, turno.direccion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('turno', 'direccion', 'celular','parroquia.nombre', 'telefono');
        if(!in_array($field, $fields)) {
            $field = 'turno';
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
        if($this->id == 1) { //Para no eliminar la información de turno
            DwMessage::warning('Lo sentimos, pero esta turno no se puede eliminar.');
            return 'cancel';
        }
    }
}
