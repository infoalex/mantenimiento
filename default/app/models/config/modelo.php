<?php
/**
 * infoalex
 * @category
 * @package     Models
 * @subpackage
 * @author      alexis borges
 * @copyright    
 */
class Modelo extends ActiveRecord {
    
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
     * Método para obtener modelos
     * @return obj
     */
   public function obtener_modelos($modelo) {
        if ($modelo != '') {
            $modelo = stripcslashes($modelo);
            $res = $this->find('columns: nombre', "nombre like '%{$modelo}%'");
            if ($res) {
                foreach ($res as $modelo) {
                    $modelos[] = $modelo->nombre;
                }
                return $modelos;
            }
        }
        return array('no hubo coincidencias');
    }
    /**
     * Método para ver la información de una sucursal
     * @param int|string $id
     * @return Sucursal
     */
    public function getInformacionModelo($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'modelo.*';
        $join = '';
        $condicion ="modelo.id = '$id'";
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las sucursales
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
    public function getListadoModelo($order='order.nombre.asc', $page='') {
       $columns = 'modelo.*';
        $order = $this->get_order($order, 'modelo', array('modelo'=>array('ASC'=>'modelo.nombre ASC, modelo.observacion ASC',
                                                                              'DESC'=>'modelo.nombre DESC, modelo.observacion ASC'),
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
    public static function setModelo($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new Modelo($data);
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
        //Nombre en mayusculas
        $this->nombre = strtoupper($this->nombre);
        $this->observacion = strtoupper($this->observacion);


        $conditions = "nombre = '$this->nombre'";
        $conditions.= (isset($this->id)) ? " AND id != $this->id" : '';
        if($this->count("conditions: $conditions")) {
            DwMessage::error('Lo sentimos, pero ya existe una sucursal registrada con el mismo nombre y ciudad.');
            return 'cancel';
        }
    }
     /**
     * Método para buscar sucursales
     */
    public function getAjaxModelos($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'modelo.* ';
        $order = $this->get_order($order, 'nombre', array(                        
            'nombre' => array(
                'ASC'=>'modelo.nombre ASC, modelo.nombre ASC', 
                'DESC'=>'modelo.nombre DESC, modelo.nombre DESC'
            ),
            'observacion' => array(
                'ASC'=>'modelo.observacion ASC, modelo.observacion ASC', 
                'DESC'=>'modelo.observacion DESC, modelo.observacion DESC'
            ),
        ));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre', 'observacion');
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

    }
    
}
