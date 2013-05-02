<?php

/**
 * Tabla HTML, interfaz para operaciones CRUD
 *
 * Ejemplo:
 * <code>
 *  <section name="Detalles" type="dojo-simple-crud" target="SolicitudCdrModel"
 * excel="true" list="true"  edit="false" add="false" delete="false">
 *    <field name="ID" target="id_solicitud" type="id_box" visible="false"
 * edit="false" add="false"/>
 *    <field name="M&amp;oacute;vil" target="msisdn" type="dojo_validation_textbox"
 * trim="true" visible="true" edit="false" add="false"/>
 *    <field name="Fijo" target="fijo" type="dojo_validation_textbox" trim="true"
 * visible="true" edit="false" add="false"/>
 *    <field name="Fecha" target="fecha_ejecucion" trim="true" type="dojo_calendar"
 * constraints="{datePattern:'yyyy-MM-dd'}" visible="true" edit="false"
 * add="false"/>
 *    <field name="Intento" target="intento" type="dojo_validation_textbox"
 * trim="true" visible="true" edit="false" add="false"/>
 *    <field name="Resultado" target="desc_error" trim="true"
 * type="dojo_validation_textbox" visible="true" edit="false" add="false"/>
 *  </section>
 * </code>
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */

class Components_DojoSimpleCrudController extends Zend_Controller_Action
{

    /**
     * @var Zwei_Admin_Xml
     *
     */
    private $_xml = null;

    /**
     * @var Zend_Config
     *
     */
    private $_config = null;
    
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
        $this->view->name = $this->_xml->getAttribute('name');
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
        // action body    }
    }
    
    public function listAction()
    {
        $this->view->model = $this->_xml->getAttribute('target');
        $this->view->elements = $this->_xml->getElements('@visible="true"');
        
        $numElements = count($this->view->elements);
        $widthCol = (100/$numElements)."%";
        for ($i = 1; $i < $numElements; $i++) {
            if (!$this->_xml->getElements()[$i]->getAttribute('width')) {
                $this->_xml->getElements()[$i]->addAttribute('width', $widthCol);
            } 
        }
    }
}

