<?php
/**
 * Controlador para generar gráficos con Dojo Toolkit.
 *
 * @example
 * <code>
 * <?xml version="1.0"?>
 * <!DOCTYPE section PUBLIC "//COMPONENTS/" "components.dtd">
 * <component 
 * name="Gráfico por Canal"
 * type="dojo-chart" 
 * chartYTarget="count"
 * chartXType="datetime"
 * chartXTarget="fecha"  
 * chartDojoType="Markers"
 * chartDojoTheme="Shrooms"
 * target="counter_c_d_r_compras"
 * />
 * </code>
 *
 *
 *
 */


/**
 * Controlador para generar gráficos con Dojo Toolkit.
 *
 * @example
 * <code>
 * <?xml version="1.0"?>
 * <!DOCTYPE section PUBLIC "//COMPONENTS/" "components.dtd">
 * <component 
 * name="Gráfico por Canal"
 * type="dojo-chart" 
 * chartYTarget="count"
 * chartXType="datetime"
 * chartXTarget="fecha"  
 * chartDojoType="Markers"
 * chartDojoTheme="Shrooms"
 * target="counter_c_d_r_compras"
 * />
 * </code>
 *
 *
 *
 */
class Components_DojoChartController extends Zend_Controller_Action
{

    /**
     * Dojo theme
     * @var string
     *
     */
    private $_dojo_style = 'claro';

    /**
     * carpeta base de dojo toolkit
     * @var string 
     *
     */
    private $_base_dojo_folder = '/dojotoolkit';

    /**
     * Nombre archivo xml
     * @var string
     *
     */
    private $_page = null;

    /**
     * Objeto XML
     * @var Zwei_Admin_Xml
     *
     */
    private $_xml = null;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        
        $this->_page = $this->_request->getParam('p');
        $this->view->component = $this->_page;
        $this->view->domPrefix = Zwei_Utils_String::toVarWord($this->_page);
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
                
                $httpParams = http_build_query($aParams);
                $this->view->url = $aUri['scheme'].'://'.$aUri['host'].$aUri['path'].'?'.str_replace('%3B', ';', $httpParams);
            } 
        }
        $aParams['format'] = 'excel';
        $aParams['excel_formatter'] = 'csv';
        $this->view->excelParams = http_build_query($aParams);
        
        //Eje Y
        $this->view->yTarget = $this->_xml->getAttribute("chartYTarget");
        if ($this->_xml->getAttribute("chart_y_label")) $this->view->yTitle = $this->_xml->getAttribute("chart_y_label");
        
        $this->view->options = $this->_xml->getAttribute("options") ? $this->_xml->getAttribute("options") : "new Object()";
        $this->view->chartingTheme = $this->_xml->getAttribute("chartDojoTheme") ? $this->_xml->getAttribute("chartDojoTheme") : "Claro";
        
        if (!empty($this->_request->style)) $this->_dojo_style = $this->_request->style;
    }

    public function indexAction()
    {
        $this->view->xTarget = $this->_xml->getAttribute("chartXTarget");
        $this->view->items = $this->_xml->getAttribute("chart_items");
        $this->view->excel = $this->_xml->helpers && $this->_xml->helpers->excel;

        if ($this->_request->getParam("chart_items")) {
            $this->view->items = $this->_request->getParam("chart_items");
            $this->view->url .= "&chart_items=" . $this->_request->getParam("chart_items");
        }
        
        if ($this->_request->getParam("chart_items_description")) {
            $this->view->items = $this->_request->getParam("chart_items_description");
            $this->view->url .= "&chart_items_description=" . $this->_request->getParam("chart_items_description");            
        }
        
        
        if ($this->_xml->getAttribute("chart_x_label")) $this->view->xTitle = $this->_xml->getAttribute("chart_x_label");
        
        $this->view->chartType = $this->_xml->getAttribute("chartDojoType") ?  $this->_xml->getAttribute("chartDojoType") : "Lines";
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

    public function excelAction()
    {
        /**
         * @var $model Zwei_Db_Table 
         */
        $model = new $this->view->model();
        
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename={$this->view->model}.csv");
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UCS-2LE');
        
        $elements = $this->_xml->elements ? $this->_xml->xpath('//component/elements/element') : array();
        
        
        foreach ($elements as $element) {
            $labels[] = $element->getAttribute("name") ? $element->getAttribute("name") : $element->getAttribute("target");
        }
        
        if (empty($elements)) {
            $labels = false;
        }
        
        
        $select = $model->select();
        $dbObject = new Zwei_Db_Object($this->getRequest()->getParams());
        $select = $dbObject->select();
        
        $collection = $model->fetchAll($select);
        $table = new Zwei_Utils_Table();
        
        $content = $table->rowsetToCsv($collection, $labels);
        $this->view->content = chr(255) . chr(254) . mb_convert_encoding($content, 'UCS-2LE', 'UTF-8');
        $this->render();
    }


}


