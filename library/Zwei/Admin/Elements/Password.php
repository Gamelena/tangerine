<?php
/**
 * Input password
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */
class Zwei_Admin_Elements_Password extends Zwei_Admin_Elements_Element{
	function edit($i,$j,$display="block")
	{
	    $readonly = @$this->params['READONLY'] ? "readonly=\"readonly\"" : '';
        $disabled = @$this->params['DISABLED'] ? "disabled=\"disabled\"" : '';
        $required = @$this->params['REQUIRED'] ? "required=\"true\"" : '';
        $onblur = @$this->params['ONBLUR'] ? "onblur=\"{$this->params['ONBLUR']}\"" : '';
        $regexp = isset($this->params['REG_EXP']) ? "RegExp=\"{$this->params['REG_EXP']}\"" : '';
        $invalid_message= isset($this->params['INVALID_MESSAGE']) ? "invalidMessage=\"{$this->params['INVALID_MESSAGE']}\"" : '';
        $prompt_message= isset($this->params['PROMPT_MESSAGE']) ? "promptMessage=\"{$this->params['PROMPT_MESSAGE']}\"" : '';
        $maxlength = isset($this->params['MAXLENGTH']) ? "maxlength=\"{$this->params['MAXLENGTH']}\"" : '';
        $trim = isset($this->params['TRIM']) ? "trim=\"true\"" : '';
	    
		return "<input dojoType=\"dijit.form.ValidationTextBox\" type=\"password\" style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" value=\"{$this->value}\" $readonly $disabled $required $regexp $trim $invalid_message $prompt_message $maxlength $onblur />";
	}
}

?>
