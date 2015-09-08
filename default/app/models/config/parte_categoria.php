<?php

class ParteCategoria extends ActiveRecord {
    protected function initialize() {
    }  
    /**
     * Método para obtener fabricantes
     * @return obj
     */
   public function obtener_fabricantes($fabricante) {
        if ($fabricante != '') {
            $fabricante = stripcslashes($fabricante);
            $res = $this->find('columns: nombre', "nombre like '%{$fabricante}%'");
            if ($res) {
                foreach ($res as $fabricante) {
                    $fabricantes[] = $fabricante->nombre;
                }
                return $fabricantes;
            }
        }
        return array('no hubo coincidencias');
    }
    /**
     * Método para ver la información de una sucursal
     * @param int|string $id
     * @return Sucursal
     */
    public function getInformacionCategoria($id, $isSlug=false) {
        $id = ($isSlug) ? Filter::get($id, 'string') : Filter::get($id, 'numeric');
        $columnas = 'parte_categoria.*';
        $condicion ="parte_categoria.id = '$id'";
        return $this->find_first("columns: $columnas",  "conditions: $condicion");
    } 
    
    /**
     * Método que devuelve las sucursales
     * @param string $order
     * @param int $page 
     * @return ActiveRecord
     */
    public function getListadoCategoria($order='order.nombre.asc', $page='') {
       $columns = 'parte_categoria.*';
        $order = $this->get_order($order, 'parte_categoria', array('parte_categoria'=>array('ASC'=>'parte_categoria.nombre ASC, parte_categoria.observacion ASC',
                                                                              'DESC'=>'parte_categoria.nombre DESC, parte_categoria.observacion ASC'),
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
    public static function setFabricante($method, $data, $optData=null) {
        //Se aplica la autocarga
        $obj = new ParteCategoria($data);
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
        $this->nombre = strtoupper($this->nombre);
        $this->observacion = strtoupper($this->observacion);
	        
        $this->nombre = Filter::get($this->nombre, 'string');
        $this->observacion = Filter::get($this->observacion, 'string');
           
        $conditions = "nombre = '$this->nombre'";
        $conditions.= (isset($this->id)) ? " AND id != $this->id" : '';
        if($this->count("conditions: $conditions")) {
            DwMessage::error('Lo sentimos, pero ya existe un fabricante registrado con el mismo nombre.');
            return 'cancel';
        }
    }
     /**
     * Método para buscar sucursales
     */
    public function getAjaxFabricantes($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) < 1 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'parte_categoria.* ';
        $order = $this->get_order($order, 'nombre', array(                        
            'nombre' => array(
                'ASC'=>'parte_categoria.nombre ASC, parte_categoria.nombre ASC', 
                'DESC'=>'parte_categoria.nombre DESC, parte_categoria.nombre DESC'
            ),
            'observacion' => array(
                'ASC'=>'parte_categoria.observacion ASC, parte_categoria.observacion ASC', 
                'DESC'=>'parte_categoria.observacion DESC, parte_categoria.observacion DESC'
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
        if($this->id == 1) { //Para no eliminar la información de sucursal
            DwMessage::warning('Lo sentimos, pero esta sucursal no se puede eliminar.');
            return 'cancel';
        }
    }
    
}
