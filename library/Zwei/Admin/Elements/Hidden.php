<?php
/**
 * Input Oculto
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */


class Zwei_Admin_Elements_Hidden extends Zwei_Admin_Elements_Element
{
	protected $visible;
	protected $edit;
	protected $name;
	protected $target;
	protected $value;
	protected $params;

	/**
	 * Constructor
	 * @param $visible
	 * @param $edit
	 * @param $name
	 * @param $target
	 * @param $value
	 * @param $params
	 * @return unknown_type
	 */

	/*
	 public function __construct($visible=false,$edit=false,$name="",$target="",$value="",$params=array()){
		//Zwei_Utils_Debug::write($params);
		$this->visible=$visible;
		$this->edit=$edit;
		$this->name=$name;
		$this->target=$target;
		$this->value=$value;
		$this->params=$params;
		}
		*/

	/**
	 * Despliegue del elemento en formulario editable
	 * @param $i
	 * @param $j
	 * @param $display
	 * @return string HTML
	 */

	public function edit($i,$j,$display="block"){
		return "<input type=\"hidden\"  id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" value=\"".str_replace('"','&quot;',$this->value)."\" />";
	}

	/**
	 * obtener valor del formulario
	 * @param $value
	 * @param $i
	 * @return unknown_type
	 */


	public function get($value,$i=0){
		return $value;
	}

}

?>
