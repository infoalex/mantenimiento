<?php
/**
 * infoalex
 * @category
 * @package     Models
 * @subpackage
 * @author      alexis borges
 * @copyright    
 */
class Proveedor extends ActiveRecord {
    
    /**
     * Constante para definir el id de la oficina principal
     */
    const OFICINA_PRINCIPAL = 1;

    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
  /*      $this->belongs_to('empresa');
        $this->belongs_to('ciudad');
        $this->has_many('usuario');

        $this->validates_presence_of('sucursal', 'message: Ingresa el nombre de la sucursal');        
        $this->validates_presence_of('direccion', 'message: Ingresa la dirección de la sucursal.');
        $this->validates_presence_of('ciudad_id', 'message: Indica la ciudad de ubicación de la sucursal.');
    */            
    }  
    /**
     * Método para obtener proveedores
     * @return obj
     */
   public function obtener_proveedores($proveedor) {
        if ($proveedor != '') {
            $proveedor = stripcslashes($proveedor);
            $res = $this->find('columns: nombre', "nombre like '%{$proveedor}%'");
            if ($res) {
                foreach ($res as $proveedor) {
                    $proveedores[] = $proveedor->nombre;
                }
                return $proveedores;
            }
        }
        return array('no hubo coincidencias');
    }
    /**
     * Método para ver la información de una sucursal
     * @param int|string $id
     * @return Sucursal
     */
    public function getInformacionProveedor($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'proveedor.*';
        $join = '';
        $condicion ="proveedor.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las sucursales
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
    public function getListadoProveedor($order='order.nombre.asc', $page='') {
       $columns = 'proveedor.*';
        $order = $this->get_order($order, 'proveedor', array('proveedor'=>array('ASC'=>'proveedor.nombre ASC, proveedor.direccion ASC, proveedor.telefono ASC, proveedor.correo ASC, proveedor.observacion ASC',
                                                                              'DESC'=>'proveedor.nombre DESC, proveedor.direccion ASC, proveedor.telefono ASC, proveedor.correo ASC, proveedor.observacion ASC')));
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
    public static function setProveedor($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new Proveedor($data);
        //Se verifica si contiene una data adicional para autocargar
        if ($optData) {
            $obj->dump_result_self($optData);
        }   
        if($method!='delete') {
            //$obj->ciudad_id = Ciudad::setCiudad($obj->ciudad)->id;        
        }
        $rs = $obj->$method();
        
        return ($rs) ? $obj : FALSE;
    }

    /**
     * Método que se ejecuta antes de guardar y/o modificar     
     */
    public function before_save() {        
        $this->nombre = Filter::get($this->nombre, 'string');
        $this->observacion = Filter::get($this->observacion, 'string');
           
        $conditions = "nombre = '$this->nombre'";
        $conditions.= (isset($this->id)) ? " AND id != $this->id" : '';
        if($this->count("conditions: $conditions")) {
            DwMessage::error('Lo sentimos, pero ya existe un proveedor registrado con el mismo nombre.');
            return 'cancel';
        }
    }
     /**
     * Método para buscar sucursales
     */
    public function getAjaxProveedores($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'proveedor.* ';
        $order = $this->get_order($order, 'nombre', array(                        
            'nombre' => array(
                'ASC'=>'proveedor.nombre ASC, proveedor.nombre ASC', 
                'DESC'=>'proveedor.nombre DESC, proveedor.nombre DESC'
            ),
            'direccion' => array(
                'ASC'=>'proveedor.direccion ASC, proveedor.direccion ASC', 
                'DESC'=>'proveedor.direccion DESC, proveedor.direccion DESC'
            ),
            'observacion' => array(
                'ASC'=>'proveedor.observacion ASC, proveedor.observacion ASC', 
                'DESC'=>'proveedor.observacion DESC, proveedor.observacion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre','direccion','observacion');
        if(!in_array($field, $fields)) {
            $field = 'nombre';
        }        
        //if(! ($field=='parroquia' && $value=='todas') ) {
          $conditions= " $field LIKE '%$value%'";
        //} 

        if($page) {
            return $this->paginated("columns: $columns", "conditions: $conditions",  "order: $order", "page: $page");
        } else {
            return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        }  
        //"conditions: $conditions",
    } 
   
    /**
     * Callback que se ejecuta antes de eliminar
     */
    public function before_delete() {
        if($this->id == 1) { //Para no eliminar la información de sucursal
            DwMessage::warning('Lo sentimos, pero esta sucursal no se puede eliminar.');
            return 'cancel';
        }
    }
    
}
