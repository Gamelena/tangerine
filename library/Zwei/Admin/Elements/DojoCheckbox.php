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
    function edit($i, $j, $display="block")
    {
        $defaultValue = isset($this->params['DEFAULT_VALUE']) ? $this->params['DEFAULT_VALUE'] : '1';
        $defaultEmpty = isset($this->params['DEFAULT_EMPTY']) ? $this->params['DEFAULT_EMPTY'] : '0';
        $checked = $this->value == $defaultValue ? "checked" : "";
        if(@$this->params['CHECKED']) $checked = "checked";
        $disabled = isset($this->params['DISABLED']) ? "disabled=\"disabled\"" : '';
        $onchange = isset($this->params['ONCHANGE']) ? $this->params['ONCHANGE'] : '';
        $onclick = isset($this->params['ONCLICK']) ? "onclick=\"{$this->params['ONCLICK']}\"" : '';
        return "<input dojoType=\"dijit.form.CheckBox\" onChange=\"if(dijit.byId('edit{$i}_{$j}').get('checked')){dijit.byId('edit{$i}_{$j}').set('value','$defaultValue')}else{dijit.byId('edit{$i}_{$j}').set('value','$defaultEmpty');dijit.byId('edit{$i}_{$j}').set('checked', false);}$onchange\" value=\"$defaultValue\" $checked $disabled $onclick style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" />";
    }



    function display($i, $j){
        $checked = $this->value!=0 ? 'checked="checked"' : "";
        return "<input type=\"checkbox\" $checked disabled=\"disabled\" id=\"field{$i}_{$j}\" name=\"$this->target[]\" />";
    }

    function get($value){
        return $value!=$this->value ? $this->value : 0;
    }
}
