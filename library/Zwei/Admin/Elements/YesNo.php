<?php
/**
 * Select Sí, No
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Elements_YesNo extends Zwei_Admin_Elements_Element{
	function edit($i, $j, $display="inline"){
		$selected=array('','');
		if($this->value=='1'){
			$selected[1] = "selected=\"selected\"";
		}else{
			$selected[0] = "selected=\"selected\"";
		}
		return "<select style=\"width:50px;display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\">
	  <option value=\"1\" $selected[1]>S&iacute;</option>
	  <option value=\"0\" $selected[0]>No</option>";
	}

	function display($i, $j){
		$checked = $this->value==1 ? 'checked="checked"' : "";
		return "<input type=\"checkbox\" $checked disabled=\"disabled\" id=\"field{$i}_{$j}\" name=\"$this->target[$i]\" />";
	}

	function get($value){
		return $value==1 ? 1 : 0;
	}
}
?>
