<?php
class Zwei_Utils_File_Parser
{
    /**
     * 
     * @var string
     */
    private $_fileName;
    /**
     * 
     * @var file resource
     */
    private $_fileResource;
    /**
     * 
     * @var array
     */
    private $_data;
    /**
     * 
     * @var Zend_Db_Adapter_Abstract
     */
    private $_adapter;
    /**
     * 
     * @var string
     */
    private $_rowSeparator = "\n";
    /**
     *
     * @var string
     */
    private $_colSeparator = ';';
    
    /**
     * 
     * @var array
     */
    
    private $_titles = array();
    
    /**
     * 
     * @var boolean
     */
    private $_ignoreEmptyRows = true;
    
    /**
     * Abortar en caso de encontrar valores no válidos
     * @var boolean
     */
    private $_abortOnError = false;
    
    
    /**
     * Omitir validación de primera fila
     * @var boolean
     */
    private $_skipTitleValidation = true;
    
    /**
     * 
     * @param string                   $fileName
     * @param array                    $titles   @example array('columna', '\[regexp]\') 
     * @param Zend_Db_Adapter_Abstract $adapter
     */
    public function __construct($fileName, $titles, $adapter = null)
    {
        $this->_fileName = $fileName;
        $this->_titles = $titles;
        $this->_fileResource = fopen($fileName, 'r');
        
        if ($adapter) {
            $this->_adapter = $adapter;
        }
    }
    
    public function csv()
    {
        while ($line = fgetcsv($this->_fileResource, null, $this->_colSeparator)) {
            
        }
    }
    
}