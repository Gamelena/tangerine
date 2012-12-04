<?php
/**
 * Caja Ajax
 * 
 * Obtiene el contenido del atributo XML "path" y lo muestra en elemento HTML con id="ajax_box"
 * Se sugiere usar clase AjaxController para implementar lógica de respuesta Ajax
 * <code>
 * <element name="N&amp;uacute;mero de abonados" type="ajax_box" path="ajax/abonados-count" edit="true" add="false"/>
 * </code>   
 * 
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 * 
 */


class Zwei_Admin_Elements_AjaxBox extends Zwei_Admin_Elements_Element
{
    function edit($i, $j, $display="inline")
    {
        $out="<div id=\"ajax_box\"></div>";
        $out.="<script type=\"text/javascript\">get_url_contents('".$this->params['PATH']."?id=".$this->params['ID']."','ajax_box');</script>";
        return $out;
    }
}
