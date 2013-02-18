<?php
/**
 * Controlador de Datos
 *
 * Controlador principal que interactúa con capa de datos para operaciones CRUD, 
 * recibe parametros por $_REQUEST y devuelve datos y/o mensajes en formatos como json u xls
 *
 * @package Controllers
 * @version $Id:$
 * @since 0.1
 */

class ObjectsController extends Zend_Controller_Action
{
    /**
     * Información de usuario Instancia Singleton.
     * @var Zend_Auth
     */
    private $_user_info;
    /**
     * Instancia de $_REQUEST, en este caso preferible a Zend_Controller_Request ya que este no permite manejar un <pre>$_REQUEST['action']</pre> al ser el índice palabra reservada.
     * @var Zwei_Utils_Form
     */
    private $_form;
    /**
     * Arreglo que será parte de la respuesta en json, Dojo data store, u otro formato a definir.
     * @var array()
     */
    private $_response_content=array();
    
    /**
     * Modelo sobre el cual se trabajará.
     * @var Zwei_Db_Table
     */
    private $_model;

    public function init()
    {
        $this->_form = new Zwei_Utils_Form();
        if (Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
        } else if ($this->_form->format == 'json') {
            $this->_helper->ContextSwitch
            ->setAutoJsonSerialization(false)
            ->addActionContext('index', 'json')
            ->initContext();
            $data = array( 'id'=>'0',
                               'state'=>'AUTH_FAILED',
                               'message'=>'Su sesión a expirado, por favor vuelva a ingresar.',
                               'todo'=>'goToLogin');//declarar dojo.admin.js y llamarla en TableDojo
            $this->view->content = Zend_Json::encode($data);
            $this->render('index'); 
        } else {
            $this->_redirect('index/login');
        }
        $this->_helper->layout()->disableLayout();
    }
    
    /**
     * Retorna un json a partir de un objeto modelo,
     * enviar nombre de clase modelo separada por "_" y sin sufijo "Model",
     * ej: enviar "model=solicitud_th" en lugar de "model=SolicitudThModel"
     * @return excel|json
     */
    
    public function indexAction()
    {
        
        /**
         * [TODO] Validar segun perfil de usuario autorizado a obtener estos datos
         */
        
        if ($this->_form->format == 'json') {
            $this->_helper->ContextSwitch
            ->setAutoJsonSerialization(false)
            ->addActionContext('index', 'json')
            ->initContext();
        }
        
        //enviar nombre de clase modelo separada por "_" y sin sufijo "Model",
        //ej: enviar solicitud_th en lugar de SolicitudThModel"
        
        $classModel = Zwei_Utils_String::toClassWord($this->_form->model)."Model";
        
        if (class_exists($classModel)) {
            /**
             * 
             * @var Zwei_Db_Table
             */    
            $this->_model = new $classModel();
            $this->view->collection = array();
            
            if (isset($this->_form->action) && Zwei_Admin_Auth::getInstance()->hasIdentity()) {
                $data = array();
                $this->_model->getAdapter()->getProfiler()->setEnabled(true);
                $id = $this->_model->getPrimary();
                if ($id === false) $id = "id";
                
                if ($this->_form->action == 'add') {
                     
                    foreach ($this->_form->data as $i=>$v) {
                        $data[$i] = $v;
                    }
                    
                    try {
                        $response = $this->_model->insert($data);
                        if ($response) {
                            $this->_response_content['state'] = 'ADD_OK';
                        } else {
                            $this->_response_content['state'] = 'ADD_FAIL';
                        }
                    } catch (Zend_Db_Exception $e) {
                        $this->_response_content['state'] = 'ADD_FAIL';
                        Zwei_Utils_Debug::write("Zend_Db_Exception:{$e->getMessage()},Code:{$e->getCode()}");
                    }
                    
                } elseif ($this->_form->action == 'delete') {
                     
                    if (!empty($this->_form->id) || $this->_form->id === "0") {
                        $where = $this->_model->getAdapter()->quoteInto("$id = ?", $this->_form->id);
                        $response = $this->_model->delete($where);
                        if ($response) {
                            $this->_response_content['state'] = 'DELETE_OK';
                        } else {
                            $this->_response_content['state'] = 'DELETE_FAIL';
                        }
                    } else {
                        $this->_response_content['state'] = 'DELETE_FAIL';
                    }
                    //Zwei_Utils_Debug::write($response);
                } else if ($this->_form->action == 'edit') {
                    
                    foreach ($this->_form->data as $i=>$v) {
                        if ($i == $id) { // si es pk, tratar como pk
                            $this->_form->$id = $v;
                        } 
                        $data[$i] = $v;
                        
                    }
                    
                    //en caso de tener multiples PK [FIXME] capturar nombres de campos para que funcione
                    if (isset($this->_form->id)) {
                        if (is_array($this->_form->id)) {
                           $where = array();    
                           foreach ($this->_form->id as $i => $v) {
                                $where[] = $this->_model->getAdapter()->quoteInto("$id = ?", $v);
                           }
                        } else {
                           $where = $this->_model->getAdapter()->quoteInto("id = ?", $this->_form->id);
                        }   
                    } else {
                       if (is_array($this->_form->$id)) {
                           $where = array();    
                           foreach ($this->_form->$id as $i => $v) {
                                $where[] = $this->_model->getAdapter()->quoteInto("$id = ?", $v);
                           }
                       } else {
                           $where = $this->_model->getAdapter()->quoteInto("$id = ?", $this->_form->$id);
                       } 
                    }
                    
                    
                    try {
                        $response = $this->_model->update($data, $where);
                        if ($response) {
                            Debug::write($response);
                            $this->_response_content['state'] = 'UPDATE_OK';
                        } else {
                            $this->_response_content['state'] = 'UPDATE_FAIL';
                        }
                    } catch (Zend_Db_Exception $e) {
                        $this->_response_content['state'] = 'UPDATE_FAIL';
                        Zwei_Utils_Debug::write("Zend_Db_Exception:{$e->getMessage()},Code:{$e->getCode()}|model:$classModel|".$e->getTraceAsString());
                    }
                    //Zwei_Utils_Debug::write($response);
                }
                $this->_response_content['todo'] = $this->_model->getAjaxTodo();
                $this->_response_content['more'] = $this->_model->getMore();

            }//if (isset($this->_form->action))

            $oDbObject = new Zwei_Db_Object($this->_form);
            $oSelect = $oDbObject->select();

            

            if (is_a($oSelect, "Zend_Db_Table_Select") || is_a($oSelect, "Zend_Db_Select")) {
                $adapter = $this->_model->getZwAdapter();

                if (isset($adapter) && !empty($adapter)) $this->_model->setAdapter($adapter);
                
                $data = $this->_model->fetchAll($oSelect);
                $paginator = Zend_Paginator::factory($oSelect);
                $numRows = $paginator->getTotalItemCount();
            } else {
                $data = $oDbObject->select();
            }    
            $i = 0;
               
            //Si es necesario se añaden columnas o filas manualmente que no vengan del select original
            if ($this->_model->overloadData($data) !== false) {
                $data = $this->_model->overloadData($data);
                $numRows = count($data);
            }    

            //si ?format=excel exportamos el rowset a excel
            if (@$this->_form->format == 'excel') {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();
                
                $Table = new Zwei_Utils_Table();
                
                
                if ($numRows > 5000) {
                    //Si el numero es mayor a 5000 filas, exportamos "a la antigua" ya que PhpExcel puede agotar el limite de RAM
                    //Tomar en cuenta que el numero mayor de registros a soportar es 20000 12/11/2012
                    header('Content-type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment; filename={$this->_form->model}.xls");
                    header("Pragma: no-cache");
                    header("Expires: 0");
                    
                    if (isset($this->_form->p)) {
                        $content = $Table->rowsetToHtml($data, $this->_form->p);
                    } else {
                        $content = $Table->rowsetToHtml($data);
                    }
                    echo $content;
                } else {
                  
                
                    if (isset($this->_form->p)) {
                        $content = $Table->rowsetToExcel($data, $this->_form->p);
                    } else {
                        $content = $Table->rowsetToExcel($data);
                    }
                }    
                    

                exit();
                
            } else if (count($this->_response_content) > 0) {
                $this->_response_content['message'] = $this->_model->getMessage();
                $data = array( 'id' => '0',
                               'state' => $this->_response_content['state'],
                               'message' => $this->_response_content['message'],
                               'todo' => $this->_response_content['todo'],
                               'more' => $this->_response_content['more']);
                $content = Zend_Json::encode($data);
            } else {

                foreach ($data as $rowArray) {
                    $collection[$i]=array();
                    foreach ($rowArray as $column => $value) {
                        if (!is_array($value)) {
                            $collection[$i][$column] = ((PHP_VERSION_ID >= 50400)) ? html_entity_decode($value) :  utf8_encode(html_entity_decode($value));
                        } else {
                            foreach ($value as $column2 => $value2) {
                                $collection[$i][$column][$column2] = (PHP_VERSION_ID >= 50400) ? html_entity_decode($value2) : utf8_encode(html_entity_decode($value2));
                            }
                        }
                    }
                    $i++;
                }
                //Zwei_Utils_Debug::write($str_collection);
                $id = $this->_model->getPrimary();
                
                if ($id !== false && (!is_array($id) || count($id) == 1)) {
                    $id = $this->_model->getPrimary();
                    $content = new Zend_Dojo_Data($id[1], @$collection);
                } else {
                    /*
                     * En caso de que no exista ninguna PK simple, inventamos un ID aca para que funcione el datastore
                     * (SOLO PARA LISTAR, NO USAR ESTA ID PARA EDITAR, MODIFICAR O ELIMINAR) 
                     * [TODO] aunque un dojo datastore no lo permita nativamente se debe emular PK multiple de ser necesario, ¿primary separada por ';'?
                     */
                    if (!isset($collection[0]['id'])) {
                        for($j=0;$j<$i;$j++) {
                            $collection[$j]['id']=$j;
                        }
                    }
                    $content = new Zend_Dojo_Data('id', @$collection);
                }

                /**
                 * Si esta especificado $this->_model->_label se especifica el ÍNDICE del atributo label, standard dojo store,
                 * Si esta especificado $this->_model->_labels se especifica el ARRAY de labels, NO standard dojo store pero necesario para algunos casos.
                 */
                if ($this->_model->getLabel()) {
                    $content->setLabel($this->_model->getLabel());
                } 
                
                if ($this->_model->getLabels()) {
                    $content->setMetadata(array("labels" => $this->_model->getLabels()));
                }
                
                if ($this->_model->getTitle()) {
                    $content->setMetadata(array("title" => $this->_model->getTitle()));
                }
                
                if (isset($numRows)) $content->setMetadata('numRows', $numRows);
                
            }
            $this->view->content = $content;

        }//if (class_exists($ClassModel))

    }//public function indexAction()

    public function multiUpdateAction()
    {
        $classModel = Zwei_Utils_String::toClassWord($this->_form->model);
        $this->_model = new $classModel();


        foreach ($this->_form->id as $i=>$v) {
            $value = isset($this->_form->value[$i]) ? $this->_form->value[$i] : "";
            $where = $this->_model->getAdapter()->quoteInto('id = ?', $v);
            $data = array('value'=>$value);
            $this->_model->update($data, $where);
        }
    }
}
