<?php
Load::models('config/parte_categoria');

class ParteCategoriaController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Categoria de las Partes';
    }
    
    /**
     * Método principal
     */
    public function index() {
        DwRedirect::toAction('listar');
    }

    /**
     * Método para buscar
     */
    public function buscar($field='sucursal', $value='none', $order='order.id.asc', $page=1) {        
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value = (Input::hasPost('field')) ? Input::post('value') : $value;
        $value = strtoupper($value);
        $categorias = new ParteCategoria();
        $fabricantes = $categorias->getAjaxFabricantes($field, $value, $order, $page);
        if(empty($fabricantes->items)) {
            DwMessage::info('No se han encontrado registros');
        }
        $this->fabricantes = $fabricantes;
        $this->order = $order;
        $this->field = $field;
        $this->value = $value;
        $this->page_title = 'Búsqueda de fabricantes del sistema';        
    }
    
    /**
     * Método para listar
     */
    public function listar($order='order.nombre.asc', $page='pag.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $categorias = new ParteCategoria();        
        $this->categorias = $categorias->getListadoCategoria($order, $page);
        $this->order = $order;        
        $this->page_title = 'Listado de Categorias de Partes';
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
    //    $empresa = Session::get('empresa', 'config');
        if(Input::hasPost('parte_categoria')) {
            if(ParteCategoria::setParteCategoria('create', Input::post('parte_categoria'))) {
                DwMessage::valid('El categorias se ha registrado correctamente!');
                return DwRedirect::toAction('listar');
            }            
        } 
        $this->page_title = 'Agregar Categoria';
    }
     /**
     * Método para obtener fabricantes
     */
    
        //accion que busca en las fabricantes y devuelve el json con los datos
    public function autocomplete() {
        View::template(NULL);
        View::select(NULL);
        if (Input::isAjax()) { //solo devolvemos los estados si se accede desde ajax 
            $busqueda = Input::post('busqueda');
            $fabricantes = Load::model('config/categorias')->obtener_fabricantes($busqueda);
            die(json_encode($fabricantes)); // solo devolvemos los datos, sin template ni vista
            //json_encode nos devolverá el array en formato json ["aragua","carabobo","..."]
        }
    }        
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = DwSecurity::isValidKey($key, 'upd_fabricante', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $categorias = new ParteCategoria();
        if(!$categorias->getInformacionFabricante($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }
        
        if(Input::hasPost('categorias') && DwSecurity::isValidKey(Input::post('fabricante_id_key'), 'form_key')) {
            if(ParteCategoria::setFabricante('update', Input::post('categorias'))){
                DwMessage::valid('La categorias se ha actualizado correctamente!');
                return DwRedirect::toAction('listar');
            }
        } 
        //$this->ciudades = Load::model('params/ciudad')->getCiudadesToJson();
        $this->categorias = $categorias;
        $this->page_title = 'Actualizar categorias';        
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = DwSecurity::isValidKey($key, 'del_fabricante', 'int')) {
            return DwRedirect::toAction('listar');
        }        
        
        $categorias = new ParteCategoria();
        if(!$categorias->getInformacionFabricante($id)) {            
            DwMessage::get('id_no_found');
            return DwRedirect::toAction('listar');
        }                
        try {
            if(ParteCategoria::setFabricante('delete', array('id'=>$categorias->id))) {
                DwMessage::valid('La categorias se ha eliminado correctamente!');
            }
        } catch(KumbiaException $e) {
            DwMessage::error('Este categorias no se puede eliminar porque se encuentra relacionada con otro registro.');
        }
        
        return DwRedirect::toAction('listar');
    }
}
