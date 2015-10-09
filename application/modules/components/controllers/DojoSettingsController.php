<?php
/**
 * Controlador de módulo tipo settings.
 *
 * @author rodrigo.riquelme@zweicom.com
 *
 *
 */
class Components_DojoSettingsController extends Zend_Controller_Action
{

    /**
     * Nombre del modelo Zend_Db_Table debe tener método loadGroups()
     * @see SettingsModel
     *
     * @var Zwei_Db_Table
     *
     */
    private $_model = null;

    /**
     * Configuración global.
     *
     * @var Zend_Config
     *
     */
    private $_config = null;
    
    /**
     * Post constructor.
     *
     * @see Zend_Controller_Action::init()
     *
     */
    public function init()
    {
        $this->_helper->layout->disableLayout();
        
        $this->_config = Zwei_Controller_Config::getOptions();
        
        $file = Zwei_Admin_Xml::getFullPath($this->getRequest()->getParam('p'));
        $this->_xml = new Zwei_Admin_Xml($file, 0, 1);
        $model = $this->_xml->getAttribute('target');
        $this->_model = new $model();
        
        $this->_model->xml = $this->_xml;
        
        $this->view->mainPane = isset($this->_config->zwei->layout->mainPane) ? $this->_config->zwei->layout->mainPane : 'undefined';
        $this->view->domPrefix = Zwei_Utils_String::toVarWord($this->getRequest()->getParam('p'));
    }

    /**
     * Acción index.
     *
     * @return void
     *
     */
    public function indexAction()
    {
        $r = $this->getRequest();
        $this->view->model = $this->_model;
        if (!method_exists($this->_model, 'loadGroups')) {
            if ($this->_xml->existsChildren('forms')) {
                $this->view->groups = array();
                $groups = $this->_xml->getTabsWithElements();
                $i = 0;
                /**
                 * @var $group Zwei_Admin_Xml
                 */
                foreach ($groups as $name => $group) {
                    $this->view->groups[$i]['group'] = $group->getAttribute("name");
                    $i++;
                }
            } else {
                throw new Zwei_Exception("Debe estar implementar implementado " . get_class($this->_model). "::loadGroups() o //components/forms/tabs en {$this->getRequest()->getParam('p')}");
            }
            $this->view->onSubmit = $this->_xml->xpath('//component/forms/onSubmit') ? dom_import_simplexml($this->_xml->forms->onSubmit)->textContent : '';
        } else {
            $this->view->groups = $this->_model->loadGroups();
        }
    }
}

