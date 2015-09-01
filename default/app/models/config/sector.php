<?php
/**
 * @category
 * @package     Models
 * @subpackage
 * @author     
 * @copyright
 */

class Sector extends ActiveRecord {
    
    protected function initialize() {
        $this->belongs_to('sucursal');
    }  
    
    /**
     * Método para ver la información de una sector
     * @param int|string $id
     * @return sector
     */
    public function getInformacionsector($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'sector.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = sector.sucursal_id';
        $condicion = ($isSlug) ? "sector.slug = '$id'" : "sector.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las sectores
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
   public function getListadoSector($order='order.sector.asc', $page='', $sucursal=null) {
        $sucursal = Filter::get($sucursal, 'int');
        
        $columns = 'sucursal.*, sector.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = sector.sucursal_id  ';        
        $conditions = (empty($sucursal)) ? 'sector.id > 0' : "sucursal.id = '$sucursal'";
        
        $order = $this->get_order($order, 'sector', array('sector'=>array('ASC'=>'sector.sector ASC',
                                                                              'DESC'=>'sector.sector DESC'),
                                                                                'sector_slug',
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
    public static function setSector($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new sector($data);
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
        $this->sector = strtoupper($this->sector);
        $this->direccion = strtoupper($this->direccion);
    }
    /**
     * Método para buscar sectores
     */
    public function getAjaxsectores($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        if($field=='parroquia'){ $field ='parroquia.nombre';}

        $columns = 'sector.*, parroquia.*, parroquia.id as idparroquia ';
        $join = 'INNER JOIN parroquia  ON  sector.parroquia_id = parroquia.id ';   
        $order = $this->get_order($order, 'sector', array(                        
            'sector' => array(
                'ASC'=>'sector.sector ASC, sector.direccion ASC', 
                'DESC'=>'sector.sector DESC, sector.direccion DESC'
            ),
            'direccion' => array(
                'ASC'=>'sector.direccion ASC, sector.sector ASC', 
                'DESC'=>'sector.direccion DESC, sector.sector DESC'
            ),
            'celular' => array(
                'ASC'=>'sector.cedula ASC, sector.apellido1 ASC', 
                'DESC'=>'sector.cedula DESC, titular.apellido1 DESC'
            ),
            'parroquia' => array(
                'ASC'=>'parroquia.nombre ASC, sector.sector ASC', 
                'DESC'=>'parroquia.nombre DESC, sector.sector DESC'
            ),
            'telefono' => array(
                'ASC'=>'sector.telefono ASC, sector.sector ASC, sector.direccion ASC', 
                'DESC'=>'sector.telefono DESC, sector.sector DESC, sector.direccion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('sector', 'direccion', 'celular','parroquia.nombre', 'telefono');
        if(!in_array($field, $fields)) {
            $field = 'sector';
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
        if($this->id == 1) { //Para no eliminar la información de sector
            DwMessage::warning('Lo sentimos, pero esta sector no se puede eliminar.');
            return 'cancel';
        }
    }
}
