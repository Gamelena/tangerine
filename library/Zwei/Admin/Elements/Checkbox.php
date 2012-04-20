<?php

/**
 * Input CheckBox
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Elements_Checkbox extends Zwei_Admin_Elements_Element
{

	function edit($i, $j, $display="block"){
		$checked = $this->value==1 ? "checked=\"checked\"" : "";
		Zwei_Utils_Debug::write($this->params);
		$onchange=isset($this->params['ONCHANGE']) ? "onchange=\"{$this->params['ONCHANGE']}\"":'';

		return "<input type=\"checkbox\" style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" value=\"1\" $checked $onchange />";
	}



	function display($i, $j){
		$checked = $this->value!=0 ? 'checked="checked"' : "";
		return "<input type=\"checkbox\" $checked disabled=\"disabled\" id=\"field{$i}_{$j}\" name=\"$this->target[]\" />";

	}



	function get($value){
		return $value!=$this->value ? $this->value : 0;
	}
}
