<?php

class Components_DojoChartController extends Zend_Controller_Action
{
    /**
     * 
     * Dojo theme
     * @var string
     */
    private $_dojo_style = 'claro';
    /**
     * 
     * carpeta base de dojo toolkit
     * @var string 
     */
    private $_base_dojo_folder = '/dojotoolkit';
    /**
     * 
     * Enter archivo xml
     * @var string
     */
    private $_page;
    /**
     * 
     * Array de archivo xml
     * @var array
     */
    private $_layout;
    
    public function init()
    {
        $this->_form = new Zwei_Utils_Form();
        $this->_helper->layout()->disableLayout();
        
        $this->_page = $this->_form->p;
        $xml = new Zwei_Admin_Xml();
        $file = Zwei_Admin_Xml::getFullPath($this->_page);
        $xml->parse($file);
        $this->_layout = $xml->elements;
        
        $this->view->dojoStyle = $this->_dojo_style;
        $this->view->baseDojo = $this->_base_dojo_folder;
        $this->view->title = isset($this->_layout[0]['NAME']) ? $this->_layout[0]['NAME'] : ""; 
        $this->view->model = $this->_layout[0]['TARGET'];
        $this->view->xTarget = $this->_layout[0]['CHART_X_TARGET'];
        if (isset($this->_layout[0]['CHART_X_LABEL'])) $this->view->xTitle = $this->_layout[0]['CHART_X_LABEL'];
        
        $this->view->options = isset($this->_layout[0]['OPTIONS']) ? $this->_layout[0]['OPTIONS'] : "new Object()";
        $this->view->chartingTheme = isset($this->_layout[0]['CHART_THEME']) ? $this->_layout[0]['CHART_THEME'] : "Claro";
        
        
        
        if (!empty($this->_request->style)) $this->_dojo_style = $this->_request->style;

    }
    

    public function indexAction()
    {
        // action body
    }

    public function pieAction()
    {

    }

    public function barsAction()
    {
        $this->view->yTarget = $this->_layout[0]['CHART_Y_TARGET'];
        $this->view->yTitle = $this->_layout[0]['CHART_Y_LABEL'];
    }

    public function linesAction()
    {
        $this->view->yTarget = $this->_layout[0]['CHART_Y_TARGET'];
        $this->view->yTitle = $this->_layout[0]['CHART_Y_LABEL'];
    }
}







