<?php

/**
 *
 * Class IndexController
 */
Load::model('incidencias/incidencia');

class IndexController extends BackendController {

    public $page_title = 'Escritorio';

    public $page_module = 'Escritorio';

    public function index() {
        $id = Session::get('id');
        $objIncidencia = new Incidencia();
        $this->incidencias = $objIncidencia->getIncidenciasByUser($id);

        //Obtener el hostorico de incidencias de un usuario
        $this->historico_incidencias = $objIncidencia->getIncidenciasHistoryByUser($id);

        /*
         * Contar todass las soliciutes ejecutas por ese tecnico
        */


        $total = $objIncidencia->getTotalSolicitudByUser($id);
        $this->total_solicitudes = $total->total;
    }
}
