<?php
/**
 * 
 *
 * Clase que gestiona lo relacionado con los paises
 *
 * @category    Parámetros
 * @package     Models
 * @author      Javier León (jel1284@gmail.com)
 * @copyright   Copyright (c) 2014 Arroz del Alba 
 */

class Sucursal extends ActiveRecord {

    /**
     * Método contructor
     */
    public function initialize() {
        $this->has_many('empresa');
                
    }

    /**
     * Método para listar los tipos de identificación
     * @return array
     */
    public function getListadoSucursal() {
        return $this->find('order: sucursal ASC');
    }



}
?>
