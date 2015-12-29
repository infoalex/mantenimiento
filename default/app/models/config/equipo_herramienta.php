<?php
/**
 * @category
 * @package     Models
 * @subpackage
 * @author     
 * @copyright
 */

class EquipoHerramienta extends ActiveRecord {
    
    protected function initialize() {
        //$this->belongs_to('sucursal');
    }  
    
    /**
     * Método para ver la información de una equipo_herramienta
     * @param int|string $id
     * @return equipo_herramienta
     */
    public function getInformacionequipo_herramienta($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'equipo_herramienta.* ';
        $join = 'INNER JOIN sucursal ON sucursal.id = equipo_herramienta.sucursal_id';
        $condicion = ($isSlug) ? "equipo_herramienta.slug = '$id'" : "equipo_herramienta.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las equipo_herramientas
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
   public function getListadoEquipo_herramienta($order='order.descripcion.asc', $page='') {
       $columns = 'equipo_herramienta.*';
        $order = $this->get_order($order, 'equipo_herramienta', array('equipo_herramienta'=>array('ASC'=>'equipo_herramienta.descripcion ASC, equipo_herramienta.observacion ASC',
                                                                              'DESC'=>'equipo_herramienta.descripcion DESC, equipo_herramienta.observacion ASC'),
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
    public static function setEquipoHerramienta($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new EquipoHerramienta($data);
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
        $this->equipo_herramienta = strtoupper($this->equipo_herramienta);
        $this->direccion = strtoupper($this->direccion);
    }
    /**
     * Método para buscar equipo_herramientas
     */
    public function getAjaxequipo_herramientas($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        if($field=='parroquia'){ $field ='parroquia.nombre';}

        $columns = 'equipo_herramienta.*, parroquia.*, parroquia.id as idparroquia ';
        $join = 'INNER JOIN parroquia  ON  equipo_herramienta.parroquia_id = parroquia.id ';   
        $order = $this->get_order($order, 'equipo_herramienta', array(                        
            'equipo_herramienta' => array(
                'ASC'=>'equipo_herramienta.equipo_herramienta ASC, equipo_herramienta.direccion ASC', 
                'DESC'=>'equipo_herramienta.equipo_herramienta DESC, equipo_herramienta.direccion DESC'
            ),
            'direccion' => array(
                'ASC'=>'equipo_herramienta.direccion ASC, equipo_herramienta.equipo_herramienta ASC', 
                'DESC'=>'equipo_herramienta.direccion DESC, equipo_herramienta.equipo_herramienta DESC'
            ),
            'celular' => array(
                'ASC'=>'equipo_herramienta.cedula ASC, equipo_herramienta.apellido1 ASC', 
                'DESC'=>'equipo_herramienta.cedula DESC, titular.apellido1 DESC'
            ),
            'parroquia' => array(
                'ASC'=>'parroquia.nombre ASC, equipo_herramienta.equipo_herramienta ASC', 
                'DESC'=>'parroquia.nombre DESC, equipo_herramienta.equipo_herramienta DESC'
            ),
            'telefono' => array(
                'ASC'=>'equipo_herramienta.telefono ASC, equipo_herramienta.equipo_herramienta ASC, equipo_herramienta.direccion ASC', 
                'DESC'=>'equipo_herramienta.telefono DESC, equipo_herramienta.equipo_herramienta DESC, equipo_herramienta.direccion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('equipo_herramienta', 'direccion', 'celular','parroquia.nombre', 'telefono');
        if(!in_array($field, $fields)) {
            $field = 'equipo_herramienta';
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
        if($this->id == 1) { //Para no eliminar la información de equipo_herramienta
            DwMessage::warning('Lo sentimos, pero esta equipo_herramienta no se puede eliminar.');
            return 'cancel';
        }
    }
}
