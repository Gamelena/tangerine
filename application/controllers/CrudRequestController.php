<?php
/**
 * Controlador de Datos
 *
 * Controlador principal que interactúa con capa de datos para operaciones CRUD, 
 * recibe parametros por $_REQUEST y devuelve datos y/o mensajes en formatos como json, xml o excel
 *
 * @package Controllers
 * @version $Id:$
 * @since 0.1
 */

class CrudRequestController extends Zend_Controller_Action
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
     * @var array
     */
    private $_response_content=array();
    
    /**
     * Modelo sobre el cual se trabajará.
     * @var Zwei_Db_Table
     */
    private $_model;
    
    /**
     * Archivo XML para obtener atributos adicionales y validar permisos
     * @var Zwei_Admin_Xml
     */
    private $_xml;

    public function init()
    {
        $this->_form = new Zwei_Utils_Form();
        
        if (Zwei_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_user_info = Zend_Auth::getInstance()->getStorage()->read();
        } else if (isset($this->_form->format) && $this->_form->format == 'json') {
            $this->_helper->ContextSwitch
            ->setAutoJsonSerialization(false)
            ->addActionContext('index', 'json')
            ->initContext();
            $data = array( 'id'=>'0',
                               'state'=>'AUTH_FAILED',
                               'message'=>'Su sesión a expirado, por favor vuelva a ingresar.',
                               'todo'=>'goToLogin');
            $this->view->content = Zend_Json::encode($data);
            $this->render('admin'); 
        } else {
            $this->_redirect('admin/login');
        }
        $this->_helper->layout()->disableLayout();
        
        if (isset($this->_form->p)) {
            $file = Zwei_Admin_Xml::getFullPath($this->_form->p);
            $this->_xml = new Zwei_Admin_Xml($file, 0, 1);
        }
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
        
        if (isset($this->_form->format) && $this->_form->format == 'json') {
            $this->_helper->ContextSwitch
            ->setAutoJsonSerialization(false)
            ->addActionContext('index', 'json')
            ->initContext();
        }
        
        $classModel = $this->getRequest()->getParam('model');
        
        if (class_exists($classModel)) {
            /**
             * 
             * @var Zwei_Db_Table
             */
            $this->_model = new $classModel();
            $a = $this->_model->getAdapter();
            $this->view->collection = array();
            
            if (isset($this->_form->action) && Zwei_Admin_Auth::getInstance()->hasIdentity()) {
                $data = array();
                $where = array();
                
                foreach ($_FILES as $i => $v) {
                    $tmpTargets = array_keys($v['name']);
                    $target = $tmpTargets[0];
                    $path = !empty($this->_form->pathdata[$target]) ? 
                        $this->_form->pathdata[$target] : 
                        ROOT_DIR . '/public/upfiles';
                    
                    $element = $this->_xml->getElements("@target='$target'");

                    $infoFiles = $this->_form->upload($i, $path);
                    if ($infoFiles) {
                        $j = 0;
                        foreach ($infoFiles as $k => $l) {
                            $this->_form->data[$k] = $l['filename'];
                            if ($element[$j]->existsChildren('thumb')) {
                                foreach ($element[$j]->thumb as $t) {
                                    try {
                                        if (preg_match("/^\{ROOT_DIR\}(.*)$/", $t->getAttribute('path'), $matches)) {
                                            $thumbPath = ROOT_DIR . $matches[1];
                                        } else if (preg_match("/^\{APPLICATION_PATH\}(.*)$/", $t->getAttribute('path'), $matches)) {
                                            $thumbPath = APPLICATION_PATH . $matches[1];
                                        } else {
                                            $thumbPath = $t->getAttribute('path');
                                        }

                                        //[TODO] revisar configuracion de Autoloader en Bootstrap para no usar require_once
                                        require_once ADMPORTAL_APPLICATION_PATH .'/../library/PhpThumb/ThumbLib.inc.php';
                                        
                                        $thumb = PhpThumbFactory::create($path."/".$l['filename']);
                                        $thumb->resize($t->getAttribute('width'), $t->getAttribute('height'));
                                        $thumb->save($thumbPath."/".$l['filename']);
                                    } catch (Exception $e) {
                                        Debug::write($e->getMessage() . '-' . $e->getCode() . $e->getTraceAsString());
                                    }
                                }
                            }
                            $j++;
                        }
                    } else {
                        Debug::write("error al subir archivo a $path");
                    }
                }
                
                if (isset($this->_form->deletedata )) {
                    foreach ($this->_form->deletedata as $i => $v) {
                        if (!@unlink($path. $v)) {
                            Debug::write("no se pudo borrar " . $this->_form->pathdata[$i] . $v);
                        }
                        $this->_form->data[$i] = '';
                    }
                }
                
                
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
                    foreach ($this->_form->primary as $i => $v) {
                        $where[] = $a->quoteInto($a->quoteIdentifier($i)." = ?", $v);
                    }
                    if (count($where) == 1) $where = $where[0];
                    
                    $response = $this->_model->delete($where);
                    if ($response) {
                        $this->_response_content['state'] = 'DELETE_OK';
                    } else {
                        $this->_response_content['state'] = 'DELETE_FAIL';
                    }
                    //Zwei_Utils_Debug::write($response);
                } else if ($this->_form->action == 'edit') {
                    foreach ($this->_form->primary as $i => $v) {
                        $where[] = $a->quoteInto($a->quoteIdentifier($i)." = ?", $v);
                    }
                    if (count($where) == 1) $where = $where[0];
                    
                    foreach ($this->_form->data as $i=>$v) {
                        $data[$i] = $v;
                    }
                    
                    try {
                        $response = $this->_model->update($data, $where);
                        if ($response) {
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
                
                
            } else {
                //[TODO] validar permisos para listar
            }

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
            if ($this->_model->overloadDataList($data)) {
                $data = $this->_model->overloadDataList($data);
                $numRows = count($data);
            }    

            //si ?format=excel exportamos el rowset a excel
            if (@$this->_form->format == 'excel') {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();
                
                $table = new Zwei_Utils_Table();
                
                if ($this->_form->excel_formatter != 'csv') {
                    header('Content-type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment; filename={$this->_form->model}.xls");
                    header("Pragma: no-cache");
                    header("Expires: 0");
                    
                    if (isset($this->_form->p)) {
                        $content = $table->rowsetToHtml($data, $this->_form->p);
                    } else {
                        $content = $table->rowsetToHtml($data);
                    }
                    echo $content;
                } else {
                    header("Pragma: public");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octet-stream");
                    header("Content-Type: application/download");
                    header("Content-Disposition: attachment; filename={$this->_form->model}.csv");
                    header('Content-Encoding: UTF-8');
                    header('Content-type: text/csv; charset=UCS-2LE');
                    
                                    
                    if (isset($this->_form->p)) {
                        $content = $table->rowsetToCsv($data, $this->_form->p);
                    } else {
                        $content = $table->rowsetToCsv($data);
                    }
                    echo  chr(255) . chr(254) . mb_convert_encoding($content, 'UCS-2LE', 'UTF-8');
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
                $this->getResponse()
                ->setHeader('Content-Type', 'text/html'); //internet explorer needs this
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
                $this->getResponse()
                ->setHeader('Content-Type', 'text/html');
                
            }
            $this->view->content = $content;

        }
    }//public function indexAction()

    public function multiUpdateAction()
    {
        $r = $this->getRequest();
        $classModel = $r->getParam('model');
        $this->_model = new $classModel();

        $updated = 0;
        $failed = 0;
        $message = '';
        
        foreach ($_FILES as $i => $v) {
            $tmpTargets = array_keys($v['name']);
            $target = $tmpTargets[0];
            $path = $this->_form->pathdata[$target];
            $infoFiles = $this->_form->upload($i, $path);
            if ($infoFiles) {
                foreach ($infoFiles as $i => $v) {
                    $this->_form->data[$i] = $v['filename'];
                }
            } else {
                Debug::write("error al subir archivo a $path");
            }
        }
        
        if (isset($this->_form->deletedata )) {
            foreach ($this->_form->deletedata as $i => $v) {
                if (!unlink($this->_form->pathdata[$i]. $v)) {
                    Debug::write("no se pudo borrar " . ROOT_DIR . '/upfiles/'. $v);
                }
                $this->_form->data[$i] = '';
            }
        }
        
        
        foreach ($this->_form->data as $i => $v) {
            $i = str_replace("'", '', $i);
            $where = $this->_model->getAdapter()->quoteInto('id = ?', $i);
            $data = array('value' => $v);
            if ($this->_model->update($data, $where)) $updated++;
        }
        
        $message .= "Actualizados $updated registros";
        
        
        $data = array( 'id' => '0',
                'state' => 0,
                'message' => $message,
                'todo' => '',
                'more' => '');
        
        $this->view->content = Zend_Json::encode($data);
    }
}
