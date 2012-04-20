<?php
/**
 * Dojo file uploader
 *
 * Depende de Uploads controller
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Components
 * @version $Id:$
 * @since 0.1
 *
 */
class Zwei_Admin_Elements_DojoFileUploader extends Zwei_Admin_Elements_Element
{

	function edit($i, $j, $display="block"){
		$disabled = @$this->params['DISABLED'] ? ", disabled: true" : '';
		 
		$url = @$this->params['URL'] ? "url=\"".BASE_URL.$this->params['URL']."\"": '';
		 
		$html="<iframe src=\"".BASE_URL."uploads?id={$this->params['ID']}\" frameborder=\"0\"></iframe>";
		return $html;
	}

	function get($value){
		return $value!=$this->value ? $this->value : 0;
	}
}
