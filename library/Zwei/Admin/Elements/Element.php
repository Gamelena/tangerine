<?php
/**
 * Elementos para los formularios del CMS
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */


class Zwei_Admin_Elements_Element
{
    protected $visible;
    protected $edit;
    protected $name;
    protected $target;
    protected $value;
    protected $params;

    /**
     * Constructor
     * @param $visible
     * @param $edit
     * @param $name
     * @param $target
     * @param $value
     * @param $params
     */

    public function __construct($visible=false,$edit=false,$name="",$target="",$value="",$params=array())
    {
        $this->visible=$visible;
        $this->edit=$edit;
        $this->name=$name;
        $this->target=$target;
        $this->value=$value;
        $this->params=$params;
    }

    /**
     * Despliegue del elemento en el listado de CMS
     * @param $i
     * @param $j
     * @return string HTML
     */

    public function display($i,$j)
    {
        if (!empty($this->params['LINK'])) {
            if (!empty($this->params['IMAGE'])) {
                $value="<img src=\"{$this->params['IMAGE']}\" alt=\"$this->value\"/>";
            } else {
                //$value=$this->value;
                $value=$this->params['DEFAULT'];
            }
            $href = str_replace("{id}" ,$this->params['ID'], $this->params['LINK']);
            $href = str_replace("{value}", $this->value, $href);
            return "<a id=\"field{$i}_{$j}\" title=\"$this->value\" name=\"$this->target[]\" href=\"$href\">$value</a>";
        }
        return "<span id=\"field{$i}_{$j}\" name=\"$this->target[]\">{$this->value}</span>";
    }

    /**
     * Despliegue del elemento en formulario editable
     * @param $i
     * @param $j
     * @param $display
     * @return string HTML
     */

    public function edit($i, $j, $display="block")
    {
        $readonly = !empty($this->params['READONLY']) ? "readonly" : '';
        $disabled = !empty($this->params['DISABLED']) ? "disabled" : '';
        //return "<input type=\"text\" style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" value=\"".str_replace('"','&quot;',$this->value)."\" $readonly $disabled/>";
        return "<input type=\"text\" style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" value=\"".str_replace('"','&quot;',$this->value)."\" $readonly $disabled/>";
    }

    /**
     * obtener valor del formulario
     * @param $value
     * @param $i
     * @return unknown_type
     */


    public function get()
    {
        return $value;
    }
    /**
     * Sobrescribir para personalizar el elemento json al desplegar en EditTableDojo 
     * @return String
     */
    public function editCustomDisplay($i, $j)
    {
       return '';    
    }
    
}
