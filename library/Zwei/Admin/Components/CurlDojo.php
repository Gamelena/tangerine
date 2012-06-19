<?php

/**
 *
 * Envía un request Ajax procesado por curl mediante un input 
 *
 * El input tiene las mismas características de un buscador, se envía como parámetro $_GET de la UR
 * Ejemplo:
 * <code>
 * <section name="Consulta de saldo" type="curl_dojo" target="http://localhost:3001/csaldo" search="MSISDN" search_required="true" search_reg_exp="^(51)\d{9}" search_prompt_message="Formato internacional('51' + 9 dígitos)." search_invalid_message="Formato internacional('51' + 9 dígitos).">
 *   <field name="M&amp;oacute;vil" type="null" target="MSISDN"/>
 * </section>
 * </code>
 *
 * @category Zwei
 * @package Zwei_Admin
 * @subpackage Elements
 * @version $Id:$
 * @author Rodrigo Riquelme <rodrigo.riquelme.e@gmail.com>
 * @license GNU GPL-v2 http://www.gnu.org/licenses/gpl-2.0.html
 * @since 0.1
 * @link http://code.google.com/p/xml-admin/
 *
 */

class Zwei_Admin_Components_CurlDojo extends Zwei_Admin_Controller implements Zwei_Admin_ComponentsInterface
{
	public $page;
	protected $_acl;

	/**
	 *
	 * @param string $page
	 */
	function __construct($page){
		$this->page=$page;
		$userInfo = Zend_Auth::getInstance()->getStorage()->read();
		$this->_acl = new Zwei_Admin_Acl($userInfo->user_name);
		$this->getLayout();
	}

	function display(){
		$form=new Zwei_Utils_Form();
		$request=array();
		foreach (get_object_vars($form) as $var=>$val){
			$request[$var]=$val;
		}
		$url=urlencode($this->layout[0]["TARGET"]);

		$out="
	    <div id=\"content_dojo\" style=\"width:100%\">\r\n";
		if(isset($this->layout[0]['SEARCH'])){
			$dojotype= @$this->layout[0]['SEARCH_DOJO_TYPE'] ? "dojoType=\"{$this->layout[0]['SEARCH_DOJO_TYPE']}\"" : "dojoType=\"dijit.form.ValidationTextBox\"";
			$constrains= @$this->layout[0]['SEARCH_CONSTRAINTS']? "constraints=\"{$this->layout[0]['SEARCH_CONSTRAINTS']}\"" : '';
			$invalid_message= @$this->layout[0]['SEARCH_INVALID_MESSAGE']? "invalidMessage=\"{$this->layout[0]['SEARCH_INVALID_MESSAGE']}\"" : '';
			$prompt_message= @$this->layout[0]['SEARCH_PROMPT_MESSAGE']? "promptMessage=\"{$this->layout[0]['SEARCH_PROMPT_MESSAGE']}\"" : '';
			$required= @$this->layout[0]['SEARCH_REQUIRED']=="true"? "required=\"true\"" : '';
			$regexp = @$this->layout[0]['SEARCH_REG_EXP'] ? "RegExp=\"{$this->layout[0]['SEARCH_REG_EXP']}\"" : '';
			$label= @$this->layout[1]['NAME']?$this->layout[1]['NAME']:"Buscar";
				
				
			$out .="<div dojoType=\"dijit.form.Form\" id=\"search_form\" jsId=\"search_form\" encType=\"multipart/form-data\" action=\"\" method=\"\">\r\n";
			$out .="
        	<script type=\"dojo/method\" event=\"onSubmit\">
                if (this.validate()) {
	                get_url_contents('http-request/curl?url=$url&params={$this->layout[0]['SEARCH']}%3D'+dojo.byId('search_form').elements['search'].value,'ajax_box');
                    return false;
                } else {
                	alert('Por favor corrija los campos marcados.');
                    return false;
                }
                return true;
            </script>\r\n";
			$out .="<table style=\"border: 1px solid #0066cc;\" cellspacing=\"10\" align=\"center\">\r\n";
			$out .="<tr><td><label for=\"search\">$label</label></td>";
			$out .="<td><input type=\"text\" name=\"search\" placeHolder=\"Ingresar\" $dojotype trim=\"true\" id=\"search\" $constrains $invalid_message $prompt_message $regexp $required /></td></tr>\r\n";
			$out .="<tr><td colspan=\"2\" align=\"center\">";
			$out .="<button type=\"submit\" dojoType=\"dijit.form.Button\" iconClass=\"dijitIconSearch\" id=\"btnBuscar\" >Buscar</button>";
			$out .="</td></tr>";
			$out .="</table>\r\n";
			$out .="</div>\n<br/>\r\n";
	   
		}


		$out.="</td></tr></table>\r\n";
		$out .= "</div>\r\n";
		$out .="<div id=\"ajax_box\"></div>";
		return $out;
	}
}
