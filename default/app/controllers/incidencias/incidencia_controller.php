<?php
/**
 * @category
 * @package     Controllers 
 * @author
 * @copyright
 */
Load::models('incidencias/incidencia','config/departamento', 'config/falla', 'equipo/equipo', 'config/sector', 'mantenimientos/mantenimiento');

class IncidenciaController extends BackendController {
    /**
     * Constante para definir el tipo de solicitud
     */
    const TPS = 1;    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Incidencias';
    }
    /**
     * Método principal
     */
    public function index() {
        DwRedirect::toAction('registro');
    }
    /**
     * Método para agregar
     */
    public function agregar() {
       // $empresa = Session::get('empresa', 'config');
        $incidencias = new Incidencia();
        if(Input::hasPost('incidencia')) {
            $inci = Incidencia::setIncidencia('create', Input::post('incidencia'));
            if($inci) {
                DwMessage::valid('La solicitud se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }
            else {
                DwMessage::error('Errores procesando solicitud!');
            }           
        } 
        $this->page_title = 'Agregar Incidencia';
    }
    /**
     * agregar las piezas a usar en el mantenimiento
     */
    public function agregar_piezas($id) {


    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.nombre.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $incidencia = new Incidencia();        
        $this->incidencias = $incidencia->getListadoIncidencia($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Incidencias';
    }
    /**
     * Método para registro
     */
    public function registro($order='order.nombre.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $incidencias = new Incidencias();        
        $this->incidenciass = $incidencias->getListadoRegistroIncidencias($order, $page,$tps=self::TPS);
        $this->order = $order;        
        $this->page_title = 'Registro de Solicitudes de Atención Primaria';
    }
    /**
     * Método para aprobacion
     */
    public function aprobacion($order='order.nombre.asc', $page='pag.1') { 
            $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
            $incidencias = new Incidencias();        
            $this->incidenciass = $incidencias->getListadoAprobacionIncidencias($order, $page,$tps=self::TPS);
            $this->order = $order;        
            $this->page_title = 'Aprobación de Solicitudes de Atención Primaria';
    }
    /**
     * Método para cargar las solicitudes siniestradas para mandar a facturar
     */
    public function facturacion($order='order.nombre.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $incidencias = new Incidencias();        
        $this->incidenciass = $incidencias->getListadoSiniestrosIncidencias($order, $page,$tps=self::TPS);
        $this->order = $order;        
        $this->page_title = 'Cargar Facturas a las solicitudes de Atención Primaria';
    }

     /**
     * Método para 
     */
    public function aprobadas($order='order.nombre.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $incidencias = new Incidencias();        
        $this->incidenciass = $incidencias->getListadoContabilizarIncidencias($order, $page,$tps=self::TPS);
        $this->order = $order;        
        $this->page_title = 'Contabilizar Solicitudes de Atención Primaria';
    }
     /**
     * Método para cargar los siniestros
     */
    public function siniestro($key) { 
        if(!$id = DwSecurity::isValidKey($key, 'upd_incidencias', 'int')) {
            return DwRedirect::toAction('registro');
        }        
        $incidencias = new Incidencias();
        $incidencias_patologia = new IncidenciasPatologia();
        if(!$incidencias->getInformacionIncidencias($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('registro');
        }
        if(Input::hasPost('incidencias')) {
            ActiveRecord::beginTrans();
            if(IncidenciasPatologia::setSolServicioPatolgia(Input::post('patologia_id'), $id)) {
                $sol = $incidencias->getInformacionIncidencias($id);
                //Input::post('diagnostico') cambie el nombre del campo para poder tomar el valor revisar en el view 
                $sol->diagnostico = strtoupper(Input::post('diagnostico'));
                $sol->motivo = strtoupper(Input::post('motivo'));
                $sol->estado_solicitud="S";
                $sol->save();               
                ActiveRecord::commitTrans();    
                DwMessage::valid('La solicitud se ha contabilizado correctamente!');
                 return DwRedirect::toAction('facturacion');
            }else{
                ActiveRecord::rollbackTrans();
                DwMessage::error('La solicitud ha dao peos!');
                return DwRedirect::toAction('aprobadas');
            }
        } 
        $this->incidencias = $incidencias;
        $this->page_title = 'Cargar Siniestro';        
    }
    /**
    * Método para cargar las facturas
    */
    public function facturar($key){
        if(!$id = DwSecurity::isValidKey($key, 'upd_incidencias', 'int')) {
            return DwRedirect::toAction('registro');
        }
        $incidencias = new Incidencias();
        $obj = new IncidenciasPatologia();
        //$factura = new Factura();
        $factura_dt = new FacturaDt();
        $this->sol =  $obj->getInformacionIncidenciasPatologia($id);
        if(!$incidencias->getInformacionIncidencias($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('registro');
        }
        if(Input::hasPost('factura')) {
            ActiveRecord::beginTrans();
            $factu = Factura::setFactura('create', Input::post('factura'));
            if($factu){
                if(FacturaDt::setFacturaDt(Input::post('descripcion'), Input::post('cantidad'), Input::post('monto'), Input::post('exento'), $factu->id)) {
                    $solfactura = IncidenciasFactura::setIncidenciasFactura($factu->id, $id);
                    if($solfactura){
                        if(Input::post('multifactura')){ //para saber si va a cargar multiples facturas sobre esa solicitud 
                            $solser = $incidencias->getInformacionIncidencias($id);
                            $solser->estado_solicitud="G"; //estado G parcialmente facturada 
                            $solser->save();
                            ActiveRecord::commitTrans();
                            DwMessage::valid('Se ha cargado la factura exitosamente!');
                            $key_upd = DwSecurity::getKey($id, 'upd_incidencias'); 
                            return DwRedirect::toAction('facturar/'.$key_upd);   //retorna a la misma visata de facturacion 
                        }
                        else{
                            $solser = $incidencias->getInformacionIncidencias($id);
                            $solser->estado_solicitud="F";
                            $solser->save();
                            ActiveRecord::commitTrans();
                            DwMessage::valid('Se ha cargado la factura exitosamente!');
                          return DwRedirect::toAction('facturacion');     
                        }

                    }
                    else{
                        ActiveRecord::rollbackTrans();
                        DwMessage::error('No se pudo enviar a cargar multiples facturas!');
                    }

                }
                else{
                    ActiveRecord::rollbackTrans();
                    DwMessage::error('Los detalles de la Factura no se han cargado correctamente Intente de nuevo!');
                }
            }
            else{
                ActiveRecord::rollbackTrans();
                DwMessage::error('La Factura no se ha cargado con exito!');
            }
        }
        $this->incidencias = $incidencias;
        $this->page_title = 'Cargar Facturas a la solicitud';        
    }
    /**
    *Metodo para procesar las solicitudes (Cambiar de Estatus)
    */
    public function procesar($key){
        if(!$id = DwSecurity::isValidKey($key, 'prs_incidencia', 'int')) {
            return DwRedirect::toAction('listar');
        }
        $incidencias = new Incidencia();
        $detalle_incidencia = $incidencias->getBasicoIncidencia($id);

        if(Input::hasPost('incidencia'))
        {
            $array = Input::post('incidencia');
            ActiveRecord::beginTrans();

            $detalle_incidencia->responsable_reparacion =  $array['responsable_reparacion'];
            $detalle_incidencia->observacion =  $array['observacion'];
            /* $detalle_incidencia->perdida_tn =  $array['perdida_tn'];
            $detalle_incidencia->persistencia_falla=  $array['persistencia_falla'];
            $detalle_incidencia->accion_correctiva  = $array['accion_correctiva'];*/
            $detalle_incidencia->estatus = 'P'; //procesado = P

            $result = $detalle_incidencia->update();
            $objIn = $incidencias->getBasicoIncidencia($id);

            if($result) {
                //tipo mantenimiento 1 preventivo , 2 correctivo
                $data = array('tipo_mantenimiento'=>'2', 'sucursal_id'=>$objIn->sucursal_id, 'sector_id'=>$objIn->sector_id, 'equipo_id'=>$objIn->equipo_id, 'falla_id'=>$objIn->falla_id, 'trabajo_solicitado'=>$objIn->analisis_falla,'responsable_reparacion'=>$objIn->responsable_reparacion, 'estatus'=>'2');

               if($man = Mantenimiento::setMantenimiento('create',$data)) {
                   ActiveRecord::commitTrans();
                   DwMessage::valid('La solicitud se ha rechazado correctamente!');
                   return DwRedirect::toAction('reporte_orden_trabajo/'.$man->id);
               }
               else
               {
                   ActiveRecord::rollbackTrans();
                   DwMessage::error("Problemas guardando el mantenimiento");
               }
            }
            else
            {
                ActiveRecord::rollbackTrans();
                DwMessage::error("Problemas actualizando la incidencia");
            }

        }
        $this->inci = $detalle_incidencia;
        $this->page_title = 'Procesar solicitud ( Registro de los materiales ) ';
   }


    /**
     *Metodo para asignar un tecnico a la incidencia ( solo cambiar de estatus y agregar el tecnico en cuestion )
     */
    public function asignar($key){
        if(!$id = DwSecurity::isValidKey($key, 'prs_incidencia', 'int')) {
            return DwRedirect::toAction('listar');
        }
        $incidencias = new Incidencia();
        $detalle_incidencia = $incidencias->getBasicoIncidencia($id);

        if(Input::hasPost('incidencia'))
        {
            $array = Input::post('incidencia');
            ActiveRecord::beginTrans();

            $detalle_incidencia->responsable_reparacion =  $array['responsable_reparacion'];
            $detalle_incidencia->responsable_id  =  $array['responsable_id'];
            $detalle_incidencia->observacion =  $array['observacion'];
            /* $detalle_incidencia->perdida_tn =  $array['perdida_tn'];
            $detalle_incidencia->persistencia_falla=  $array['persistencia_falla'];
            $detalle_incidencia->accion_correctiva  = $array['accion_correctiva'];*/
            $detalle_incidencia->estatus = 'A'; // Asignado = A

            $result = $detalle_incidencia->update();
            $objIn = $incidencias->getBasicoIncidencia($id);

            if($result) {
                //tipo mantenimiento 1 preventivo , 2 correctivo
                $data = array('tipo_mantenimiento'=>'2', 'sucursal_id'=>$objIn->sucursal_id, 'sector_id'=>$objIn->sector_id, 'equipo_id'=>$objIn->equipo_id, 'falla_id'=>$objIn->falla_id, 'trabajo_solicitado'=>$objIn->analisis_falla,'responsable_reparacion'=>$objIn->responsable_reparacion, 'estatus'=>'2');

                if($man = Mantenimiento::setMantenimiento('create',$data)) {
                    ActiveRecord::commitTrans();
                    DwMessage::valid('La solicitud se ha creado correctamente!');
                    return DwRedirect::toAction('listar');
                }
                else {
                    ActiveRecord::rollbackTrans();
                    DwMessage::error("Problemas guardando el mantenimiento");
                }
            }
            else
            {
                ActiveRecord::rollbackTrans();
                DwMessage::error("Problemas actualizando la incidencia");
            }
        }
        $this->page_title = 'Asignando solicitud';
    }


    /** Metodo para rechazar con motivo una solicitud **/
    public function rechazar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_incidencias', 'int')) {
            return DwRedirect::toAction('aprobacion');
        }
        $incidencias = new Incidencias();
        $sol = $incidencias->getInformacionIncidencias($id);
        if(!$sol) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('registro');
        }
        if(Input::hasPost('incidencias')) {
            $es = "E";
            //$motivo = $_POST['incidencias'];
            if(Incidencias::setIncidencias('update', Input::post('incidencias'), array('estado_solicitud'=>$es))){
                DwMessage::valid('La solicitud se ha rechazado correctamente!');
                return DwRedirect::toAction('registro');
            }       
        } 
        $this->incidencias = $sol;
        $this->page_title = 'Rechazar solicitud';        
    }
    /**
    *Metodo para aprobar las solicitudes (Cambiar de Estatus)
    */

    public function reversar_aprobacion($key){
        if(!$id = DwSecurity::isValidKey($key, 'upd_incidencias', 'int')) {
            return DwRedirect::toAction('aprobacion');
        } 
        //Mejorar esta parte  implementando algodon de seguridad
        $incidencias = new Incidencias();
        $sol = $incidencias->getInformacionIncidencias($id);
        $sol->estado_solicitud="R";
        $sol->save();
        return DwRedirect::toAction('aprobacion');
    }
    /**
     * Método para formar el reporte en pdf 
     */
    public function reporte_orden_trabajo($id) { 
        View::template(NULL);       

        $objMantenimiento = new Mantenimiento();
        if(!$mantenimiento = $objMantenimiento->getOrdenMantenimiento($id)) {
            DwMessage::get('id_no_found');
        }

        $this->nro_orden  = $mantenimiento->nro_orden;
        $this->fecha  = $mantenimiento->fecha;
        $this->tipo_mantenimiento  = $mantenimiento->tipo_mantenimiento;
        $this->responsable_reparacion  = $mantenimiento->responsable_reparacion;
        $this->trabajo_ejecutado  = $mantenimiento->trabajo_ejecutado;
        $this->trabajo_solicitado  = $mantenimiento->trabajo_solicitado;
        $this->codigo_equipo  = $mantenimiento->codigo_equipo;
        $this->nombre_equipo  = $mantenimiento->nombre_equipo;
        $this->sucursal  = $mantenimiento->sucursal;
        $this->falla  = $mantenimiento->falla;
        
    }

    /**
     * Mostrar detalles de incidencia desde el menu de listar
     * Método para formar el reporte en pdf
     */
    public function print_parcial($key) {
        View::template(NULL);
        if(!$id = DwSecurity::isValidKey($key, 'prs_incidencia', 'int')) {
            return DwRedirect::toAction('listar');
        }

        $objIncidencia = new Incidencia();
        if(!$incidencia = $objIncidencia->getInformacionIncidencia($id) ) {
            DwMessage::get('id_no_found');
        }

        $this->nro_orden  = $incidencia->nro_orden;
        $this->hora_inicio  = $incidencia->hora_inicio;
        $this->fecha  = $incidencia->fecha;
        $this->turno  = $incidencia->turno;
        $this->falla  = $incidencia->falla;
        $this->nombre_equipo  = $incidencia->nombre_equipo;
        $this->sucursal  = $incidencia->sucursal;
        $this->sector  = $incidencia->sector;
        $this->estatus  = $incidencia->estatus;
        $this->responsable  = $incidencia->nombres." ".$incidencia->apellidos;
        $this->falla  = $incidencia->falla;
        $this->nombre_equipo = $incidencia->nombre_equipo;
        $this->codigo_equipo = $incidencia->codigo_equipo;

        // obteniendo la palabra si o no
        $this->parada_sector = $this->getSiOrNo($incidencia->parada_sector);
        $this->parada_planta = $this->getSiOrNo($incidencia->parada_planta);

    }

    /**
     *  Evalua verdadero o falso y devuelve string SI o NO
     * @param $object
     * @return string
     */
    public function getSiOrNo($object)
    {
        if($object!='f'){
            $response = "SI";
        } else {
            $response = "NO";
        }
        return $response;
    }

    /*
     Método para editar solicitudes que estan registradas solamente (ya que el metodo de modificar es para afectar aquellas que fueron rechazads 
     y se van a actualizar)
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_incidencias', 'int')) {
            return DwRedirect::toAction('registro');
        }        
        
        $incidencias = new Incidencias();
        if(!$incidencias->getInformacionIncidencias($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('registro');
        }
        
        if(Input::hasPost('incidencias') && DwSecurity::isValidKey(Input::post('incidencias_id_key'), 'form_key')) {
            if(Incidencias::setIncidencias('update', Input::post('incidencias'), array('id'=>$id))){
                DwMessage::valid('La solicitud se ha actualizado correctamente!');
                return DwRedirect::toAction('registro');
            }
        } 
        $this->incidencias = $incidencias;
        $this->page_title = 'Actualizar solicitud';        
    }
    /*
        Metodo para modificar las solicitudes de modificacion
    */
    public function modificar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_incidencias', 'int')) {
            return DwRedirect::toAction('registro');
        }        
        
        $incidencias = new Incidencias();
        if(!$incidencias->getInformacionIncidencias($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('registro');
        }
        
        if(Input::hasPost('incidencias') && DwSecurity::isValidKey(Input::post('incidencias_id_key'), 'form_key')) {
            if(Incidencias::setIncidencias('update', Input::post('incidencias'), array('id'=>$id))){
                DwMessage::valid('La solicitud se ha actualizado correctamente!');
                return DwRedirect::toAction('registro');
            }
        } 
        $this->incidencias = $incidencias;
        $this->page_title = 'Actualizar solicitud';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_incidencias', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $incidencias = new Incidencias();
        if(!$incidencias->getInformacionIncidencias($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(Incidencias::setIncidencias('delete', array('id'=>$incidencias->id))) {
                DwMessage::valid('La solicitud se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Esta solicitud no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
}
