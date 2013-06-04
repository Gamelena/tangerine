<?php

class Components_DojoSettingsController extends Zend_Controller_Action
{
    
    /**
     * Nombre del modelo Zend_Db_Table
     * @var Zwei_Db_Table
     */
    private $_model;
    
    /**
     * 
     * @var Zend_Config
     */
    private $_config;

    public function init()
    {
        $this->_helper->layout->disableLayout();
        
        $configParams = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $this->_config = new Zend_Config($configParams);
        
        $file = Zwei_Admin_Xml::getFullPath($this->getRequest()->getParam('p'));
        $this->_xml = new Zwei_Admin_Xml($file, 0, 1);
        $this->view->mainPane = isset($this->_config->zwei->layout->mainPane) ? $this->_config->zwei->layout->mainPane : 'undefined';
        $this->view->domPrefix  = (isset($this->view->mainPane) && $this->view->mainPane == 'dijitTabs') ? Zwei_Utils_String::toVarWord($this->getRequest()->getParam('p')) : '';
    }

    public function indexAction()
    {
        $r = $this->getRequest();
        $model = $this->_xml->getAttribute('target');
        $this->_model = new $model();
        $this->view->model = $this->_model;
        $this->view->groups = $this->_model->loadGroups();
    }
}

