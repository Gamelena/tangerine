<?php
/**
 * Input textarea
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */
class Zwei_Admin_Elements_Textarea extends Zwei_Admin_Elements_Element
{

	function display($i,$j)
	{
		$width=isset($this->params['WIDTH'])?"width:{$this->params['WIDTH']}px;":"";

		return "<div id=\"field{$i}_{$j}\" style=\"{$width}height:100px;overflow:scroll\" name=\"$this->target[]\">{$this->value}</div>";
	}

	function edit($i, $j, $display="inline")
	{
		$width=isset($this->params['WIDTH'])?$this->params['WIDTH']:200;
		$height=isset($this->params['HEIGHT'])?$this->params['HEIGHT']:100;

		return "<textarea style=\"display:$display;width:{$width}px;height:{$height}px\" id=\"edit{$i}_{$j}\" name=\"$this->target[$i]\">{$this->value}</textarea>";

	}
}
?>