<?php

/**
 * CheckBox Dojo
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */


class Zwei_Admin_Elements_DojoCheckbox extends Zwei_Admin_Elements_Element
{
	function edit($i, $j, $display="block"){
		$checked = $this->value=="1" ? "checked" : "";
		if(@$this->params['CHECKED']) $checked = "checked";
		$disabled = @$this->params['DISABLED'] ? "disabled" : '';
		$onchange=isset($this->params['ONCHANGE']) ? "onChange=\"{$this->params['ONCHANGE']}\"":'';
		return "<input dojoType=\"dijit.form.CheckBox\"  value=\"1\" $checked $disabled $onchange style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" />";
	}



	function display($i, $j){
		$checked = $this->value!=0 ? 'checked="checked"' : "";
		return "<input type=\"checkbox\" $checked disabled=\"disabled\" id=\"field{$i}_{$j}\" name=\"$this->target[]\" />";
	}

	function get($value){
		return $value!=$this->value ? $this->value : 0;
	}
}
