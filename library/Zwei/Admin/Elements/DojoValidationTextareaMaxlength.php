<?php

/**
 * Validation Textarea Dojo
 *
 * Requiere JS auxiliar, no es elemento nativo Dojo
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */



class Zwei_Admin_Elements_DojoValidationTextareaMaxlength extends Zwei_Admin_Elements_Element
{
    protected $visible;
    protected $edit;
    protected $name;
    protected $target;
    protected $value;
    protected $params;

    /**
     * Despliegue del elemento en formulario editable
     * @param $i
     * @param $j
     * @param $display
     * @return string HTML
     */

    public function edit($i,$j,$display="block"){

        $readonly = @$this->params['READONLY'] ? "readonly" : '';
        $disabled = @$this->params['DISABLED'] ? "disabled" : '';
        $required = @$this->params['REQUIRED'] ? "required=\"true\"" : '';
        $regexp = isset($this->params['REG_EXP']) ? "RegExp=\"{$this->params['REG_EXP']}\"" : '';
        $invalid_message= isset($this->params['INVALID_MESSAGE']) ? "invalidMessage=\"{$this->params['INVALID_MESSAGE']}\"" : '';
        $prompt_message= isset($this->params['PROMPT_MESSAGE']) ? "promptMessage=\"{$this->params['PROMPT_MESSAGE']}\"" : '';
        $maxlength = $this->params['MAXLENGTH']; //requerido
        $trim = isset($this->params['TRIM']) ? "trim=\"true\"" : '';

        $return = "<textarea id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" dojoType=\"dijit.form.ValidationTextarea\" $readonly $disabled $required $regexp $trim $invalid_message $prompt_message maxlength=\"$maxlength\"
        onKeyDown=\"limitText(document.getElementById('edit{$i}_{$j}'), document.getElementById('countdown_$j'), $maxlength);\"
                onKeyUp=\"limitText(document.getElementById('edit{$i}_{$j}'), document.getElementById('countdown_$j'), $maxlength);\" onclick=\"setTimeout('limitText(document.getElementById(\'edit{$i}_{$j}\'), document.getElementById(\'countdown_$j\'), $maxlength)', 100);\" >".str_replace('"','&quot;',$this->value)."</textarea>";
        $return .= "<div class=\"aux\"><input type=\"text\" name=\"countdown_$j\" style=\"width:35px; float:left;\" id=\"countdown_$j\" dojoType=\"dijit.form.ValidationTextBox\" size=\"3\" value=\"\" readonly=\"\" /><label class=\"tip\">caracteres disponibles.</label></div>";
        return $return;

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
