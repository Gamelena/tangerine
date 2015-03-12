<?php 
class Zwei_Utils_File_Uploader
{
    /**
     * 
     * @var Zwei_Admin_Xml
     */
    private $_xml;
    
    /**
     *  
     * @var int
     */
    private $_maxAllowedLines = 5000;
    
    /**
     * 
     * @var string
     */
    private $_path;
    
    /**
     * @var array
     */
    private $_columns = array();
    
    /**
     * 
     * @var Zend_Db_Table_Abstract
     */
    private $_model;
    
    /**
     * @var int
     */
    private $_processed = 0;
    
    /**
     * 
     * @var boolean
     */
    private $_truncate = false;
    
    /**
     * 
     * @param Zwei_Admin_Xml $xml
     * @param Zwei_Utils_Form $form
     */
    public function __construct($xml)
    {
        $this->_xml = $xml;
        $this->_columns = $xml->xpath("//component/elements/element[@visible='true']");
        $modelName = $this->_xml->getAttribute('target'); 
        $this->_model = new $modelName();
        $this->_path = ROOT_DIR . "/public/upfiles";
    }
    
    /**
     * 
     * @param string $path
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }
    
    /**
     * 
     * @param boolean $value
     */
    public function truncate($value = true)
    {
        $this->_truncate = $value;
    }
    
    /**
     *
     * @param int $path
     */
    public function setMaxAllowedLines($maxAllowedLines)
    {
        $this->_maxAllowedLines = $maxAllowedLines;
    }
    
    /**
     * 
     * @param $_FILE $file
     * @param string $action
     */
    public function process($file, $action)
    {
        $ext = Zwei_Utils_File::getExtension($file['name']);
        //Si se supera el valor de la variable de configuración "upload_max_filesize", $_FILE['size'] retorna 0
        if ($file['size'] == 0) {
            $response['error'] = '9';
            $response['message'] = "El archivo no puede pesar más de " . ini_get('upload_max_filesize') . " bytes";
        } else if ($ext == 'csv') {
            $form = new Zwei_Utils_Form();
            
            $infoFiles = $form->upload("file", $this->_path);
            $filename  = $this->_path . "/" . $infoFiles['filename'];
            
            $separator = Zwei_Utils_File::getSeparator($filename, 5);
            
            $j        = 0;
            $auxQuery = '';
            $query    = '';
            $data = array();
            $aQueries = array();
            
            $handle = fopen($filename, 'r');
            $ad = $this->_model->getAdapter();
            $processed = 0;
            
            /**
             * @var $column Zwei_Admin_Xml
             */
            $i = 0;
            $firstLine = true;
            $errors = 0;
            
            while ($line = fgetcsv($handle, null, $separator)) {
                $validateLine = !empty($line) && count($line) >= count($this->_columns);
                if ($validateLine) {
                    if (!empty($line[0])) {
                        $push = false;
                        foreach ($this->_columns as $j => $column) {
                            $text = trim(Zwei_Utils_String::textify($line[$j]));
                            
                            //Se valida contra las 'regExp' y los 'required' declarados en XML
                            $validate = (!$column->getAttribute("required") || !$column->getAttribute("required") != 'true' || $text !== '') &&
                            (!$column->getAttribute("regExp") || preg_match("/{$column->getAttribute("regExp")}/", $text));
                            
                            if (($firstLine && $text == $column->getAttribute('name'))) { //Si primera línea corresponde a titulos declarados en XML, no se procesa.
                                break;
                            } else if (!$validate && $action !== 'delete') {
                                Console::error(array("'$text' No pasó la validación", $column->getAttribute("regExp"), $column->getAttribute("required")));
                                $errors++;
                                $data = array();
                                break;
                            } else {
                                $data[$column->getAttribute('target')] = $ad->quote(trim($text));
                                $push = true;
                            }
                        }
                        
                        if ($push) {
                            $aQueries[] = "(" . implode(",", $data) . ")";
                        }
                    }
                }
                
                $firstLine = false;
                $i++;
                
                if ($i >= $this->_maxAllowedLines) {
                    $this->iterateFile($aQueries, $action, $processed);
                    $i = 0;
                    $aQueries = array();
                }
            }
            
            if (!empty($aQueries)) {
                $this->iterateFile($aQueries, $action, $processed);
            }
            
            $response['error'] = $processed > 0 ? '0' : '1';
            $response['message'] = $action != 'delete' ? "Ingresados y/o actualizados $processed registros." : "Eliminados $processed registros.";
            if ($errors) {
                $response['message'] .= "$errors registros no se pudieron procesar.";
            }
            
        } else {
            $response['error'] = '9';
            $response['message'] = "Extensión de archivo no permitida para '{$file['name']}'";
        }
        return $response;
    }
    
    /**
     * 
     * @param array $aQueries
     * @param string $action
     * @param int $processed
     */
    private function iterateFile($aQueries, $action, &$processed)
    {
        $columnNames = array();
        $replaceNameKey = array();
        $auxQuery = implode($aQueries, ",\n");
        $tableName = $this->_model->info(Zend_Db_Table::NAME);
        $ad = $this->_model->getAdapter();
        
        /**
         * @var $column Zwei_Admin_Xml
         */
        foreach ($this->_columns as $i => $column) {
            $columnNames[] = $ad->quoteIdentifier($column->getAttribute('target'));
            $replaceNameKey[] = $ad->quoteIdentifier($column->getAttribute('target')) . "=VALUES(" . $ad->quoteIdentifier($column->getAttribute('target')) . ")";
        }
        
        $columns = implode(",", $columnNames);
        $columnsReplace =  implode(",", $replaceNameKey);
        
        if ($action == 'load') {
             $query = "INSERT INTO `$tableName` ($columns) VALUES $auxQuery ON DUPLICATE KEY UPDATE $columnsReplace";
        } else if ($action == 'insert') {
             if ($this->_truncate) {
                $ad->query("TRUNCATE TABLE `$tableName`");
             }
             $query = "INSERT IGNORE INTO `$tableName` ($columns) VALUES $auxQuery";
        } else if ($action == 'delete') {
             $query = "DELETE FROM `$tableName` WHERE  ($columns) IN ($auxQuery)";
        }
        
        
        $executed = $ad->query($query);
        $processed += $executed->rowCount();
    }
}