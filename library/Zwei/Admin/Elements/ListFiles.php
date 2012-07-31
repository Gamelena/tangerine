<?php
/**
 * Listador de Archivos
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @since 0.1
 */

class Zwei_Admin_Elements_ListFiles extends Zwei_Admin_Elements_Element{
	private $_acl;

	public function __construct($visible, $edit, $name, $target, $value, $params){
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();
		$this->_acl = new Zwei_Admin_Acl($userInfo->user_name);
		parent::__construct($visible, $edit, $name, $target, $value, $params);
	}

	function edit($i, $j){
		if (is_dir($this->params['PATH'])) {
			if ($dh = opendir($this->params['PATH'])) {
				$out="
				<div style=\"height:300px;overflow:auto\">
				<table class=\"select_files\">";
				
				$files = array();
				while ($files[] = readdir($dh));
				sort($files);
				
				foreach ($files as $file) {

					if (!is_dir($this->params['PATH'] .'/'. $file) && $file!="." && $file!=".."){
						//solo si el archivo es un directorio, distinto que "." y ".."
						$row_file="row_".str_replace('.','', $file);
						$out.="<tr id=\"$row_file\">";
						
						if (isset($this->params['FUNCTIONS'])) {
						    $out .= "<td>$file</td>";
						} else if (isset($this->params['PATH_PUBLIC'])){
						    $out .= "<td><a href=\"{$this->params['PATH_PUBLIC']}/$file\">$file</a></td>";     
						} else {
						    $out .= "<td>$file</td>";
						}
						
						if(isset($this->params['FUNCTIONS'])){
							$CustomFunctions=new Zwei_Utils_CustomFunctions();
							$params=$this->params['PATH'] .'/'. $file;
							$component=$this->page;
							$functions=explode(",",(@$this->params['FUNCTIONS']));
							if(!is_array($functions)) $functions=array($this->params['FUNCTIONS']);
							$permissions=explode(",",($this->params['FUNCTIONS_PERMISSIONS']));
							if(!is_array($permissions)) $permissions=array($this->params['FUNCTIONS_PERMISSIONS']);
							$i=0;
							foreach ($functions as $f)
							{
								//Zwei_Utils_Debug::write($permissions[$i]);
								//if(empty($permissions[$i]) || $this->_acl->isUserAllowed($this->page, strtoupper($permissions[$i]))){
								$foo=Zwei_Utils_String::toFunctionWord($f);
								$out.="<td><a href=\"javascript:void(0)\" onclick=\"execFunction('$f', '$params','$component');document.getElementById('ajax_loading_bar').style.background='url(".BASE_URL."images/ajax_loading_bar.gif)';return false;\">";
								$out.=$CustomFunctions->getName($foo);
								$out.="</a></td>";
								//}
								$i++;
							}
						}
						$out.="</tr>";
					}

				}
				$out.="</table>";
				$out.="<div id=\"ajax_loading_bar\"></div></div>";
				closedir($dh);
			}
		}else{
			$out = "Ruta {$this->params['PATH']} no encontrada";
		}
		return $out;
	}

	function display($i, $j){
		return edit($i, $j, $display="block");
	}
}
