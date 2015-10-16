<?php
/**
 * @category
 * @package     Models
 * @subpackage
 * @author     
 * @copyright
 */

class Mano_obra extends ActiveRecord {
    
    protected function initialize() {
        //$this->belongs_to('sucursal');
    }  
    
    /**
     * Método para ver la información de una mano_obra
     * @param int|string $id
     * @return mano_obra
     */
    public function getInformacionmano_obra($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'mano_obra.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = mano_obra.sucursal_id';
        $condicion = ($isSlug) ? "mano_obra.slug = '$id'" : "mano_obra.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las mano_obras
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
   public function getListadoMano_obra($order='order.descripcion.asc', $page='') {
       $columns = 'mano_obra.*';
        $order = $this->get_order($order, 'mano_obra', array('mano_obra'=>array('ASC'=>'mano_obra.descripcion ASC, mano_obra.observacion ASC',
                                                                              'DESC'=>'mano_obra.descripcion DESC, mano_obra.observacion ASC'),
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
    public static function setMano_obra($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new mano_obra($data);
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
        $this->mano_obra = strtoupper($this->mano_obra);
        $this->direccion = strtoupper($this->direccion);
    }
    /**
     * Método para buscar mano_obras
     */
    public function getAjaxmano_obras($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        if($field=='parroquia'){ $field ='parroquia.nombre';}

        $columns = 'mano_obra.*, parroquia.*, parroquia.id as idparroquia ';
        $join = 'INNER JOIN parroquia  ON  mano_obra.parroquia_id = parroquia.id ';   
        $order = $this->get_order($order, 'mano_obra', array(                        
            'mano_obra' => array(
                'ASC'=>'mano_obra.mano_obra ASC, mano_obra.direccion ASC', 
                'DESC'=>'mano_obra.mano_obra DESC, mano_obra.direccion DESC'
            ),
            'direccion' => array(
                'ASC'=>'mano_obra.direccion ASC, mano_obra.mano_obra ASC', 
                'DESC'=>'mano_obra.direccion DESC, mano_obra.mano_obra DESC'
            ),
            'celular' => array(
                'ASC'=>'mano_obra.cedula ASC, mano_obra.apellido1 ASC', 
                'DESC'=>'mano_obra.cedula DESC, titular.apellido1 DESC'
            ),
            'parroquia' => array(
                'ASC'=>'parroquia.nombre ASC, mano_obra.mano_obra ASC', 
                'DESC'=>'parroquia.nombre DESC, mano_obra.mano_obra DESC'
            ),
            'telefono' => array(
                'ASC'=>'mano_obra.telefono ASC, mano_obra.mano_obra ASC, mano_obra.direccion ASC', 
                'DESC'=>'mano_obra.telefono DESC, mano_obra.mano_obra DESC, mano_obra.direccion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('mano_obra', 'direccion', 'celular','parroquia.nombre', 'telefono');
        if(!in_array($field, $fields)) {
            $field = 'mano_obra';
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
        if($this->id == 1) { //Para no eliminar la información de mano_obra
            DwMessage::warning('Lo sentimos, pero esta mano_obra no se puede eliminar.');
            return 'cancel';
        }
    }
}
