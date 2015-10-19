<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las profesiones de la empresa
*/
 Load::models('equipo/equipo', 'config/equipo_parte');

class EquipoController extends BackendController {
   
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Equipos/Maquinarias';
    }

    /**
    * Metodo para imprimir la ficha del equipo
    */
    public function ficha($equipo_id, $formato='html') {
        if(empty($equipo_id)) {
            DwMessage::info('No se ha seleccionado un equipo');
            return DwRedirect::toAction('listar');
        }
        $equipo_parte = new EquipoParte();
        $this->ficha_equipo = $equipo_parte->getInformacionEquipoConPartes($equipo_id);
        $this->page_module = 'Ficha del equipo ';
        $this->page_format = $formato; 
    }    
}

