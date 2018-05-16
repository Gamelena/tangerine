<?php
/**
 * Controlador de Datos.
 * Interactúa con capa de datos para operaciones CRUD via REST (no RESTFUL),
 * recibe parametros por $_REQUEST y devuelve datos y/o mensajes en formatos como json, xml o excel.
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
    private $_userInfo;
    /**
     * Instancia de $_REQUEST, en este caso preferible a Zend_Controller_Request ya que este no permite manejar un <pre>$_REQUEST['action']</pre> al ser el índice reservado.
     * @var Gamelena_Utils_Form
     */
    private $_form;
    /**
     * Arreglo que será parte de la respuesta en json, Dojo data store, u otro formato a definir.
     * @var array
     */
    private $_responseContent = array();
    
    /**
     * Modelo sobre el cual se trabajará.
     * @var Gamelena_Db_Table
     */
    private $_model;
    
    /**
     * Archivo XML para obtener atributos adicionales y validar permisos
     * @var Gamelena_Admin_Xml
     */
    private $_xml;
    
    /**
     * Lista de control de accesos
     * @var Gamelena_Admin_Acl
     */
    private $_acl;
    
    public function init()
    {
        $this->_form = new Gamelena_Utils_Form();
        
        if (Gamelena_Admin_Auth::getInstance()->hasIdentity()) {
            $this->_userInfo = Zend_Auth::getInstance()->getStorage()->read();
        } else if (isset($this->_form->format) && $this->_form->format == 'json') {
            $this->_helper->ContextSwitch->setAutoJsonSerialization(false)->addActionContext('index', 'json')->initContext();
            $data                = array(
                'id' => '0',
                'state' => 'AUTH_FAILED',
                'type' => 'error',
                'message' => 'Su sesión ha expirado, por favor vuelva a ingresar.',
                'todo' => 'goToLogin'
            );
            $this->view->content = Zend_Json::encode($data);
            $this->render('index');
        } else {
            $this->_redirect('admin/login');
        }
        $this->_helper->layout()->disableLayout();
        
        if (isset($this->_form->p)) {
            $file       = Gamelena_Admin_Xml::getFullPath($this->_form->p);
            $this->_xml = new Gamelena_Admin_Xml($file, 0, 1);
        }
        //@todo migrar a Bootstrap
        if (!defined('DEFAULT_CHARSET')) {
            define('DEFAULT_CHARSET', ini_get('default_charset'));
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
        if (isset($this->_form->format) && $this->_form->format == 'json') {
            $this->_helper->ContextSwitch->setAutoJsonSerialization(false)->addActionContext('index', 'json')->initContext();
        }
        
        $classModel = $this->getRequest()->getParam('model');
        
        if (class_exists($classModel)) {
            $data                   = array();
            try {
                $this->_model           = new $classModel();
            } catch (Zend_Application_Resource_Exception $e) {
                throw new Zend_Application_Resource_Exception($classModel . ": " . $e->getMessage(), $e->getCode());
            }
            
            if (!$this->_model instanceof Zend_Db_Table_Abstract && !$this->_model instanceof Gamelena_Admin_ModelInterface) {
                throw new Gamelena_Exception("$classModel no es una instancia de Zend_Db_Table_Abstract ni implementa Gamelena_Admin_ModelInterface");
            }
            
            
            $this->view->collection = array();
            
            //Es posible que $this->_model NO sea un modelo Gamelena_Db_Table y sea una implementación de Gamelena_Admin_ModelInterface
            //en cuyo caso no existe el método 'getValidateXmlAcl'
            $validateXml = method_exists($this->_model, 'getValidateXmlAcl')
                ? $this->_model->getValidateXmlAcl()
                : array('EDIT' => false, 'ADD' => false, 'DELETE' => false, 'LIST' => false);
                        
            if (Gamelena_Admin_Auth::getInstance()->hasIdentity()) {
                if (isset($this->_form->action)) {
                    $validatedCUD = true;
                    $aclAction = strtoupper($this->_form->action);
                    if ($validateXml[$aclAction]) {
                        $validatedCUD = $this->_model->validateXmlAcl($this->_form, $this->_xml);
                        if (!$validatedCUD) {
                            $this->_responseContent['state'] = $aclAction . "_FAIL";
                            $this->_responseContent['message'] = 'Acceso denegado.';
                            $this->_responseContent['type'] = 'error';
                            $this->_responseContent['more'] = null;
                            $this->_responseContent['todo'] = null;
                        }
                    }
                    if ($validatedCUD) {
                        $where = array();
                        
                        foreach ($_FILES as $i => $file) {
                            $tmpTargets = array_keys($file['name']);
                            $target     = $tmpTargets[0];
                            $path       = !empty($this->_form->pathdata[$target]) ? $this->_form->pathdata[$target] : ROOT_DIR . '/public/upfiles';
                            
                            $element = $this->_xml->getElements("@target='$target'");
                            
                            $infoFiles = $this->_form->upload($i, $path);
                            if ($infoFiles) {
                                $j = 0;
                                foreach ($infoFiles as $k => $uploaded) {
                                    $this->_form->data[$k] = $uploaded['filename'];
                                    //Crear miniaturas de imagen si corresponde
                                    if ($element[$j]->existsChildren('thumb')) {
                                        foreach ($element[$j]->thumb as $thumb) {
                                            $this->createThumb($thumb, $uploaded, $path);
                                        }
                                    }
                                    $j++;
                                }
                            } else {
                                Console::error(array("error al subir archivo a $path", $file));
                            }
                        }
                        
                        if (isset($this->_form->deletedata)) {
                            foreach ($this->_form->deletedata as $i => $file) {
                                if (!@unlink($path . $file)) {
                                    Console::error("no se pudo borrar " . $this->_form->pathdata[$i] . $file);
                                }
                                $this->_form->data[$i] = '';
                            }
                        }
                        
                        
                        if ($this->_form->action == 'add') {
                            foreach ($this->_form->data as $i => $myData) {
                                $data[$i] = $myData;
                            }
                            
                            try {
                                $response = $this->_model->insert($data);
                                if ($response) {
                                    $this->_responseContent['state'] = 'ADD_OK';
                                    $this->_responseContent['type'] = 'message';
                                } else {
                                    $this->_responseContent['state'] = 'ADD_FAIL';
                                    $this->_responseContent['type'] = 'error';
                                }
                            } catch (Zend_Db_Exception $e) {
                                $this->_responseContent['state'] = 'ADD_FAIL';
                                $this->_responseContent['type'] = 'error';
                                Console::error(array("Zend_Db_Exception:{$e->getMessage()},Code:{$e->getCode()}", $data));
                            }
                        } else if ($this->_form->action == 'delete') {
                            if (isset($this->_form->primary)) {
                                foreach ($this->_form->primary as $i => $primary) {
                                    $where[] = $this->_model->getAdapter()->quoteInto($this->_model->getAdapter()->quoteIdentifier($i) . " = ?", $primary);
                                }
                                if (count($where) == 1) {
                                    $where = $where[0];
                                }
                                $response = $this->_model->delete($where);
                            } else {
                                Console::error(array($this->_model->info('name'), "Se intento borrar sin parametros"));
                                $response = false;
                            }
                            
                            if ($response) {
                                $this->_responseContent['state'] = 'DELETE_OK';
                                $this->_responseContent['type'] = 'message';
                            } else {
                                $this->_responseContent['state'] = 'DELETE_FAIL';
                                $this->_responseContent['type'] = 'error';
                            }
                        } else if ($this->_form->action == 'edit') {
                            if (isset($this->_form->primary)) {
                                foreach ($this->_form->primary as $i => $primary) {
                                    $where[] = $this->_model->getAdapter()->quoteInto($this->_model->getAdapter()->quoteIdentifier($i) . " = ?", $primary);
                                }
                                if (count($where) == 1) {
                                    $where = $where[0];
                                }
                                foreach ($this->_form->data as $i => $myData) {
                                    $data[$i] = $myData;
                                }
                                
                                try {
                                    $response = $this->_model->update($data, $where);
                                    if ($response) {
                                        $this->_responseContent['state'] = 'UPDATE_OK';
                                        $this->_responseContent['type'] = 'message';
                                    } else {
                                        $this->_responseContent['state'] = 'UPDATE_FAIL';
                                        $this->_responseContent['type'] = 'error';
                                    }
                                } catch (Zend_Db_Exception $e) {
                                    $this->_responseContent['state'] = 'UPDATE_FAIL';
                                    $this->_responseContent['type'] = 'error';
                                    Console::error("Zend_Db_Exception:{$e->getMessage()},Code:{$e->getCode()}|model:$classModel|" . $e->getTraceAsString());
                                }
                            } else {
                                Console::error(array($this->_model->info('name'), "Se intento actualizar sin parametros"));
                                $this->_responseContent['state'] = 'UPDATE_FAIL';
                                $this->_responseContent['type'] = 'error';
                            }
                        }
                        
                        $this->_responseContent['todo'] = $this->_model->getAjaxTodo();
                        $this->_responseContent['more'] = $this->_model->getMore();
                    }
                } else {
                    $validatedR = true;
                    if ($validateXml['LIST']) {
                        $validatedR = $this->_model->validateXmlAcl($this->_form, $this->_xml);
                    }
               
                    if ($validatedR) {
                        $oDbObject = new Gamelena_Db_Object($this->_form);
                        $oSelect   = $oDbObject->select();
                        
                        if ($oSelect instanceof Zend_Db_Select) {
                            $adapter = $this->_model->getZwAdapter();
                            
                            if (isset($adapter) && !empty($adapter)) {
                                $this->_model->setAdapter($adapter);
                            }
                            
                            try {
                                $data      = $this->_model->fetchAll($oSelect);
                            } catch (Zend_Db_Exception $e) {
                                throw new Zend_Db_Exception("$classModel::select() {$e->getMessage()}", $e->getCode());
                            }
                            
                            if ($this->_model->count() === false) {
                                $paginator = Zend_Paginator::factory($oSelect);
                                $numRows   = $paginator->getTotalItemCount();
                            } else {
                                $numRows = $this->_model->count();
                            }
                        } else {
                            $data = $this->_model->fetchAll();
                        }
                        $i = 0;
                        
                        //Si es necesario se añaden columnas o filas manualmente que no vengan del select original
                        if (method_exists($this->_model, 'overloadDataList') && $this->_model->overloadDataList($data) !== false) {
                            $data      = $this->_model->overloadDataList($data);
                            if (!method_exists($this->_model, 'count') || $this->_model->count() === false) {
                                $countData = count($data);
                                if ($numRows < $countData) {
                                    $numRows = $countData;
                                }
                            }
                        }
                        
                        //si ?format=excel exportamos el rowset a excel
                        if (isset($this->_form->format) && $this->_form->format == 'excel') {
                            $this->_helper->layout->disableLayout();
                            $this->_helper->viewRenderer->setNoRender();
                            
                            $table = new Gamelena_Utils_Table();
                            
                            if (in_array($this->_form->excel_formatter, array('Excel5', 'Excel2007'))) {
                                if (isset($this->_form->p)) {
                                    $content = $table->rowsetToExcel($data, $this->_form->p);
                                } else {
                                    $content = $table->rowsetToExcel($data);
                                }
                                $this->view->content = $content;
                            } else if ($this->_form->excel_formatter == 'csv') {
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
                                $this->view->content = chr(255) . chr(254) . mb_convert_encoding($content, 'UCS-2LE', 'UTF-8');//hack para prevenir advertencias de MS Excel
                            } else {
                                header('Content-type: application/vnd.ms-excel');
                                header("Content-Disposition: attachment; filename={$this->_form->model}.xls");
                                header("Pragma: no-cache");
                                header("Expires: 0");
                                
                                if (isset($this->_form->p)) {
                                    $content = $table->rowsetToHtml($data, $this->_form->p);
                                } else {
                                    $content = $table->rowsetToHtml($data);
                                }
                                $this->view->content = $content;
                            }
                            
                            $this->render();
                        }
                    } else {
                        $data = array();
                    }
                }
            } else {
                $data = array();
            }
            
            if (count($this->_responseContent) > 0) {
                if ($this->_model->getMessage() || !isset($this->_responseContent['message'])) {
                    $this->_responseContent['message'] = $this->_model->getMessage();
                }
                $data                               = array(
                    'id' => '0',
                    'state' => $this->_responseContent['state'],
                    'message' => $this->_responseContent['message'],
                    'type' => $this->_responseContent['type'],
                    'todo' => $this->_responseContent['todo'],
                    'more' => $this->_responseContent['more']
                );
                $content                            = Zend_Json::encode($data);
                $this->getResponse()->setHeader('Content-Type', 'text/html'); //internet explorer needs this
            } else {
                $i          = 0;
                $collection = array();
                foreach ($data as $rowArray) {
                    $collection[$i] = array();
                    foreach ($rowArray as $column => $value) {
                        if (!is_array($value)) {
                            $collection[$i][$column] = (strtoupper(DEFAULT_CHARSET) == 'UTF-8') ? html_entity_decode($value, null, 'UTF-8') : utf8_encode(html_entity_decode($value));
                        } else {
                            foreach ($value as $column2 => $value2) {
                                if (!is_array($value)) {
                                    $collection[$i][$column][$column2] = (strtoupper(DEFAULT_CHARSET) == 'UTF-8') ? html_entity_decode($value2, null, 'UTF-8') : utf8_encode(html_entity_decode($value2));
                                } else {
                                    $collection[$i][$column][$column2] = $value2;
                                }
                            }
                        }
                    }
                    $i++;
                }
                
                $id = method_exists($this->_model, 'info') ? $this->_model->info(Zend_Db_Table_Abstract::PRIMARY) : 'id';
                
                if ((!is_array($id) || count($id) == 1)) {
                    if (is_array($id)) {
                        $arrayValues = array_values($id);
                        $id          = $arrayValues[0];
                    }
                    $content = new Zend_Dojo_Data($id, @$collection);
                } else {
                    /**
                     * En caso de que no exista ninguna PK simple, inventamos un ID aca para que funcione dojo.data.ItemFileStore
                     */
                    if (!isset($collection[0]['AdmFakeId'])) {
                        for ($j = 0; $j < $i; $j++) {
                            $collection[$j]['AdmFakeId'] = $j;
                        }
                    }
                    
                    $content = new Zend_Dojo_Data('AdmFakeId', @$collection);
                }
                
                /**
                 * Si esta especificado $this->_model->_label se especifica el ÍNDICE del atributo label, standard dojo store,
                 * Si esta especificado $this->_model->_labels se especifica el ARRAY de labels, NO standard dojo store pero necesario para algunos casos.
                 */
                if (method_exists($this->_model, 'getLabel') && $this->_model->getLabel()) {
                    $content->setLabel($this->_model->getLabel());
                }
                
                if (method_exists($this->_model, 'getLabels') && $this->_model->getLabels()) {
                    $content->setMetadata(
                        array(
                        "labels" => $this->_model->getLabels()
                        )
                    );
                }
                
                if (method_exists($this->_model, 'getTitle') && $this->_model->getTitle()) {
                    $content->setMetadata(
                        array(
                        "title" => $this->_model->getTitle()
                        )
                    );
                }
                
                if (method_exists($this->_model, 'getMore') && $this->_model->getMore()) {
                    $content->setMetadata(
                        array(
                        "more" => $this->_model->getMore()
                        )
                    );
                }
                
                if (isset($numRows)) {
                    $content->setMetadata('numRows', $numRows);
                }
                
                $this->getResponse()->setHeader('Content-Type', 'text/html');
                
            }
            $this->view->content = $content;
            
        }
    }
    
    public function multiUpdateAction()
    {
        $r            = $this->getRequest();
        $classModel   = $r->getParam('model');
        $this->_model = new $classModel();
        
        $updated = 0;
        $failed  = 0;
        $message = '';
        
        $count = 0;
        foreach ($_FILES as $data => $v) {
            $tmpTargets = array_keys($v['name']);
            $target     = $tmpTargets[$count];
            $path       = $this->_form->pathdata[$target];
            $infoFiles  = $this->_form->upload($data, $path);
            
            if ($infoFiles) {
                foreach ($infoFiles as $id => $v) {
                    $id          = str_replace("'", '', $id);
                    $row         = $this->_model->find($id)->current();
                    $xmlChildren = new Gamelena_Admin_Xml('<element>' . html_entity_decode($row->xml_children) . '</element>');
                    
                    foreach ($xmlChildren->thumb as $child) {
                        $this->createThumb($child, $v, $path);
                    }
                    //Se agrega nombre de archivo subido a array de actualizacion de datos
                    $this->_form->data[$id] = $v['filename'];
                }
            } else if ($v['size'] > 0) {
                Console::error("error al subir archivo a $path");
            }
            $count++;
        }
        
        
        if (isset($this->_form->deletedata)) {
            foreach ($this->_form->deletedata as $id => $value) {
                if (!unlink($this->_form->pathdata[$id] . $value)) {
                    Console::error("no se pudo borrar " . ROOT_DIR . '/upfiles/' . $value);
                }
                $this->_form->data[$id] = '';
            }
        }
        
        $i = 0;
        foreach ($this->_form->data as $id => $value) {
            $columnValueName = isset($this->_model->columnValueName) ? $this->_model->columnValueName : 'value';
            $found      = $this->_model->find($id);
            if ($found->count()) {
                $row = $this->_model->find($id)->current();
            } else {
                $row = $this->_model->createRow();
                $primary = $this->_model->info(Zend_Db_Table::PRIMARY);
                
                //es un loop pero solo espera un array de un elemento
                foreach ($primary as $pk) {
                    $row->$pk = $id;
                }
            }
            $row->$columnValueName = $value;
            if ($row->save()) {
                $updated++;
            }
        }
        
        $message .= "Actualizados $updated registros";
        
        $data = array(
            'id' => '0',
            'state' => 0,
            'message' => $message,
            'todo' => '',
            'more' => ''
        );
        
        $this->view->content = Zend_Json::encode($data);
    }
    
    /**
     * Genera las miniaturas a partir de información xml.
     * @param Gamelena_Admin_Xml $node
     * @param array          $infoFile
     * @param string         $path
     * @return GdThumb
     */
    protected function createThumb(Gamelena_Admin_Xml $node, $infoFile, $path)
    {
        try {
            $thumbPath = $path;
            if ($node->getAttribute('path')) {
                //La RegExp busca constantes declaradas entre llaves en atributo xml "path"
                //ej {ROOT_DIR}/myupfiles
                if (preg_match("/^\{(.*)\}(.*)$/", $node->getAttribute('path'), $matches)) {
                    $thumbPath = constant($matches[1]) . $matches[2];
                } else {
                    $thumbPath = $node->getAttribute('path');
                }
            }
            
            //[TODO] cambiar configuracion del Autoloader en Bootstrap para no usar require_once
            include_once TANGERINE_APPLICATION_PATH . '/../library/PhpThumb/ThumbLib.inc.php';
            
            if (!file_exists($thumbPath)) {
                mkdir($thumbPath, 0777, true);
            }
            
            $thumb  = PhpThumbFactory::create($path . "/" . $infoFile['filename']);
            $width  = $node->getAttribute('width') ? $node->getAttribute('width') : 0;
            $height = $node->getAttribute('height') ? $node->getAttribute('height') : 0;
            
            $thumb->resize($width, $height);
            $thumb->save($thumbPath . "/" . $infoFile['filename']);
        } catch (Exception $e) {
            Console::error($e->getMessage() . '-' . $e->getCode() . $e->getTraceAsString());
            return false;
        }
    }
}
