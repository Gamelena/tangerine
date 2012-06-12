<?php

/**
 * Parseo de componentes XML del back-office
 *
 * Ejemplo:
 * <code>
 * <?php
 * $Xml=new Zwei_Admin_Xml();
 * $Xml->parse('file');
 * echo $Xml->elements[0]['NAME'];
 * ?>
 * </code>
 *
 * @category Zwei
 * @package Zwei_Admin
 * @version $Id:$
 * @since 0.1
 */



class Zwei_Admin_Xml{
	var $file;
	var $xml_parser;
	var $elements;
	var $parents;
	var $pos;

	function startElement($parser, $name, $attrs)
	{
		$this->pos++;
		$this->elements[$this->pos]=$attrs;
		$this->elements[$this->pos]['_name']=$name;
		if(count($this->parents)>0)$this->elements[$this->pos]['_parent']=$this->parents[count($this->parents)-1];
		array_push($this->parents,$this->pos);
	}

	function endElement($parser, $name)
	{
		array_pop($this->parents);
	}


	/**
	 * Constructor
	 */

	function __construct()
	{
		$this->xml_parser = xml_parser_create();
		xml_set_object($this->xml_parser,$this);
		xml_set_element_handler($this->xml_parser, "startElement", "endElement");
	}

	/**
	 * Parsea archivo XML
	 * @param $file
	 * @return
	 */

	function parse($file)
	{
		$this->elements=array();
		$this->parents=array();
		$this->pos=-1;

		if (!($fp = fopen($file, "r"))) {
			Debug::write("no se encuentra XML $file");
			die("no se encuentra XML $file");
		}

		while ($data = fread($fp, 4096)) {
			if (!xml_parse($this->xml_parser, $data, feof($fp))) {
			    Debug::write(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code($this->xml_parser)),
                xml_get_current_line_number($this->xml_parser)));
                Debug::write($file);
			    Debug::write(file_get_contents($file));
                
                
				die(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->xml_parser)),
				xml_get_current_line_number($this->xml_parser)));
			}
		}
		xml_parser_free($this->xml_parser);
	}
	
	static function getFullPath($file) {
        if (preg_match('/(.*).php/', $file)) {
            $prefix = BASE_URL ."/components/";
            
            $model = new SettingsModel();//Buscar URL 
            $select = $model->select();
            $select->where('id = ?', 'url_from_local');
            $select->where('value != ?', '');
            $select->where('value IS NOT NULL');
            Debug::writeBySettings($select->__toString(), 'query_log');
            $settings = $model->fetchAll($select);
            
            if ($settings->count() > 0) { $prefix = $settings[0]['value'];}  
            
        } else {
            $prefix = COMPONENTS_ADMIN_PATH."/";
        } 
        return $prefix.$file; 
	}    
}
