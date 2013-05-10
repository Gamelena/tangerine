<?php

/**
 * Interfaz para operaciones CRUD
 *
 * @example
 * @package Components
 * @version 2013-05-09
 * @since 1.0
 * @author rodrigo.riquelme@zweicom.com
 * 
 * <code>
 * <?xml version="1.0"?> 
 * <component xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="components.xsd" 
 * name="Usuarios" type="dojo-simple-crud" target="AclUsersModel" list="true" edit="true" add="true" delete="true">
 *     <elements>
 *         <element name="ID" target="id" type="id-box" visible="false" edit="false" add="false"/>
 *         <element name="Usuario" target="user_name" type="dijit-form-validation-text-box" visible="true" edit="false" add="true"/>
 *         <element name="Nombres" target="first_names" type="dijit-form-validation-text-box" visible="true" edit="true" add="true"/>
 *         <element name="Apellidos" target="last_names" type="dijit-form-validation-text-box" visible="true" edit="true" add="true"/>
 *         <element name="E-Mail" target="email" regExp="[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}" invalidMessage="mail no valido" type="dijit-form-validation-text-box" visible="true" edit="true" add="true" />
 *         <element name="Perfil" target="acl_roles_id" defaultValue="" type="dijit-form-filtering-select" table="AclRolesModel" field="role_name" visible="true" edit="true" add="true"/>
 *         <element name="Activo" target="approved" type="dijit-form-check-box" formatter="formatYesNo" visible="true" edit="true" add="true"/>
 *     </elements>
 *     <searchers>
 *         <group>
 *             <element target="user_name"/>
 *             <element target="first_names"/>
 *             <element target="last_names"/>
 *             <element target="email"/>
 *         </group>
 *         <element target="acl_roles_id" defaultText="Todo"/>
 *     </searchers>
 * </component>
 * </code>
 *
 */

class Components_DojoSimpleCrudController extends Zend_Controller_Action
{

    /**
     * @var Zwei_Admin_Xml
     *
     *
     *
     */
    private $_xml = null;
    
    /**
     * 
     * @var string
     */
    private $_component;

    /**
     * @var Zend_Config
     *
     *
     *
     */
    private $_config = null;
    
    /**
     * 
     * @var Zwei_Admin_Acl
     */
    private $_acl;
    
    /**
     * 
     * @var Zwei_Db_Table
     */
    private $_model;
    
    public function init()
    {
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $this->_acl = new Zwei_Admin_Acl($userInfo->user_name);
        
        $configParams = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $this->_config = new Zend_Config($configParams);
        
        if ($this->getRequest()->getParam('p')) {
            $this->_component = $this->getRequest()->getParam('p');
            $file = Zwei_Admin_Xml::getFullPath($this->_component);
            $this->_xml = new Zwei_Admin_Xml($file, 0, 1);
        }
        $this->view->mainPane = isset($this->_config->zwei->layout->mainPane) ? $this->_config->zwei->layout->mainPane : 'undefined';
        $this->view->domPrefix  = (isset($this->view->mainPane) && $this->view->mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord($this->getRequest()->getParam('p')) : '';
    }

    public function indexAction()
    {
        $this->view->name = $this->_xml->getAttribute('name');
        $this->view->includeJs = $this->_xml->getAttribute('js') ? "<script src=\"".BASE_URL.'js/'.$this->_xml->getAttribute('js')."\"></script>" : '';
    }

    public function searchAction()
    {
        $this->view->p = $this->_component;
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->xml = $this->_xml;
        $this->view->groups = $this->_xml->getSearchers(true);
    }

    public function initForm($mode){
        $this->view->p = $this->_component;
        $this->view->xml = $this->_xml;
        $this->view->mode = $mode;
        $className = $this->_xml->getAttribute('target');
        $this->view->model = $className;
        $this->_model = new $className();
        $this->view->modelPrimary = $this->_model->info('primary');
        $this->view->tabs = $this->_xml->getTabs(true, "@$mode='true'");
    }
    
    public function editAction()
    {
        $this->initForm('edit');
    }

    public function addAction()
    {
        $this->initForm('add');
        $this->render('edit');
    }

    public function listAction()
    {
        $this->view->p = $this->_component;
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->dataDojoType = $this->_xml->getAttribute('serverPagination') === "true" ? 'dojox.data.QueryReadStore' : 'dojo.data.ItemFileReadStore';
        $this->view->gridDojoType = $this->_xml->getAttribute('gridDojoType') ? $this->_xml->getAttribute('gridDojoType') : 'dojox.grid.EnhancedGrid';
        $this->view->plugins = $this->_xml->getAttribute('plugins') ? $this->_xml->getAttribute('plugins') : "{pagination: {defaultPageSize:25, maxPageStep: 5 }}";
        $this->view->onRowClick = $this->_xml->getAttribute('onRowClick') ? "onRowClick:{$this->_xml->getAttribute('onRowClick')}," : "";
        $this->view->onRowDblClick = $this->_xml->getAttribute('onRowDblClick') 
            ? $this->_xml->getAttribute('onRowDblClick') : 
                $this->_acl->isUserAllowed($this->_component, 'EDIT') ? 
                    "var form = new zwei.Form({dijitDialog: dijit.byId('{$this->view->domPrefix}dialog_edit'), dijitForm: dijit.byId('{$this->view->domPrefix}form_edit'), dijitDataGrid: dijit.byId('{$this->view->domPrefix}dataGrid')}); form.showDialog()" : "";
        
        $this->view->searchHideSubmit = $this->_xml->getAttribute('searchHideSubmit') === "true" ? true : false;
        $this->view->elements = $this->_xml->getElements("@visible='true'");
        
        $numElements = count($this->view->elements);
        $widthCol = round((100/$numElements), 1) . "%";//Se le asigna a cada columna de la grilla un ancho proporcional a su cantidad en porcentaje
        for ($i = 1; $i < $numElements; $i++) {
            //Para cada columna se permite sobreescribir el ancho asignado por defecto, se sugiere trabajar con porcentajes
            if (!$this->_xml->getElements()[$i]->getAttribute('width')) {
                $this->_xml->getElements()[$i]->addAttribute('width', $widthCol);
            }
        } 
        
    }

    public function keypadAction()
    {
        // action body
    }


}

