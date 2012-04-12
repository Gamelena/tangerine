<?php

/**
 * Checkbox para seleccionar item en listado
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Elements_Idbox extends Zwei_Admin_Elements_Element
{
	function edit($i, $j, $display="inline")
	{
		return "<input type=\"hidden\" name=\"{$this->target}[$i]\" value=\"{$this->value}\"/>$this->value";
	}

	function display($i, $j)
	{
		return "<input id=\"edit{$i}_{$j}\" type=\"checkbox\" name=\"{$this->target}[]\" value=\"{$this->value}\"/>";
	}
}
