<?php

/**
 * Controlador de Componentes XML.
 * 
 * No relacionado con controlador MVC de Zend
 * 
 * @category Zwei 
 * @package Zwei_Admin
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Controller{
    //[TODO] asegurarse de que estos atributos public no sean usados como tales 
    //y cambiarlos a protected
    /**
     * 
     * @var array
     */
    public $layout;
    /**
     * 
     * @var string
     */
    public $page;
    /**
     * 
     * @var int
     */
    public $id;
    /**
     * 
     * @var string
     */
    public $name;
    /**
     * 
     * @var string
     */
    public $target;
    /**
     * 
     * @var int
     */
    public $maxsize;
    /**
     * 
     * @var Zwei_Admin_Acl
     */
    protected $_acl;
    /**
     * 
     * @var Zend_Config
     */
    protected $_config;
    /**
     * 
     * @var string
     */
    protected $_mainPane;

  /**
   * Constructor
   * @param string
   * @param array
   * @param boolean
   */
  
    function __construct($page, $id=array())
    {
        $this->layout=array();
        $this->page = $page;
        $this->id = $id;
        $this->requested_params = "";
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        
        $configParams = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $this->_config = new Zend_Config($configParams);
        
        
        $this->_mainPane = isset($this->_config->zwei->layout->mainPane) ? $this->_config->zwei->layout->mainPane : 'undefined';
        if (isset($userInfo) && !empty($userInfo)) $this->_acl = new Zwei_Admin_Acl($userInfo->user_name);
    }

    function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Captura los elementos del componente XML
     * @return void
     */
    function getLayout()
    {
        $oXml = new Zwei_Admin_Xml();
        
        $file = Zwei_Admin_Xml::getFullPath($this->page);
        
        $oXml->parse($file);
        $this->layout = $oXml->elements;
        $this->name = @$this->layout[0]['NAME'];
        $this->target = @$this->layout[0]['TARGET'];
        return $this->layout;
    }
    
    /**
     * Obtiene los datos a desplegar del modelo asociado, se usa con template urban, componente xml type="table"
     * @param int
     * @param int
     * @param string
     * @param string
     * @param string
     */
    
    function getData($start=0, $limit=20, $search="", $sort="", $dir="ASC")
    {
        $oForm = new Zwei_Utils_Form();
        $ClassName = Zwei_Utils_String::toClassWord($this->layout[0]["TARGET"])."Model"; 
        $oModel = new $ClassName();
        $oSelect = $oModel->select();
        
        if ($search != "") {
            if (method_exists($oModel,"getSearchFields")){
                $fields = $oModel->getSearchFields();
                foreach ($fields as $f) {
                    //Ejs. de filtros interpretados como PK o FK: id, categoria_id, id_categoria
                    if ($f == "id" || preg_match("/^id_/", $f) || preg_match("/id$_/", $f)) {
                        $oSelect->where($oModel->getAdapter()->quoteInto("$f = ? ", $search));
                    } else {
                        $oSelect->where($oModel->getAdapter()->quoteInto("$f LIKE ?", "%$search%"));                   
                    }   
                }
            }
        }
        
        if (!empty($this->layout[0]["WHERE"])) $oSelect->where($this->layout[0]["WHERE"]);
        if ($sort!="") $oSelect->order("$sort $dir");
        if (empty($this->id)) $oSelect->limit($limit, $start);
        
        //Zwei_Utils_Debug::write($select->__toString());
        //Get request parameters for the data fields
        $count = count($this->layout);
        for ($i=1; $i<$count; $i++) {
            $field = $this->layout[$i]["TARGET"];
            if (isset($form->$field) && !is_array($form->$field) && $form->$field!=="") {
                $oSelect->where($oSelect->getAdapter()->quoteInto("$field = ?", $form->$field));
            }
        }
        //Se imprime query en log debug según configuración del sitio
        Zwei_Utils_Debug::writeBySettings($oSelect->__toString());
        $aRows=$oModel->fetchAll($oSelect);
        
        foreach ($aRows as $row) {
            for ($i=1; $i<$count; $i++) {
                if (isset($this->layout[$i]["VALUE"]) && !is_array($this->layout[$i]["VALUE"])) {
                    $this->layout[$i]["VALUE"] = array($this->layout[$i]["VALUE"]); 
                } else {
                    //$this->layout[$i]["VALUE"] = array();    
                }
                 
                if (isset($row[$this->layout[$i]["TARGET"]])) {
                    $this->layout[$i]["VALUE"][] = $row[$this->layout[$i]["TARGET"]];
                } else if (isset($this->layout[$i]["DEFAULT"])) {
                    $this->layout[$i]["VALUE"][] = $this->layout[$i]["DEFAULT"];
                } else {
                    $this->layout[$i]["VALUE"][] = "";
                }
            }
        }
        $this->maxsize = $aRows->count();
    }

    /*
     * Se obtienen los parametros de un nodo XML para configurar un input, 
     * el índice puede venir en minusculas ó mayusculas, 
     * ya que puede ser del (con índice en mayúsculas)
     * @param SimpleXMLElement
     * @return array()
     */
    function getInputParams($node)
    {
        $params = array();
        
        foreach ($node->attributes() as $i=>$v) {
            if ($v == "true") $params[strtoupper($i)] = true;
            else if ($v == "false") $params[strtoupper($i)] = false;
            else $params[strtoupper($i)] = (string) $v;
        }
        
        return $params;
    }
    
    /**
    * Captura los parámetros $_REQUEST si existen en el componente XML
    * @return string $_GET
    */
    function getRequested_params()
    { 
        $form = new Zwei_Utils_Form();
        $count = count($this->layout);
        $params = "";
    
        for ($i=1; $i<$count; $i++) {
            $field = @$this->layout[$i]["TARGET"];
            if (isset($form->$field)) {
                if (is_array($form->$field)) {
                    $params .= "&$field=".urlencode($form->{$field}[0]);
                } else {
                    $params .= "&$field=".urlencode($form->$field);
                }
            }
        }
        return $params; 
    }
}
