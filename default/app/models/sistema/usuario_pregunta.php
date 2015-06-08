<?php
/**
 * SGIMPC (Sistema de Gestion de Incidencias de Mantenimientos Preventivos y Correctivos )
 *
 * Descripcion: Clase que gestiona lo relacionado con los usuarios sus y preguntas
 *
 * @category
 * @package     Models
 * @subpackage
 * @author      Grupo SGIMPC UPTP 
 * @copyright   Copyright (c) 2015 UPTP / E.M.S. Arroz del Alba S.A.
 */

class UsuarioPregunta extends ActiveRecord {
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('usuario');
    }
      

}
