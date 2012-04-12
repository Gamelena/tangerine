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
	function edit($i,$j,$display="inline"){
		return "<input type=\"password\" style=\"display:$display\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\" value=\"{$this->value}\" />";
	}
}

?>