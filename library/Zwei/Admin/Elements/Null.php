<?php
/**
 * Input nulo, no retorna nada pero envia parametro a ObjectsController
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */
class Zwei_Admin_Elements_Null extends Zwei_Admin_Elements_Element{
    function edit($i, $j, $display="inline"){
        return '';
    }

    function display($i, $j){
        return '';
    }
}
