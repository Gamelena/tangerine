<?php

/**
 * Parseo de componentes XML del back-office
 *
 * Ejemplo:
 * <code>
 * <?php
 * $Xml=new Zwei_Admin_Xml();
 * $Xml->parse('file.xml');
 * echo $Xml->elements[0]['NAME'];
 * ?>
 * </code>
 * [FIXME]
 * Se han duplicado metodos de Zwei_Utils_Xml ya que esa clase extiende de SimpleXMLElement e implementa un constructor final, no sobreescribible, 
 * por lo tanto NO podemos heredar de Zwei_Utils_Xml (por ahora) ya que necesitamos un constructor personalizado para compatibilidad hacia atras.
 *  
 * Sin embargo es deseable heredar de Zwei_Utils_SimpleXML a futuro y borrar métodos duplicados.
 * Esto se puede hacer cuando no usemos más xml_parser_create o lo que es lo mismo: cuando no se invoque más el constructor de esta clase sin parametros.
 *
 * @category Zwei
 * @package Zwei_Admin
 * @version $Id:$
 * @since 0.1
 */



class Zwei_Admin_Xml
{
    var $file;
    /**
     * Est var debiera ser ser siemple xml_parser_create, xml_parser_create es solo para backward compatibility
     * @var Zwei_Utils_SimpleXML
     */
    private $_parser;
    var $elements;
    var $parents;
    var $pos;

    /**
     * 
     * @param $parser
     * @param $name
     * @param $attrs
     * @return unknown_type
     * @deprecated
     */
    private function startElement($parser, $name, $attrs)
    {
        $this->pos++;
        $this->elements[$this->pos]=$attrs;
        $this->elements[$this->pos]['_name']=$name;
        if(count($this->parents)>0)$this->elements[$this->pos]['_parent']=$this->parents[count($this->parents)-1];
        array_push($this->parents,$this->pos);
    }
    /**
     * 
     * @param $parser
     * @param $name
     * @return void
     * @deprecated
     */
    private function endElement($parser, $name)
    {
        array_pop($this->parents);
    }


    /**
     * Por motivos de backward compatibility con xml_parser_create() se admite el constructor sin parámetros,
     * sin embargo $data debe ser obligatorio a futuro ya que se debiera siempre usar SimpleXmlElement en su lugar.  
     * 
     * @param string ruta de archivo a parsear
     * @param int - options
     * @param boolean - es url
     * @param string - namespace
     * @param boolean - es prefijo
     * 
     * @return void
     */

    public function __construct($data = null, $options = 0, $data_is_url = false, $ns = "", $is_prefix = false)
    {
        if (is_null($data)) {
            //[TODO] esto se debe deprecar
            $this->_parser = xml_parser_create();
            xml_set_object($this->_parser,$this);
            xml_set_element_handler($this->_parser, "startElement", "endElement");
        } else {
            $this->_parser = new Zwei_Utils_SimpleXML($data, $options, $data_is_url, $ns, $is_prefix);
        }    
    }

    /**
     * Parsea archivo XML
     * @param $file ruta de archivo a parsear
     * @return void
     * @deprecated
     */

    public function parse($file)
    {
        $this->elements=array();
        $this->parents=array();
        $this->pos=-1;

        if (!($fp = fopen($file, "r"))) {
            Debug::write("no se encuentra XML $file");
            die("no se encuentra XML $file");
        }

        while ($data = fread($fp, 4096)) {
            if (!xml_parse($this->_parser, $data, feof($fp))) {
                Debug::write(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code($this->_parser)),
                xml_get_current_line_number($this->_parser)));
                Debug::write($file);
                Debug::write(file_get_contents($file));
                
                
                die(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code($this->_parser)),
                xml_get_current_line_number($this->_parser)));
            }
        }
        xml_parser_free($this->_parser);
    }
    
    /**
     * Obtiene el path completo del archivo xml, según las convenciones de admportal,
     * si el archivo xml termina con .php se buscará el archivo por http y no por ruta interna,
     * si existe el atributo de tabla 'web_settings.url_from_local', buscara el archivo en host 'web_settings.url_from_local' en lugar de localhost,
     * este último caso es util para cuando se use tunel http. 
     * 
     * @param string
     * @return string
     */
    public function getFullPath($file) {
        if (preg_match('/(.*).php/', $file)) {
            $prefix = BASE_URL ."/components/";
            
            $model = new SettingsModel();//Buscar URL 
            $select = $model->select();
            $select->where('id = ?', 'url_from_local');
            $select->where('value != ?', '');
            $select->where('value IS NOT NULL');
            Debug::writeBySettings($select->__toString(), 'query_log');
            $settings = $model->fetchAll($select);
            
            if ($settings->count() > 0) { $prefix = $settings[0]['value']."/components/";}  
            
        } else {
            $prefix = COMPONENTS_ADMIN_PATH."/";
        } 
        return $prefix.$file; 
    }

    
    
    public function getAttribute($name)
    {
        return $this->_parser->getAttribute($name);
    }
    
    public function getChildrenCount()
    {
        return $this->_parser->getChildrenCount();
    }
    
    public function getChildrenCountName($name)
    {
        return $this->_parser->getChildrenCountName($name);
    }
    
    
    public function existsChildren($name)
    {
        return $this->_parser->existsChildren($name);
    }
    
    public function getAttributeCount()
    {
        return $this->_parser->getAttributeCount();
    }
    
    public function getAttributesArray($names)
    {
        return $this->_parser->getAttributesArray($names);
    }
}