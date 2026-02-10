<?php

/**
 * Transforma un Zend_Db_Rowset a una Tabla HTML
 * 
 * @package Gamelena_Utils
 * @version $Id:$
 * @since   0.1
 */
class Gamelena_Utils_Table
{

    /**
     * componente XML
     * 
     * @var Gamelena_Admin_Xml
     */
    private $_xml;

    /**
     *
     * @var Zend_Db_Rowset|array
     */
    private $_rowset = array();

    /**
     * atributo name componente xml
     * 
     * @var array()
     */
    private $_name = array();

    /**
     * Retorna los headers de la tabla HTML
     * 
     * @param $rowset Zend_Db_Rowset            
     * @param $component Gamelena_Admin_Components
     * @return string html
     */
    function showTitles($rowset, $html = true, $separator = ',')
    {
        $out = $html ? "<tr>" : "";
        $i = 0;
        $keys = array_keys($this->_name);
        $counter = $rowset instanceof Zend_Db_Table_Rowset ? count(
            $rowset[0]->toArray()
        ) : count($rowset[0]);
        foreach ($rowset[0] as $target => $value) {
            if (in_array($target, $keys)) {
                $i++;
                if (!isset($this->_xml)) {
                    if ($html) {
                        $out .= "<th>$target</th>";
                    } else {
                        $out .= stristr($target, $separator) ||
                            stristr($target, '"') ? '"' .
                            str_replace('"', "", $target) . '"' : $target;
                    }
                } else
                    if (!empty($this->_name[$target])) {
                        if ($html) {
                            $out .= "<th>{$this->_name[$target]}</th>";
                        } else {
                            $out .= stristr(",", $this->_name[$target]) ||
                                stristr('"', $this->_name[$target]) ? '"' .
                                str_replace('"', "", $this->_name[$target]) .
                                '"' : $this->_name[$target];
                        }
                    }
                if (!$html && $i < $counter) {
                    $out .= $separator;
                }
            }
        }
        if ($html) {
            $out .= "</tr>";
        }
        $out .= "\r\n ";
        return $out;
    }

    /**
     * Retorna una fila del Rowset como HTML
     * 
     * @param
     *            $rowset
     * @param
     *            $count
     * @return HTML
     */
    function showContent($rowset, $count, $html = true, $separator = ',')
    {
        $out = $html ? "<tr>" : "";
        $i = 0;
        $keys = array_keys($this->_name);
        $counter = is_a($rowset, 'Zend_Db_Table_Rowset') ? count(
            $rowset[$count]->toArray()
        ) : count($rowset[$count]);
        foreach ($rowset[$count] as $target => $value) {
            if (in_array($target, $keys)) {
                $value = html_entity_decode($value);
                $i++;
                if (!empty($this->_name[$target]) || !isset($this->_xml)) {
                    if ($html) {
                        $out .= "<td>$value</td>";
                    } else {
                        $out .= $value &&
                            (stristr($value, $separator) ||
                                stristr($value, '"')) ? '"' .
                            str_replace('"', "", $value) . '"' : $value;
                    }
                }
                if (!$html && $i < $counter) {
                    $out .= $separator;
                }
            }
        }
        if ($html) {
            $out .= "</tr>";
        }
        $out .= "\r\n ";
        return $out;
    }

    /**
     * Lee los alias de los campos de la tabla según su equivalente en el XML
     * y lo prepara para su impresión si es que debe ser visible
     */
    private function parseComponent($component)
    {
        $file = Gamelena_Admin_Xml::getFullPath($component);
        $this->_xml = new Gamelena_Admin_Xml($file, null, true);

        foreach ($this->_xml->elements->element as $element) {
            if (
                $element->getAttribute("visible") &&
                $element->getAttribute("visible") === "true"
            ) {
                if ($element->getAttribute("field")) {
                    $this->_name[$element->getAttribute("field")] = html_entity_decode(
                        $element->getAttribute("name")
                    );
                } else {
                    $this->_name[$element->getAttribute("target")] = html_entity_decode(
                        $element->getAttribute("name")
                    );
                }
            }
        }
    }

    /**
     * Tranforma un Zend_Db_Rowset a CSV
     * 
     * @param
     *            array|Zend_Db_Rowset
     * @param
     *            string|array componente XML|array de títulos
     * @return string tabla HTML
     */
    public function rowsetToCsv($rowset, $component = false)
    {
        if ($component) {
            if (!is_array($component)) { // buscar títulos en componente xml
                $this->parseComponent($component);
            } else { // sacar títulos de array
                $row = $rowset[0];
                $j = 0;
                foreach ($row as $i => $v) {
                    if (isset($component[$j])) {
                        $this->_name[$i] = $component[$j];
                        $j++;
                    }
                }

                $this->_xml = "array";
            }
        }

        $count = count($rowset);
        $out = '';

        if (!empty($rowset) && count($rowset) > 0) {
            if ($rowset instanceof Zend_Db_Table_Rowset) {
                $rowset = $rowset->toArray();
            }

            $out .= $this->showTitles($rowset, false);
            for ($i = 0; $i < $count; $i++) {
                $out .= $this->showContent($rowset, $i, false);
            }
        }
        return $out;
    }

    /**
     * Tranforma un Zend_Db_Rowset a HTML
     * 
     * @param
     *            array|Zend_Db_Rowset
     * @param
     *            string|array componente XML|array de títulos
     * @return string tabla HTML
     */
    public function rowsetToHtml($rowset, $component = false)
    {
        if ($component) {
            if (!is_array($component)) { // buscar títulos en componente xml
                $this->parseComponent($component);
            } else { // sacar títulos de array
                $row = $rowset[0];
                $j = 0;
                foreach ($row as $i => $v) {
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
     * Convierte un recordset en una hoja excel
     *
     * @param
     *            array|Zend_Db_Rowset
     * @param
     *            string|array componente XML|array de títulos
     * @param
     *            string 'Excel5'|'Excel2007'
     * @param
     *            string
     */
    public function rowsetToExcel(
        $rowset,
        $component = false,
        $excelVersion = 'Excel5',
        $filename = false
    ) {
        if ($component) {
            if (!is_array($component)) { // buscar títulos en componente xml
                $this->parseComponent($component);
            } else { // sacar títulos de array
                $row = $rowset[0];
                $j = 0;
                foreach ($row as $i => $v) {
                    $this->_name[$i] = $component[$j];
                    $j++;
                }

                $this->_xml = "array";
            }
        }
        $count = count($rowset);

        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getProperties()->setCreator("gamelena");

        if (!empty($this->_xml[0]['NAME'])) {
            $spreadsheet->getProperties()->setTitle(!empty($this->_xml[0]['NAME']));
        }

        if (!$filename) {
            $filename = (!empty($this->_xml[0]['TARGET'])) ? $this->_xml[0]['TARGET'] : "Reporte";
        }

        $headers = [];
        $writerType = 'Xls';
        $ext = 'xls';

        if ($excelVersion == 'Excel2007') {
            $writerType = 'Xlsx';
            $ext = 'xlsx';
        }

        $worksheet = $spreadsheet->getActiveSheet();

        $col = 1; // 1-based column index
        $row = 1;

        if ($count) {
            // Titulos
            foreach ($rowset[0] as $target => $value) {
                if (!isset($this->_xml)) {
                    $worksheet->setCellValueByColumnAndRow($col, $row, $target);
                    $col++;
                } else
                    if (!empty($this->_name[$target])) {
                        $title = str_ireplace('\n', "", $this->_name[$target]);
                        $title = html_entity_decode((string) $title, ENT_QUOTES | ENT_HTML401, 'UTF-8');
                        $worksheet->setCellValueByColumnAndRow($col, $row, $title);
                        $col++;
                    }
            }

            // Styles for header
            $styleArray = [
                'font' => [
                    'bold' => true,
                    'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE,
                    'name' => 'Arial'
                ]
            ];
            $worksheet->getStyle('A1:' . $worksheet->getHighestColumn() . '1')->applyFromArray($styleArray);

            // Valores
            $row = 2;
            foreach ($rowset as $index => $tuple) {
                $col = 1;
                foreach ($tuple as $target => $value) {
                    if (!empty($this->_name[$target]) || !isset($this->_xml)) {
                        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML401, 'UTF-8');
                        $worksheet->setCellValueByColumnAndRow($col, $row, $value);
                        $col++;
                    }
                }
                $row++;
            }
        }

        // Clean output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Redirect output to a client’s web browser (Xls or Xlsx)
        if ($writerType == 'Xlsx') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } else {
            header('Content-Type: application/vnd.ms-excel');
        }

        header("Content-Disposition: attachment;filename=\"$filename.$ext\"");
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $writerType);
        $writer->save('php://output');
        exit;
    }
}
