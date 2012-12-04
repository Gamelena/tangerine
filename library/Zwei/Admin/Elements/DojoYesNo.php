<?php


/**
 * Filtering Select Yes No
 *
 *
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Elements_DojoYesNo extends Zwei_Admin_Elements_Element
{
    function edit($i, $j, $display="block") {
        $selected=array('','');
        if($this->value == 'No'){
            $selected[0] = "selected=\"selected\"";
        }else{
            $selected[1] = "selected=\"selected\"";
        }
        $return = "<select dojoType=\"dijit.form.FilteringSelect\"  onload=\"dijit.byId('edit{$i}_{$j}').set('value', dijit.byId('edit{$i}_{$j}').get('value'))\" style=\"width:50px;display:$display\"  id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" >
      <option value=\"1\" $selected[1]>1</option>
      <option value=\"0\" $selected[0]>0</option>
      </select>";
        
        /*
        $return .= "
         <script type=\"dojo/method\">
        function switchYesNo(){
          if (dijit.byId('edit{$i}_{$j}')).get('value') == '0') dijit.byId('edit{$i}_{$j}').set('value', '0')
          else if (dijit.byId('edit{$i}_{$j}')).get('value') == '1') dijit.byId('edit{$i}_{$j}').set('value', '1')
        }
        </script>";
        */
        
        return $return;
    }

    function display($i, $j)
    {
        $checked = $this->value == 1 ? 'checked="checked"' : "";
        return "<input type=\"checkbox\" $checked disabled=\"disabled\" id=\"field{$i}_{$j}\" name=\"$this->target[$i]\" />";
    }

    function get($value)
    {
        return $value == 1 ? 1 : 0;
    }
}
