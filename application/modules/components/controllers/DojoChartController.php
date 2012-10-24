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
        $this->_helper->layout()->disableLayout();
        
        $this->_page = $this->_request->getParam('p');
        $xml = new Zwei_Admin_Xml();
        $file = $xml->getFullPath($this->_page);
        $xml->parse($file);
        $this->_layout = $xml->elements;
        
        $this->view->dojoStyle = $this->_dojo_style;
        $this->view->baseDojo = $this->_base_dojo_folder;
        $this->view->title = isset($this->_layout[0]['NAME']) ? $this->_layout[0]['NAME'] : "";
         
        //Si existe $this->_layout[0]['TARGET'] se usa el modelo especificado en el XML, 
        //pero se sigue usando el resto de los parametros
        if (isset($this->_layout[0]['TARGET'])) {
            $this->view->model = $this->_layout[0]['TARGET'];
        }
        
        $uri = html_entity_decode(urldecode($this->_request->getParam('uri')));
        if (!empty($uri)) { 
            $aUri = parse_url($uri);
            $aParams = array(); 
            
            parse_str($aUri['query'], $aParams);
            
            if (isset($this->_layout[0]['TARGET'])) $aParams['model'] = $this->_layout[0]['TARGET'];
            if (isset($this->_layout[0]['GROUP_BY'])) $aParams['group'] = $this->_layout[0]['GROUP_BY'];
    
            if (!empty($aUri)) $this->view->url = $aUri['scheme'].'://'.$aUri['host'].$aUri['path'].'?'.str_replace('%3B', ';',http_build_query($aParams)); 
        }
        //Eje Y
        $this->view->yTarget = $this->_layout[0]['CHART_Y_TARGET'];
        if (isset($this->_layout[0]['CHART_Y_LABEL'])) $this->view->yTitle = $this->_layout[0]['CHART_Y_LABEL'];
        
        $this->view->options = isset($this->_layout[0]['OPTIONS']) ? $this->_layout[0]['OPTIONS'] : "new Object()";
        $this->view->chartingTheme = isset($this->_layout[0]['CHART_DOJO_THEME']) ? $this->_layout[0]['CHART_DOJO_THEME'] : "Claro";
        
        if (!empty($this->_request->style)) $this->_dojo_style = $this->_request->style;
    }
    

    public function indexAction()
    {
        $this->view->xTarget = $this->_layout[0]['CHART_X_TARGET'];
        $this->view->columns = $this->_layout[0]['CHART_COLUMNS'];
        
        
        if (isset($this->_layout[0]['CHART_X_LABEL'])) $this->view->xTitle = $this->_layout[0]['CHART_X_LABEL'];
        
        $this->view->chartType = (isset($this->_layout[0]['CHART_DOJO_TYPE'])) ? $this->_layout[0]['CHART_DOJO_TYPE'] : "Lines";
    }

    public function pieAction()
    {

    }

    public function barsAction()
    {
        $this->view->xTarget = $this->_layout[0]['CHART_X_TARGET'];
        if (isset($this->_layout[0]['CHART_X_LABEL'])) $this->view->xTitle = $this->_layout[0]['CHART_X_LABEL'];
    }

    public function linesAction()
    {
        $this->view->xTarget = $this->_layout[0]['CHART_X_TARGET'];
        $this->view->columns = $this->_layout[0]['CHART_COLUMNS'];
        
        
        if (isset($this->_layout[0]['CHART_X_LABEL'])) $this->view->xTitle = $this->_layout[0]['CHART_X_LABEL'];
    }
}







