<?php 

/**
 * Transforma un Zend_Db_Rowset a una Tabla HTML
 * 
 * @package Zwei_Utils 
 * @version $Id:$
 * @since 0.1
 *
 */

class Zwei_Utils_Table
{
    /**
     * componente XML
     * @var Zwei_Admin_Xml
     */
    private $_xml;
    /**
     * @var Zend_Db_Rowset|array
     */
    private $_rowset=array();
    /**
     * atributo name componente xml
     * @var array()
     */
    private $_name=array();

    /**
     * Retorna los headers de la tabla HTML  
     * @param $rowset Zend_Db_Rowset
     * @param $component Zwei_Admin_Components
     * @return string html
     */
    
    function showTitles($rowset, $html = true, $separator = ',')
    {
        $out = $html ? "<tr>" :  "";
        $i = 0;
        $keys = array_keys($this->_name);
        $counter = $rowset instanceof Zend_Db_Table_Rowset ? count($rowset[0]->toArray()) : count($rowset[0]);
        foreach ($rowset[0] as $target => $value) 
        {
            if (in_array($target, $keys)) {
                $i++;
                if (!isset($this->_xml)) {
                    if ($html) {
                        $out .= "<th>$target</th>";
                    } else {
                        $out .= stristr(",", $target) || stristr('"', $target) ? '"' . str_replace('"', "", $target) . '"' : $target;
                    }
                } else if(!empty($this->_name[$target])) {
                    if ($html) {
                        $out .= "<th>{$this->_name[$target]}</th>";
                    } else {
                        $out .= stristr(",", $this->_name[$target]) || stristr('"', $this->_name[$target]) ? 
                            '"' . str_replace('"', "", $this->_name[$target]) . '"' : 
                            $this->_name[$target];
                    }
                }
                if (!$html && $i < $counter) $out .= $separator;
            }
        }
        if ($html) $out .= "</tr>";
        $out .= "\r\n ";
        return $out;        
    }
    
    /**
     * Retorna una fila del Rowset como HTML
     * @param $rowset
     * @param $count
     * @return HTML
     */
    
    function showContent($rowset, $count, $html = true, $separator = ',')
    {
        $out = $html ? "<tr>" :  "";
        $i = 0;
        $keys = array_keys($this->_name);
        $counter = is_a($rowset, 'Zend_Db_Table_Rowset') ? count($rowset[$count]->toArray()) : count($rowset[$count]);
        foreach ($rowset[$count] as $target => $value) 
        {
            if (in_array($target, $keys)) {
                $value = html_entity_decode($value);
                $i++;
                if (!empty($this->_name[$target]) || !isset($this->_xml)) {
                    if ($html) {
                        $out .= "<td>$value</td>";
                    } else {
                        $out .= $value && (stristr($separator, $value) || stristr('"', $value)) ? '"' . str_replace('"', "", $value) . '"' : $value;
                    }
                }
                if (!$html && $i < $counter) $out .= $separator;
            }
        }
        if ($html) $out .= "</tr>";
        $out .= "\r\n ";
        return $out;        
    }    
    
    /**
     * Lee los alias de los campos de la tabla según su equivalente en el XML
     * y lo prepara para su impresión si es que debe ser visible
     */
    
    private function parseComponent($component)
    {
        $file = Zwei_Admin_Xml::getFullPath($component);
        $this->_xml = new Zwei_Admin_Xml($file, null, true);
        
        foreach ($this->_xml->elements->element as $element) {
            if ($element->getAttribute("visible") && $element->getAttribute("visible") === "true") {
                if ($element->getAttribute("field")) {
                  $this->_name[$element->getAttribute("field")] = html_entity_decode($element->getAttribute("name"));
                } else {
                  $this->_name[$element->getAttribute("target")] = html_entity_decode($element->getAttribute("name"));
                }
            } 
        }
    }
    
    /**
     * Tranforma un Zend_Db_Rowset a CSV
     * @param array|Zend_Db_Rowset
     * @param string|array componente XML|array de títulos
     * @return string tabla HTML
     */
    
    public function rowsetToCsv($rowset, $component=false)
    {
        if ($component) {
            if (!is_array($component)) { // buscar títulos en componente xml
                $this->parseComponent($component);
            } else { // sacar títulos de array
                $row = $rowset[0];
                $j = 0;
                foreach($row as $i => $v) {
                    $this->_name[$i] = $component[$j];
                    $j++;
                }
    
                $this->_xml = "array";
            }
        }
        
        $count = count($rowset);
        $out = '';
    
        if (!empty($rowset) && count($rowset) > 0) {
            if ($rowset instanceof Zend_Db_Table_Rowset) $rowset = $rowset->toArray();
            
            $out .= $this->showTitles($rowset, false);
            for ($i=0; $i < $count; $i++) {
                $out .= $this->showContent($rowset, $i, false);
            }
        }
        return $out;
    }
    
    
    /**
     * Tranforma un Zend_Db_Rowset a HTML
     * @param array|Zend_Db_Rowset
     * @param string|array componente XML|array de títulos
     * @return string tabla HTML
     */
    
    public function rowsetToHtml($rowset, $component=false)
    { 
        if ($component) {
            if (!is_array($component)) { // buscar títulos en componente xml
                $this->parseComponent($component);
            } else { // sacar títulos de array
                $row = $rowset[0];
                $j = 0;
                foreach($row as $i => $v) {
                    $this->_name[$i] = $component[$j];
                    $j++;
                }
                
                $this->_xml = "array";
            }    
        }
        
        $count = count($rowset);

        $out = "<table border=\"1\">\n";
        if (!empty($rowset) && count($rowset) > 0) {
            $out .= $this->showTitles($rowset);
            for ($i = 0; $i < $count; $i++) {
                $out .= $this->showContent($rowset, $i);
            }
        }        
        $out .= "</table>\n";
        return $out;
    }
    
    /**
     * 
     * Convierte un recordset en una hoja excel
     * 
     * @param array|Zend_Db_Rowset
     * @param string|array componente XML|array de títulos
     * @param string 'Excel5'|'Excel2007'
     * @param string
     */
    public function rowsetToExcel($rowset, $component=false, $excelVersion='Excel5', $filename = false)
    {
        if ($component) {
            if (!is_array($component)) { // buscar títulos en componente xml
                $this->parseComponent($component);
            } else { // sacar títulos de array
                $row = $rowset[0];
                $j = 0;
                foreach($row as $i => $v) {
                    $this->_name[$i] = $component[$j];
                    $j++;
                }
                
                $this->_xml = "array";
            }    
        }
        $count = count($rowset);
        
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);
        $excel->getProperties()->setCreator("Zweicom");
        
        if (!empty($this->_xml[0]['NAME'])) {
            $excel->getProperties()->setTitle(!empty($this->_xml[0]['NAME']));    
        }
        
        if (!$filename) {
            $filename = (!empty($this->_xml[0]['TARGET'])) ? $this->_xml[0]['TARGET'] : "Reporte";
        }
        
        $ext = $excelVersion == 'Excel2007' ? 'xlsx' : 'xls';
        $worksheet = $excel->getActiveSheet();

        $col = "A";
        $row = 1;
        
        //Titulos
        foreach ($rowset[0] as $target => $value) 
        {
            if (!isset($this->_xml)) {
                $worksheet->getCell($col.$row)->setValue($target);
                $col++;
            } else if(!empty($this->_name[$target])) {
                 $title = str_ireplace('\n', "", $this->_name[$target]);
                 $title = html_entity_decode($title, null, 'UTF-8');
                $worksheet->getCell($col.$row)->setValue($title);
                $col++;
            }
        }
        
        $worksheet->getStyle(1)->getFont()->setBold(true)->setUnderline("single")->setName("Arial");

        //Valores
        $row = 2;
        foreach ($rowset as $index => $tuple) {
            $col = "A";
            foreach ($tuple as $target => $value) 
            {
                if(!empty($this->_name[$target]) || !isset($this->_xml)){
                    $value = html_entity_decode($value, null, 'UTF-8');    
                    $worksheet->getCell($col.$row)->setValue($value);
                    $col++;
                }    
            }
            $row ++;
        } 
        

        ob_end_clean();
        
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment;filename=\"$filename.$ext\"");

        $objWriter = PHPExcel_IOFactory::createWriter($excel, $excelVersion);
        ob_end_clean();
        
        $objWriter->save('php://output');
        $excel->disconnectWorksheets();
        unset($excel);
    }
}
