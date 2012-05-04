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
	 * @var Zend_Db_Rowset
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
	 * @return html
	 */
	
	function showTitles($rowset, $component)
	{
		$out="<tr>";
		foreach ($rowset[0] as $target => $value) 
		{
			if(!isset($this->_xml)){
				$out.= "<th>".$target."</th>";
			}else if(!empty($this->_name[$target])){		
			    $out.= "<th>".$this->_name[$target]."</th>";
			}
		}
		$out.="</tr>\n";
		return $out;		
	}
	
	/**
	 * Retorna una fila del Rowset como HTML
	 * @param $rowset
	 * @param $count
	 * @return HTML
	 */
	
	function showContent($rowset, $count)
	{
		$out="<tr>";
		foreach ($rowset[$count] as $target => $value) 
		{
			if(!empty($this->_name[$target]) || !isset($this->_xml)){	
			    $out.= "<td>$value</td>";
			}    
		}
		$out.="</tr>\n";
		return $out;		
	}	
	
	/**
	 * Lee los alias de los campos de la tabla según su equivalente en el XML
	 * y lo prepara para su impresión si es que debe ser visible
	 */
	
	private function parseComponent($component)
	{
		$Xml=new Zwei_Admin_Xml();
		
	    if (preg_match('/(.*).php/', $component)) {
            $file = BASE_URL ."/components/".$component;
        } else {
            $file = COMPONENTS_ADMIN_PATH."/".$component;
        }
		
		$Xml->parse(COMPONENTS_ADMIN_PATH."/".$component);
		$this->_xml=$Xml->elements;
		$count=count($this->_xml);
	    for($i=1; $i<$count; $i++){
	  		if($this->_xml[$i]["VISIBLE"]=="true"){
	  			$this->_name[$this->_xml[$i]["TARGET"]]=$this->_xml[$i]["NAME"];
	  		}	
		}
	}
	
	/**
	 * Tranforma un Zend_Db_Rowset a HTML
	 * @param $rowset
	 * @param $component componente XML asociado
	 * @return string tabla HTML
	 */
	
	function rowsetToHtml($rowset, $component=false)
	{
		if($component){
			$this->parseComponent($component);
		}
		
		$count=count($rowset);

		$out="<table cellspacing=\"0\" border=\"2\">\n";
		$out.=$this->showTitles($rowset, $component);
		for($i=0;$i<$count;$i++){
			$out.=$this->showContent($rowset, $i);
		}	
		$out.="</table>\n";
		return $out;
	}
}
