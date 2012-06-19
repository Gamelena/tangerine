<?php

/**
 * Grafico Google
 *
 * Por ahora funciona como extensión de componente Table_Dojo del cual hereda el datastore, típicamente mediante popup.<br/>
 * No funciona como stand alone.<br/>
 * Ejemplo:
 * <code>
 * <section name="Gráfico de Ganadores" type="google_chart" inherits_data="true" 
 * cols="nombre" titles="fecha_hora" items="cantidad" list="false" edit="false" add="false"
 * delete="false"/>
 * </code>
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */

final class Zwei_Admin_Components_GoogleChart implements Zwei_Admin_ComponentsInterface
{
    /**
     * 
     * @var String
     */
    public $page;
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
    
    /**
     * 
     * @var array
     */
    private $_layout;

    /**
     * 
     * @var array
     */
    private $_cols;
    
    /**
     * 
     * @var array
     */
    private $_rows;
    
    /**
     * 
     * @var Zend_Db_Adapter_Abstract
     */ 
    private $_db;
    /**
     * 
     * @param string
     */ 
    public function __construct($page){
        $this->page=$page;
        $userInfo = Zend_Auth::getInstance()->getStorage()->read();
        $this->_acl = new Zwei_Admin_Acl($userInfo->user_name);
        $config = new Zend_Config_Ini(ROOT_DIR.'/application/configs/application.ini', APPLICATION_ENV);
        $this->_db = Zend_Db::factory($config->resources->multidb->auth);
        
    }

    /**
     * Despliegue para mostrar listados
     * @return string
     */
    public function display()
    {
        $oForm = new Zwei_Utils_Form();
         
        $oViewtable=new Zwei_Admin_Components_Helpers_ViewTable($this->page);
        $oViewtable->getLayout();
        $this->_layout = $oViewtable->layout;

        $sXtype = (isset($this->_layout[0]['CHART_X_TYPE'])) ? $this->_layout[0]['CHART_X_TYPE'] : 'string';
        
        $out = "    <script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>
    <script type=\"text/javascript\">
      try{
            window.parent.document.getElementById('popup_body').style.backgroundColor='#ffffff';
      }catch(e){}

      
      google.load(\"visualization\", \"1\", {packages:[\"corechart\"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.axisTitlesPosition='in';
        data.addColumn('$sXtype', 'Columna');\n";
        
        $sStoreUri = html_entity_decode(urldecode($oForm->uri));
        if (isset($this->_layout[0]['INHERITS_DATA'])) {
            $aUri = parse_url($sStoreUri);
            @parse_str($aUri['query'], $aParams); 
            $oSubForm = new Zwei_Utils_Form($aParams);
            
            if (!empty($this->_layout[0]['TARGET'])) {
                $model = Zwei_Utils_String::toClassWord($this->_layout[0]['TARGET'])."Model";
                $oSubForm->model = $this->_layout[0]['TARGET'];
            } else {    
                @$model = Zwei_Utils_String::toClassWord($oSubForm->model)."Model";
            }
            
            if(!@class_exists($model)) exit ("<b>No se encuentra consulta</b><br/>Asegurese de haber clickeado \"Buscar\" previamente.");
            
            $this->_model = new $model;
            


            
            if (isset($this->_layout[0]['GROUP_BY']) || isset($this->_layout[0]['FIELDS'])) {
                $groups = explode(";", $this->_layout[0]['GROUP_BY']);
                $fields = explode(";", $this->_layout[0]['FIELDS']);
                if (!is_array($groups)) $groups = array($groups);
                if (!is_array($fields)) $fields = array($fields);
                
                if (empty($fields)) $fields = array('*');
                $fields['count'] = new Zend_Db_Expr("COUNT(*)");
                
                $oSelect = new Zend_Db_Select($this->_db);
                $oDbObject = new Zwei_Db_Object($oSubForm, $oSelect);
                $oSelect = $oDbObject->select();
                $oSelect->from($this->_model->getName(), $fields);
                if (!empty($groups)) {
                    foreach ($groups as $g) {
                       $oSelect->group($g);
                    }
                }
                $oCollection = $this->_db->fetchAll($oSelect);
            } else {
                $oDbObject = new Zwei_Db_Object($oSubForm);
                $oSelect = $oDbObject->select();
                $oCollection = $this->_model->fetchAll($oSelect);
            }
            
            Zwei_Utils_Debug::writeBySettings($oSelect->__toString(), 'query_log');
             
                
            foreach ($oCollection as $v)
            {
                if (!isset($this->_rows[$v[$this->_layout[0]['COLS']]]))  {
                    $this->_rows[$v[$this->_layout[0]['COLS']]] = array();
                }
                $this->_rows[$v[$this->_layout[0]['COLS']]][$v[$this->_layout[0]['TITLES']]] = $v[$this->_layout[0]['ITEMS']];
            }    
            
            $iCols = count(array_keys($this->_rows));
            $outCols = "";
            $outRows = "\tdata.addRows([\n";
            
            $printedRows = array();
            foreach (array_keys($this->_rows) as $v) {
                $outCols .= "\tdata.addColumn('number', '$v')\n";
                foreach ($this->_rows[$v] as $j => $w){
                    if (!in_array($j, $printedRows)) {
                        $outRows .= "\t\t[";
                        if (!isset($this->_layout[0]['CHART_X_TYPE'])) {
                            $outRows .= $j;
                        } else if ($this->_layout[0]['CHART_X_TYPE'] == 'datetime') {
                            $aDateTime = explode(" ", $j);
                            $aDate = explode("-", $aDateTime[0]);
                            $aTime = explode(":", $aDateTime[1]);
                            $y = (int) $aDate[0];
                            $M = (int) $aDate[1];
                            $d = (int) $aDate[2];
                            $h = (int) $aTime[0];
                            $m = (int) $aTime[1];
                            $s = (int) $aTime[2];
                            $outRows .= "new Date($y, $M, $d, $h, $m, $s),";
                        }
                        $auxCount = 1;
                        foreach (array_keys($this->_rows) as $x) {
                            $outRows .= isset($this->_rows[$x][$j]) ? $this->_rows[$x][$j] : "null";
                            $outRows .= $auxCount < $iCols ? "," : "";
                            $auxCount++;
                        }
                        $outRows .= "],\n";
                        $printedRows[] = $j;
                    }   
                }
            }
            $outRows .= "\t\tnull\n\t]);\n";
            $out .= $outCols;
            $out .= $outRows;
            
        } else {
            
            
            /*[TODO]
             * Aca debiera ir la logica en caso de que no herede datos de URL,
             * usar clase modelo enviada en $viewtable->layout[0]['TARGET']
             */
        }
         

         

        $out .= "
        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        var options = {$this->_layout[0]['OPTIONS']};
        
        chart.draw(data, options);
      }
    </script>
    <div id=\"chart_div\"></div>

        ";
      
        return $out;
    }
    
    /**
     * 
     * @param Zend_Db_Table_Select
     * @return string
     */
    public function printTitles($subSelect)
    {
        $oModel = $this->_model;
        $table = $this->_model->getName();

        $oSelect = $this->_db->select()->distinct()
            ->from($table)
            ->where("{$this->_layout[0]['TITLE']} IN ($subSelect)")
            ;
           
        Zwei_Utils_Debug::write($oSelect->__toString());    
            
        return "";
    }
    
}