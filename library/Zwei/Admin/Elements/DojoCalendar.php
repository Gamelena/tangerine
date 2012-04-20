<?php

/**
 * Calendario Dojo
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Elements_DojoCalendar extends Zwei_Admin_Elements_Element
{
	function edit($i, $j, $display="block")
	{
		$readonly = @$this->params['READONLY'] ? "readonly" : '';
		$disabled = @$this->params['DISABLED'] ? "disabled" : '';
		$required = @$this->params['REQUIRED'] ? "required=\"true\"" : '';
		$constraints= @$this->params['CONSTRAINTS']? "constraints=\"{$this->params['CONSTRAINTS']}\"" : "constraints=\"{datePattern:'yyyy-MM-dd'}\"";
		$invalid_message= @$this->params['INVALID_MESSAGE']? "invalidMessage=\"{$this->params['INVALID_MESSAGE']}\"" : "invalidMessage=\"Fecha inválida\"";
		$prompt_message= @$this->params['PROMPT_MESSAGE']? "promptMessage=\"{$this->params['PROMPT_MESSAGE']}\"" : '';

		return "<input type=\"text\" name=\"$this->target[$i]\" value=\"".str_replace('"','&quot;',$this->value)."\" placeHolder=\"\" dojoType=\"dijit.form.DateTextBox\" id=\"edit{$i}_{$j}\" $required $constraints $invalid_message $prompt_message/>";
	}
}
