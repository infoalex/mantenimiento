<?php
/**
 * Dailyscript - Web | App | Media
 *
 * Clase que gestiona lo relacionado con los tipos de identificacion
 *
 * @category    Parámetros
 * @package     Models
 * @author      Iván D. Meléndez (ivan.melendez@dailyscript.com.co)
 * @copyright   Copyright (c) 2013 Dailyscript Team (http://www.dailyscript.com.co) 
 */

class Hreembolso extends ActiveRecord {

    /**
     * Método contructor
     */
    public function initialize() {
       // $this->has_many('empresa');
       // $this->has_many('persona');
    }

    /**
     * Método para listar los tipos de identificación
     * @return array
     */
    public function getPeriodos() {
        return $this->find_all_by_sql("SELECT date_part('month'::text, w.fecha_solicitud)
as mes, count(w.fecha_solicitud) as total_reembolsos  FROM hreembolso w  
 GROUP BY  date_part('month'::text, w.fecha_solicitud)
 order by mes");
    }

    public function getCountRembolsostitular($id) {
        return $this->find_all_by_sql("SELECT CASE WHEN COUNT(1) = 0
THEN 0
ELSE count(tcedula) END
from hreembolso where tcedula ='".$id."'  and pcedula = '".$id."'  
");
    }
    public function getCountRembolsosbeneficiario($id) {
        return $this->find_all_by_sql("SELECT CASE WHEN COUNT(1) = 0
THEN 0
ELSE count(tcedula) END
from hreembolso where tcedula ='".$id."' and pcedula <> '".$id."' 
");
    }


    public function getMontoRembolsostitular($id) {
        return $this->find_all_by_sql("SELECT CASE WHEN count(1) = 0
THEN 0
ELSE sum(monto_pagado_reembolso)  END
from hreembolso where tcedula ='".$id."' and pcedula = '".$id."'");
    }
    public function getMontoRembolsosbeneficiario($id) {
        return $this->find_all_by_sql("SELECT CASE WHEN count(1) = 0
THEN 0
ELSE sum(monto_pagado_reembolso)  END
from hreembolso where tcedula ='".$id."' and pcedula <> '".$id."'");
    }
    
}

?>
