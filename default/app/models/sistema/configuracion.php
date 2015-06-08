<?php
/**
 * SGIMPC (Sistema de Gestion de Incidencias de Mantenimientos Preventivos y Correctivos )
 *
 * Descripcion: Modelo para la configuracion
 *
 * @category
 * @package     Models
 * @subpackage
 * @author      Grupo SGIMPC UPTP 
 * @copyright   Copyright (c) 2015 UPTP / E.M.S. Arroz del Alba S.A.
 */

class Configuracion extends ActiveRecord {

    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {        
        //$this->belongs_to('tipo_nuip');
    }

    /**
     * Método para obtener la información de la empresa
     * @return obj
     */
    public function getInformacionConfiguracion() {
        $columnas = 'configuracion.*';
        $join = '';
        return $this->find_first("columns: $columnas", "join: $join", 'conditions: configuracion.id IS NOT NULL', 'order: configuracion.fecha_registro DESC');
    }    
    
    /**
     * Método para registrar y modificar los datos de la empresa
     * 
     * @param string $method Método para guardar en la base de datos (create, update)
     * @param array $data Array de datos para la autocarga de objetos
     * @param arraty $other Se utiliza para autocargar datos adicionales al objeto
     * @return Empresa
     */
    public static function setConfiguracion($method, $data, $optData=null) {
        $obj = new Configuracion($data);
        if($optData) {
            $obj->dump_result_self($optData);
        }
        $rs = $obj->$method();
        return ($rs) ? $obj : NULL;            
    }
    
    public function after_save() {
        Session::delete('seguridad', 'config');
        
    }

    
}
?>
