<?php
/**
 * Dojo file uploader
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */
class Zwei_Admin_Elements_DojoUploader extends Zwei_Admin_Elements_Element
{

    /**
     * (non-PHPdoc)
     * @see Zwei_Admin_Elements_Element::edit()
     * @return string
     */
    function edit($i, $j, $display="block"){
        $disabled = isset($this->params['DISABLED']) && $this->params['DISABLED'] == 'true' ? 'disabled="true"' : '';
        $readonly = @$this->params['READONLY'] ? "readonly=\"readonly\"" : '';
        $required = @$this->params['REQUIRED'] ? "required=\"true\"" : '';
        $onblur = @$this->params['ONBLUR'] ? "onblur=\"{$this->params['ONBLUR']}\"" : '';
        $invalid_message= isset($this->params['INVALID_MESSAGE']) ? "invalidMessage=\"{$this->params['INVALID_MESSAGE']}\"" : '';
        $prompt_message= isset($this->params['PROMPT_MESSAGE']) ? "promptMessage=\"{$this->params['PROMPT_MESSAGE']}\"" : '';
        
        $url = isset($this->params['URL']) ? "url=\"{$this->params['URL']}\"" : '';
        
        return "<input name=\"$this->target[$i]\" type=\"file\" data-dojo-type=\"dojox.form.Uploader\" label=\"Seleccionar\" id=\"edit{$i}_{$j}\" data-dojo-props=\"showInput:'before',isDebug:true\" $disabled $url $required $onblur $invalid_message $prompt_message />";
    }
}
