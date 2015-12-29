<?php
/**
 * @category
 * @package     Models
 * @subpackage
 * @author     
 * @copyright
 */

class MotivoParada extends ActiveRecord {
    
    protected function initialize() {
        //$this->belongs_to('sucursal');
    }  
    
    /**
     * Método para ver la información de una motivo_parada
     * @param int|string $id
     * @return motivo_parada
     */
    public function getInformacionmotivo_parada($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'motivo_parada.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = motivo_parada.sucursal_id';
        $condicion = ($isSlug) ? "motivo_parada.slug = '$id'" : "motivo_parada.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las motivo_paradas
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
   public function getListadoMotivo_parada($order='order.descripcion.asc', $page='') {
       $columns = 'motivo_parada.*';
        $order = $this->get_order($order, 'motivo_parada', array('motivo_parada'=>array('ASC'=>'motivo_parada.descripcion ASC, motivo_parada.observacion ASC',
                                                                              'DESC'=>'motivo_parada.descripcion DESC, motivo_parada.observacion ASC'),
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
    public static function setMotivo_parada($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new motivo_parada($data);
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
        $this->motivo_parada = strtoupper($this->motivo_parada);
        $this->direccion = strtoupper($this->direccion);
    }
    /**
     * Método para buscar motivo_paradas
     */
    public function getAjaxmotivo_paradas($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        if($field=='parroquia'){ $field ='parroquia.nombre';}

        $columns = 'motivo_parada.*, parroquia.*, parroquia.id as idparroquia ';
        $join = 'INNER JOIN parroquia  ON  motivo_parada.parroquia_id = parroquia.id ';   
        $order = $this->get_order($order, 'motivo_parada', array(                        
            'motivo_parada' => array(
                'ASC'=>'motivo_parada.motivo_parada ASC, motivo_parada.direccion ASC', 
                'DESC'=>'motivo_parada.motivo_parada DESC, motivo_parada.direccion DESC'
            ),
            'direccion' => array(
                'ASC'=>'motivo_parada.direccion ASC, motivo_parada.motivo_parada ASC', 
                'DESC'=>'motivo_parada.direccion DESC, motivo_parada.motivo_parada DESC'
            ),
            'celular' => array(
                'ASC'=>'motivo_parada.cedula ASC, motivo_parada.apellido1 ASC', 
                'DESC'=>'motivo_parada.cedula DESC, titular.apellido1 DESC'
            ),
            'parroquia' => array(
                'ASC'=>'parroquia.nombre ASC, motivo_parada.motivo_parada ASC', 
                'DESC'=>'parroquia.nombre DESC, motivo_parada.motivo_parada DESC'
            ),
            'telefono' => array(
                'ASC'=>'motivo_parada.telefono ASC, motivo_parada.motivo_parada ASC, motivo_parada.direccion ASC', 
                'DESC'=>'motivo_parada.telefono DESC, motivo_parada.motivo_parada DESC, motivo_parada.direccion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('motivo_parada', 'direccion', 'celular','parroquia.nombre', 'telefono');
        if(!in_array($field, $fields)) {
            $field = 'motivo_parada';
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
        if($this->id == 1) { //Para no eliminar la información de motivo_parada
            DwMessage::warning('Lo sentimos, pero esta motivo_parada no se puede eliminar.');
            return 'cancel';
        }
    }
}
