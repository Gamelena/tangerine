<?php

/**
 * Dojo Filtering Select
 * 
 * 
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */


class Zwei_Admin_Elements_PkOriginal extends Zwei_Admin_Elements_Element
{
    /**
     * Despliegue del elemento en formulario editable
     * @param $i
     * @param $j
     * @param $display
     * @return string HTML
     */

    public function edit($i, $j, $display="none") 
    {
        return "<input type=\"hidden\" style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" value=\"".str_replace('"','&quot;',$this->value)."\" />";
    }


    /**
     * obtener valor del formulario
     * @param $value
     * @param $i
     * @return unknown_type
     */
    public function get($value) {
        return $value;
    }
}
