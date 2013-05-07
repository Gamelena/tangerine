<?php

/**
 * Tabla HTML, interfaz para operaciones CRUD
 *
 * Ejemplo:
 * <?xml version="1.0"?> 
 * <component xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 * xsi:noNamespaceSchemaLocation="components.xsd" 
 * name="Usuarios" type="dojo-simple-crud" target="AclUsersModel" list="true"
 * edit="true" add="true" delete="true">
 * <elements>
 * <element name="ID" target="id" type="id-box" visible="false" edit="false"
 * add="false"/>
 * <element name="Usuario" target="user_name" type="dijit-form-validation-text-box"
 * visible="true" edit="false" add="true"/>
 * <element name="Nombres" target="first_names"
 * type="dijit-form-validation-text-box" visible="true" edit="true" add="true"/>
 * <element name="Apellidos" target="last_names"
 * type="dijit-form-validation-text-box" visible="true" edit="true" add="true"/>
 * <element name="E-Mail" target="email"
 * regExp="[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}" invalidMessage="mail
 * no valido" type="dijit-form-validation-text-box" visible="true" edit="true"
 * add="true" />
 * <element name="Perfil" target="acl_roles_id" defaultValue=""
 * type="dijit-form-filtering-select" table="AclRolesModel" field="role_name"
 * visible="true" edit="true" add="true"/>
 * <element name="Activo" target="approved" type="dijit-form-check-box"
 * formatter="formatYesNo" visible="true" edit="true" add="true"/>
 * </elements>
 * <searchers>
 * <group>
 * <element target="user_name"/>
 * <element target="first_names"/>
 * <element target="last_names"/>
 * <element target="email"/>
 * </group>
 * <element target="acl_roles_id" defaultText="Todo"/>
 * </searchers>
 * </component>
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 *
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
     * @var Zend_Config
     *
     *
     *
     */
    private $_config = null;

    public function init()
    {
        $this->_helper->layout->disableLayout();
        
        $configParams = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $this->_config = new Zend_Config($configParams);
        
        if ($this->getRequest()->getParam('p')) {
            $file = Zwei_Admin_Xml::getFullPath($this->getRequest()->getParam('p'));
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
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->xml = $this->_xml;
        $this->view->elements = $this->_xml->getElements();
        $this->view->groups = $this->_xml->getSearchers(true);
    }

    public function editAction()
    {
        $this->view->mode = 'edit';    
    }

    public function addAction()
    {
        $this->view->mode = 'add';
        $this->render('edit');
    }

    public function listAction()
    {
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->dataDojoType = $this->_xml->getAttribute('serverPagination') === "true" ? 'dojox.data.QueryReadStore' : 'dojo.data.ItemFileReadStore';
        $this->view->gridDojoType = $this->_xml->getAttribute('gridDojoType') ? $this->_xml->getAttribute('gridDojoType') : 'dojox.grid.EnhancedGrid';
        $this->view->plugins = $this->_xml->getAttribute('plugins') ? $this->_xml->getAttribute('plugins') : "{pagination: {defaultPageSize:25, maxPageStep: 5 }}";
        $this->view->onRowClick = $this->_xml->getAttribute('onRowClick') ? "onRowClick:{$this->_xml->getAttribute('onRowClick')}," : "";
        $this->view->searchHideSubmit = $this->_xml->getAttribute('searchHideSubmit') === "true" ? true : false;
        $this->view->elements = $this->_xml->getElements('@visible="true"');
        
        $numElements = count($this->view->elements);
        $widthCol = (100/$numElements)."%";
        for ($i = 1; $i < $numElements; $i++) {
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

