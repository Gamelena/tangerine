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
     * Nombre archivo xml
     * @var string
     */
    private $_page;
    /**
     * 
     * Objeto XML
     * @var Zwei_Admin_Xml
     */
    private $_xml;
    
    public function init()
    {
        $this->_helper->layout()->disableLayout();
        
        $this->_page = $this->_request->getParam('p');
        $file = Zwei_Admin_Xml::getFullPath($this->_page);
        $this->_xml = new Zwei_Admin_Xml($file, 0, true);
        
        $this->view->dojoStyle = $this->_dojo_style;
        $this->view->baseDojo = $this->_base_dojo_folder;
        $this->view->title = $this->_xml->getAttribute("name") ? $this->_xml->getAttribute("name") : "";
         
        //Si existe $this->_layout[0]['TARGET'] se usa el modelo especificado en el XML, 
        //pero se sigue usando el resto de los parametros
        if ($this->_xml->getAttribute("target")) {
            $this->view->model = $this->_xml->getAttribute("target");
        }
        
        $uri = html_entity_decode(urldecode($this->_request->getParam('uri')));
        if (!empty($uri)) { 
            $aUri = parse_url($uri);
            if (!empty($aUri)) {
                $aParams = array(); 
                
                parse_str($aUri['query'], $aParams);
                
                if ($this->_xml->getAttribute("target")) { $aParams['model'] = $this->_xml->getAttribute("target");}
                else if ($this->_request->getParam("target")) { $aParams['model'] = $this->_request->getParam("target");}
                
                if ($this->_xml->getAttribute("group_by")) { $aParams['group'] = $this->_xml->getAttribute("group_by");}
                else if ($this->_request->getParam("group_by")) { $aParams['group'] = $this->_request->getParam("group_by");}
                
                $aParams['p'] = $this->_request->getParam('p');
        
                $this->view->url = $aUri['scheme'].'://'.$aUri['host'].$aUri['path'].'?'.str_replace('%3B', ';',http_build_query($aParams));
            } 
        }
        //Eje Y
        $this->view->yTarget = $this->_xml->getAttribute("chart_y_target");
        if ($this->_xml->getAttribute("chart_y_label")) $this->view->yTitle = $this->_xml->getAttribute("chart_y_label");
        
        $this->view->options = $this->_xml->getAttribute("options") ? $this->_xml->getAttribute("options") : "new Object()";
        $this->view->chartingTheme = $this->_xml->getAttribute("chart_dojo_theme") ? $this->_xml->getAttribute("chart_dojo_theme") : "Claro";
        
        if (!empty($this->_request->style)) $this->_dojo_style = $this->_request->style;
    }
    

    public function indexAction()
    {
        $this->view->xTarget = $this->_xml->getAttribute("chart_x_target");
        $this->view->items = $this->_xml->getAttribute("chart_items");

        if ($this->_request->getParam("chart_items")) {
            $this->view->items = $this->_request->getParam("chart_items");
            $this->view->url .= "&chart_items=" . $this->_request->getParam("chart_items");
        }
        
        if ($this->_request->getParam("chart_items_description")) {
            $this->view->items = $this->_request->getParam("chart_items_description");
            $this->view->url .= "&chart_items_description=" . $this->_request->getParam("chart_items_description");            
        }
        
        
        if ($this->_xml->getAttribute("chart_x_label")) $this->view->xTitle = $this->_xml->getAttribute("chart_x_label");
        
        $this->view->chartType = $this->_xml->getAttribute("chart_dojo_type") ?  $this->_xml->getAttribute("chart_dojo_type") : "Lines";
    }

    public function pieAction()
    {

    }

    public function barsAction()
    {

    }

    public function linesAction()
    {

    }
}