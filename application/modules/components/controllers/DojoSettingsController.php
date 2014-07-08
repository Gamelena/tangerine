<?php
/**
 * Controlador de módulo tipo settings.
 * 
 * @author rodrigo.riquelme@zweicom.com
 *
 */
class Components_DojoSettingsController extends Zend_Controller_Action
{
    
    /**
     * Nombre del modelo Zend_Db_Table debe tener método loadGroups()
     * @see SettingsModel
     * 
     * @var Zwei_Db_Table
     */
    private $_model;
    
    /**
     * Configuración global.
     * 
     * @var Zend_Config
     */
    private $_config;
    
    /**
     * Post constructor.
     * 
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        $this->_helper->layout->disableLayout();
        
        $configParams = Zend_Controller_Front::getInstance()->getParam("bootstrap")->getApplication()->getOptions();
        $this->_config = new Zend_Config($configParams);
        
        $file = Zwei_Admin_Xml::getFullPath($this->getRequest()->getParam('p'));
        $this->_xml = new Zwei_Admin_Xml($file, 0, 1);
        $this->view->mainPane = isset($this->_config->zwei->layout->mainPane) ? $this->_config->zwei->layout->mainPane : 'undefined';
        $this->view->domPrefix = isset($this->view->mainPane) && $this->view->mainPane == 'dijitTabs' ? Zwei_Utils_String::toVarWord($this->getRequest()->getParam('p')) : '';
    }
    /**
     * Acción index.
     * 
     * @return void
     */
    public function indexAction()
    {
        $r = $this->getRequest();
        $model = $this->_xml->getAttribute('target');
        $this->_model = new $model();
        $this->view->model = $this->_model;
        $this->view->groups = $this->_model->loadGroups();
    }
}

